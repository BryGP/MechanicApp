<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\CfdiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class InvoiceController
 * 
 * Manages CFDI 4.0 invoice creation, listing, XML downloads, and cancellation.
 * 
 * @package App\Http\Controllers
 */
class InvoiceController extends Controller
{
    protected CfdiService $cfdiService;

    public function __construct(CfdiService $cfdiService)
    {
        $this->cfdiService = $cfdiService;
    }

    /**
     * Display a listing of invoices with optional search and status filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with('order.items.product')->latest('id');

        // Status filter
        if ($request->filled('status') && in_array($request->status, ['vigente', 'cancelada'])) {
            $query->where('status', $request->status);
        }

        // Search filter (Folio, UUID, RFC, Razón Social)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('folio', 'like', "%{$search}%")
                  ->orWhere('uuid', 'like', "%{$search}%")
                  ->orWhere('rfc_receptor', 'like', "%{$search}%")
                  ->orWhere('razon_social_receptor', 'like', "%{$search}%");
            });
        }

        $invoices = $query->paginate($request->integer('per_page', 15));

        // Aggregate summary metrics
        $metrics = [
            'total_invoiced_month' => (float) Invoice::where('status', 'vigente')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
            'active_invoices_count' => Invoice::where('status', 'vigente')->count(),
            'cancelled_invoices_count' => Invoice::where('status', 'cancelada')->count(),
            'total_iva_month' => (float) Invoice::where('status', 'vigente')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('iva_trasladado'),
        ];

        return response()->json([
            'data'    => InvoiceResource::collection($invoices),
            'meta'    => [
                'current_page' => $invoices->currentPage(),
                'last_page'    => $invoices->lastPage(),
                'per_page'     => $invoices->perPage(),
                'total'        => $invoices->total(),
            ],
            'metrics' => $metrics,
        ]);
    }

    /**
     * Get SAT catalogs for CFDI 4.0 forms.
     */
    public function catalogs(): JsonResponse
    {
        return response()->json([
            'data' => $this->cfdiService->getCatalogs(),
        ]);
    }

    /**
     * Store and digitally stamp a new CFDI 4.0 invoice.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->cfdiService->createInvoice($request->validated());

        return response()->json([
            'message' => 'Factura CFDI 4.0 emitida y timbrada exitosamente.',
            'data'    => new InvoiceResource($invoice->load('order.items.product')),
        ], 201);
    }

    /**
     * Display the specified invoice details.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json([
            'data' => new InvoiceResource($invoice->load('order.items.product')),
        ]);
    }

    /**
     * Download the official CFDI 4.0 XML file.
     */
    public function downloadXml(Invoice $invoice): Response
    {
        $filename = ($invoice->series ?: 'FAC') . '-' . str_pad($invoice->folio, 4, '0', STR_PAD_LEFT) . '_' . $invoice->uuid . '.xml';

        return response($invoice->xml_content, 200, [
            'Content-Type'        => 'application/xml; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Cancel an invoice with SAT protocol (protected by admin PIN).
     */
    public function cancel(Request $request, Invoice $invoice): JsonResponse
    {
        $request->validate([
            'motivo' => 'nullable|string|in:01,02,03,04',
        ]);

        $motivo = $request->input('motivo', '02');
        $updated = $this->cfdiService->cancelInvoice($invoice, $motivo);

        return response()->json([
            'message' => 'Factura cancelada exitosamente con acuse fiscal.',
            'data'    => new InvoiceResource($updated->load('order')),
        ]);
    }
}
