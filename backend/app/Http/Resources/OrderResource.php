<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrderResource
 * 
 * Transforms Order model attributes into a predictable JSON payload
 * including customer data, status, financials, and line items.
 * 
 * @package App\Http\Resources
 */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activeInvoice = $this->relationLoaded('invoices')
            ? $this->invoices->firstWhere('status', 'vigente')
            : $this->invoices()->where('status', 'vigente')->first();

        return [
            'id'                   => $this->id,
            'customer_name'        => $this->customer_name,
            'vehicle'              => $this->vehicle,
            'notes'                => $this->notes,
            'status'               => $this->status,
            'total'                => (float) $this->total,
            'is_invoiced'          => $activeInvoice !== null,
            'active_invoice_folio' => $activeInvoice ? (($activeInvoice->series ?: 'FAC') . '-' . str_pad($activeInvoice->folio, 4, '0', STR_PAD_LEFT)) : null,
            'active_invoice_uuid'  => $activeInvoice?->uuid,
            'active_invoice_id'    => $activeInvoice?->id,
            'created_at'           => $this->created_at?->toISOString(),
            'updated_at'           => $this->updated_at?->toISOString(),
            'items'                => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
