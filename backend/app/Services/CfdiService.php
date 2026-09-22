<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Class CfdiService
 * 
 * Manages official SAT CFDI 4.0 fiscal invoice workflows, tax calculations,
 * PAC timbrado simulation, digital seal cryptography, and XML generation.
 * 
 * @package App\Services
 */
class CfdiService
{
    // Workshop Fiscal Identity (Emisor)
    public const EMISOR_RFC              = 'TME200101ABC';
    public const EMISOR_RAZON_SOCIAL      = 'TALLER MECANICO AUTOMOTRIZ ESPECIALIZADO S.A. DE C.V.';
    public const EMISOR_REGIMEN_FISCAL    = '601'; // General de Ley Personas Morales
    public const EMISOR_LUGAR_EXPEDICION  = '06000'; // Matriz Cuauhtémoc, CDMX
    public const NO_CERTIFICADO_EMISOR    = '30001000000500003416';
    public const PAC_RFC                  = 'SAT970701NN3';
    public const NO_CERTIFICADO_SAT       = '00001000000504465028';

    /**
     * Get official SAT CFDI 4.0 catalogs.
     */
    public function getCatalogs(): array
    {
        return [
            'regimenes_fiscales' => [
                ['code' => '601', 'name' => '601 - General de Ley Personas Morales', 'type' => 'moral'],
                ['code' => '603', 'name' => '603 - Personas Morales con Fines no Lucrativos', 'type' => 'moral'],
                ['code' => '605', 'name' => '605 - Sueldos y Salarios e Ingresos Asimilados a Salarios', 'type' => 'fisica'],
                ['code' => '606', 'name' => '606 - Arrendamiento', 'type' => 'fisica'],
                ['code' => '612', 'name' => '612 - Personas Físicas con Actividades Empresariales y Profesionales', 'type' => 'fisica'],
                ['code' => '616', 'name' => '616 - Sin obligaciones fiscales', 'type' => 'fisica'],
                ['code' => '625', 'name' => '625 - Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas', 'type' => 'fisica'],
                ['code' => '626', 'name' => '626 - Régimen Simplificado de Confianza (RESICO)', 'type' => 'both'],
            ],
            'usos_cfdi' => [
                ['code' => 'G01', 'name' => 'G01 - Adquisición de mercancías'],
                ['code' => 'G02', 'name' => 'G02 - Devoluciones, descuentos o bonificaciones'],
                ['code' => 'G03', 'name' => 'G03 - Gastos en general'],
                ['code' => 'I01', 'name' => 'I01 - Construcciones'],
                ['code' => 'I08', 'name' => 'I08 - Otra maquinaria y equipo'],
                ['code' => 'D01', 'name' => 'D01 - Honorarios médicos, dentales y gastos hospitalarios'],
                ['code' => 'D02', 'name' => 'D02 - Gastos médicos por incapacidad o discapacidad'],
                ['code' => 'D04', 'name' => 'D04 - Donativos'],
                ['code' => 'S01', 'name' => 'S01 - Sin efectos fiscales'],
                ['code' => 'CP01', 'name' => 'CP01 - Pagos'],
            ],
            'formas_pago' => [
                ['code' => '01', 'name' => '01 - Efectivo'],
                ['code' => '02', 'name' => '02 - Cheque nominativo'],
                ['code' => '03', 'name' => '03 - Transferencia electrónica de fondos'],
                ['code' => '04', 'name' => '04 - Tarjeta de crédito'],
                ['code' => '28', 'name' => '28 - Tarjeta de débito'],
                ['code' => '99', 'name' => '99 - Por definir'],
            ],
            'metodos_pago' => [
                ['code' => 'PUE', 'name' => 'PUE - Pago en una sola exhibición'],
                ['code' => 'PPD', 'name' => 'PPD - Pago en parcialidades o diferido'],
            ],
            'emisor' => [
                'rfc'              => self::EMISOR_RFC,
                'razon_social'     => self::EMISOR_RAZON_SOCIAL,
                'regimen_fiscal'   => self::EMISOR_REGIMEN_FISCAL,
                'codigo_postal'    => self::EMISOR_LUGAR_EXPEDICION,
            ],
        ];
    }

    /**
     * Create and digitally stamp a CFDI 4.0 invoice.
     *
     * @param array $data
     * @return Invoice
     */
    public function createInvoice(array $data): Invoice
    {
        $series = 'FAC';
        $lastFolio = Invoice::where('series', $series)->max('folio') ?? 0;
        $folio = $lastFolio + 1;

        $order = null;
        if (!empty($data['order_id'])) {
            $existingInvoice = Invoice::where('order_id', $data['order_id'])
                ->where('status', 'vigente')
                ->first();

            if ($existingInvoice) {
                $folio = ($existingInvoice->series ?: 'FAC') . '-' . str_pad($existingInvoice->folio, 4, '0', STR_PAD_LEFT);
                throw new \DomainException("La Orden #{$data['order_id']} ya se encuentra facturada con el comprobante {$folio} vigente.");
            }

            $order = Order::with('items.product')->find($data['order_id']);
        }

        // Build item lines and financials (Prices are NET, IVA is included and desglosado)
        $items = $this->buildConcepts($order, $data['custom_concept'] ?? null);

        $subtotal = 0.0;
        $ivaTrasladado = 0.0;
        foreach ($items as $item) {
            $subtotal += (float) $item['importe'];
            $ivaTrasladado += (float) $item['iva_importe'];
        }

        $subtotal = round($subtotal, 2);
        $ivaTrasladado = round($ivaTrasladado, 2);
        $total = round($subtotal + $ivaTrasladado, 2);

        // Timbrado Cryptography Simulation
        $uuid = (string) Str::uuid();
        $now = Carbon::now();
        $fechaEmision = $now->toIso8601String();
        $fechaTimbrado = $now->copy()->addSeconds(2)->toIso8601String();

        $rfcReceptor = strtoupper(trim($data['rfc_receptor']));
        $razonSocialReceptor = strtoupper(trim($data['razon_social_receptor']));
        $regimenReceptor = trim($data['regimen_fiscal_receptor']);
        $cpReceptor = trim($data['codigo_postal_receptor']);
        $usoCfdi = trim($data['uso_cfdi']);
        $formaPago = trim($data['forma_pago']);
        $metodoPago = trim($data['metodo_pago'] ?? 'PUE');

        // Cryptographic Digital Seals (Simulated 2048-bit RSA-SHA256)
        $selloEmisor = $this->generateSeal(self::EMISOR_RFC . '|' . $rfcReceptor . '|' . $total . '|' . $fechaEmision);
        $selloSat    = $this->generateSeal($uuid . '|' . $fechaTimbrado . '|' . self::PAC_RFC);

        // Cadena Original del Complemento de Certificación Digital del SAT
        $cadenaOriginal = sprintf(
            '||1.1|%s|%s|%s|%s|%s||',
            $uuid,
            $fechaTimbrado,
            self::PAC_RFC,
            substr($selloEmisor, 0, 100),
            self::NO_CERTIFICADO_SAT
        );

        // Official SAT 2D Barcode (QR Code) verification URL
        $totalFormatted = number_format($total, 6, '.', '');
        $last8Seal = substr($selloEmisor, -8);
        $qrCodeUrl = sprintf(
            'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id=%s&re=%s&rr=%s&tt=%s&fe=%s',
            $uuid,
            self::EMISOR_RFC,
            $rfcReceptor,
            $totalFormatted,
            $last8Seal
        );

        // Generate official CFDI 4.0 XML
        $xmlContent = $this->generateXml([
            'uuid'                   => $uuid,
            'series'                 => $series,
            'folio'                  => $folio,
            'fecha_emision'          => $fechaEmision,
            'fecha_timbrado'         => $fechaTimbrado,
            'forma_pago'             => $formaPago,
            'metodo_pago'            => $metodoPago,
            'subtotal'               => $subtotal,
            'iva_trasladado'         => $ivaTrasladado,
            'total'                  => $total,
            'rfc_receptor'           => $rfcReceptor,
            'razon_social_receptor'  => $razonSocialReceptor,
            'regimen_receptor'       => $regimenReceptor,
            'cp_receptor'            => $cpReceptor,
            'uso_cfdi'               => $usoCfdi,
            'sello_emisor'           => $selloEmisor,
            'sello_sat'              => $selloSat,
            'cadena_original'        => $cadenaOriginal,
            'items'                  => $items,
        ]);

        return Invoice::create([
            'uuid'                    => $uuid,
            'series'                  => $series,
            'folio'                   => $folio,
            'order_id'                => $order?->id,
            'rfc_receptor'            => $rfcReceptor,
            'razon_social_receptor'   => $razonSocialReceptor,
            'regimen_fiscal_receptor' => $regimenReceptor,
            'codigo_postal_receptor'  => $cpReceptor,
            'uso_cfdi'                => $usoCfdi,
            'forma_pago'              => $formaPago,
            'metodo_pago'             => $metodoPago,
            'moneda'                  => 'MXN',
            'tipo_cambio'             => 1.0000,
            'subtotal'                => $subtotal,
            'iva_trasladado'          => $ivaTrasladado,
            'total'                   => $total,
            'no_certificado_emisor'   => self::NO_CERTIFICADO_EMISOR,
            'no_certificado_sat'      => self::NO_CERTIFICADO_SAT,
            'fecha_emision'           => $now,
            'fecha_timbrado'          => $now->copy()->addSeconds(2),
            'sello_emisor'            => $selloEmisor,
            'sello_sat'               => $selloSat,
            'cadena_original'         => $cadenaOriginal,
            'qr_code_url'             => $qrCodeUrl,
            'xml_content'             => $xmlContent,
            'status'                  => 'vigente',
        ]);
    }

    /**
     * Cancel an active CFDI 4.0 invoice with SAT acknowledgment.
     *
     * @param Invoice $invoice
     * @param string $motivo
     * @return Invoice
     */
    public function cancelInvoice(Invoice $invoice, string $motivo = '02'): Invoice
    {
        if ($invoice->status === 'cancelada') {
            return $invoice;
        }

        $invoice->update([
            'status'             => 'cancelada',
            'fecha_cancelacion'  => Carbon::now(),
            'motivo_cancelacion' => $motivo,
        ]);

        return $invoice;
    }

    /**
     * Build fiscal concept items from order or fallback custom concept.
     */
    protected function buildConcepts(?Order $order, ?array $customConcept): array
    {
        $concepts = [];

        if ($order && $order->items->isNotEmpty()) {
            foreach ($order->items as $item) {
                $productName = $item->product ? $item->product->name : 'Servicio / Refacción';
                $isLabor = ($item->product && $item->product->is_service)
                    || str_contains(strtolower($productName), 'mano')
                    || str_contains(strtolower($productName), 'servicio')
                    || str_contains(strtolower($productName), 'diagnostico')
                    || str_contains(strtolower($productName), 'protocolo')
                    || str_contains(strtolower($productName), 'mantenimiento')
                    || str_contains(strtolower($productName), 'purga')
                    || str_contains(strtolower($productName), 'alineacion');

                $claveProdServ = $isLabor ? '78181500' : '25171700'; // 78181500 = Servicios mecánicos, 25171700 = Frenos/suspensión
                $claveUnidad   = $isLabor ? 'E48' : 'H87'; // E48 = Unidad de servicio, H87 = Pieza
                $unidadNombre  = $isLabor ? 'Servicio' : 'Pieza';

                $netItemTotal = round((float) $item->unit_price * (int) $item->quantity, 2);
                $subtotalItem = round($netItemTotal / 1.16, 2);
                $ivaItem      = round($netItemTotal - $subtotalItem, 2);
                $unitPriceSinIva = (int) $item->quantity > 0 ? round($subtotalItem / (int) $item->quantity, 2) : $subtotalItem;

                $concepts[] = [
                    'clave_prod_serv' => $claveProdServ,
                    'clave_unidad'    => $claveUnidad,
                    'unidad'          => $unidadNombre,
                    'descripcion'     => $productName . ($order->vehicle ? ' - Vehículo: ' . $order->vehicle : ''),
                    'cantidad'        => (int) $item->quantity,
                    'valor_unitario'  => $unitPriceSinIva,
                    'importe'         => $subtotalItem,
                    'iva_importe'     => $ivaItem,
                ];
            }
        } elseif ($order) {
            // Order with total but no items
            $netTotal = (float) $order->total;
            $totalWithoutIva = round($netTotal / 1.16, 2);
            $ivaItem = round($netTotal - $totalWithoutIva, 2);

            $concepts[] = [
                'clave_prod_serv' => '78181500',
                'clave_unidad'    => 'E48',
                'unidad'          => 'Servicio',
                'descripcion'     => 'Servicio de mantenimiento automotriz integral - Orden #' . $order->id . ' (' . ($order->vehicle ?? 'General') . ')',
                'cantidad'        => 1,
                'valor_unitario'  => $totalWithoutIva,
                'importe'         => $totalWithoutIva,
                'iva_importe'     => $ivaItem,
            ];
        } elseif ($customConcept) {
            $netTotal = round((float) ($customConcept['valor_unitario'] ?? 1000) * (int) ($customConcept['cantidad'] ?? 1), 2);
            $subtotal = round($netTotal / 1.16, 2);
            $ivaItem  = round($netTotal - $subtotal, 2);
            $unitPriceSinIva = (int) ($customConcept['cantidad'] ?? 1) > 0 ? round($subtotal / (int) ($customConcept['cantidad'] ?? 1), 2) : $subtotal;

            $concepts[] = [
                'clave_prod_serv' => $customConcept['clave_prod_serv'] ?? '78181500',
                'clave_unidad'    => $customConcept['clave_unidad'] ?? 'E48',
                'unidad'          => 'Servicio',
                'descripcion'     => $customConcept['descripcion'] ?? 'Servicio de reparación mecánica automotriz general',
                'cantidad'        => (int) ($customConcept['cantidad'] ?? 1),
                'valor_unitario'  => $unitPriceSinIva,
                'importe'         => $subtotal,
                'iva_importe'     => $ivaItem,
            ];
        }

        return $concepts;
    }

    /**
     * Generate simulated cryptographic digital seal string (RSA-SHA256 Base64).
     */
    protected function generateSeal(string $payload): string
    {
        $hash = hash_hmac('sha256', $payload, 'SAT_SECRET_KEY_MOCK_2026_MECHANIC_APP');
        return base64_encode(pack('H*', $hash) . str_repeat('Xf', 24));
    }

    /**
     * Generate official XML structure conforming to CFDI 4.0 specification.
     */
    protected function generateXml(array $data): string
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $comprobante = $xml->createElementNS('http://www.sat.gob.mx/cfd/4', 'cfdi:Comprobante');
        $comprobante->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $comprobante->setAttribute('xsi:schemaLocation', 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd');
        $comprobante->setAttribute('Version', '4.0');
        $comprobante->setAttribute('Serie', $data['series']);
        $comprobante->setAttribute('Folio', (string) $data['folio']);
        $comprobante->setAttribute('Fecha', $data['fecha_emision']);
        $comprobante->setAttribute('Sello', $data['sello_emisor']);
        $comprobante->setAttribute('FormaPago', $data['forma_pago']);
        $comprobante->setAttribute('NoCertificado', self::NO_CERTIFICADO_EMISOR);
        $comprobante->setAttribute('SubTotal', number_format($data['subtotal'], 2, '.', ''));
        $comprobante->setAttribute('Moneda', 'MXN');
        $comprobante->setAttribute('Total', number_format($data['total'], 2, '.', ''));
        $comprobante->setAttribute('TipoDeComprobante', 'I'); // Ingreso
        $comprobante->setAttribute('Exportacion', '01'); // No aplica
        $comprobante->setAttribute('MetodoPago', $data['metodo_pago']);
        $comprobante->setAttribute('LugarExpedicion', self::EMISOR_LUGAR_EXPEDICION);

        // Emisor
        $emisor = $xml->createElement('cfdi:Emisor');
        $emisor->setAttribute('Rfc', self::EMISOR_RFC);
        $emisor->setAttribute('Nombre', self::EMISOR_RAZON_SOCIAL);
        $emisor->setAttribute('RegimenFiscal', self::EMISOR_REGIMEN_FISCAL);
        $comprobante->appendChild($emisor);

        // Receptor
        $receptor = $xml->createElement('cfdi:Receptor');
        $receptor->setAttribute('Rfc', $data['rfc_receptor']);
        $receptor->setAttribute('Nombre', $data['razon_social_receptor']);
        $receptor->setAttribute('DomicilioFiscalReceptor', $data['cp_receptor']);
        $receptor->setAttribute('RegimenFiscalReceptor', $data['regimen_receptor']);
        $receptor->setAttribute('UsoCFDI', $data['uso_cfdi']);
        $comprobante->appendChild($receptor);

        // Conceptos
        $conceptos = $xml->createElement('cfdi:Conceptos');
        foreach ($data['items'] as $item) {
            $concepto = $xml->createElement('cfdi:Concepto');
            $concepto->setAttribute('ClaveProdServ', $item['clave_prod_serv']);
            $concepto->setAttribute('Cantidad', (string) $item['cantidad']);
            $concepto->setAttribute('ClaveUnidad', $item['clave_unidad']);
            $concepto->setAttribute('Unidad', $item['unidad']);
            $concepto->setAttribute('Descripcion', $item['descripcion']);
            $concepto->setAttribute('ValorUnitario', number_format($item['valor_unitario'], 2, '.', ''));
            $concepto->setAttribute('Importe', number_format($item['importe'], 2, '.', ''));
            $concepto->setAttribute('ObjetoImp', '02'); // Sí objeto de impuesto

            // Impuestos del concepto
            $impuestosConcepto = $xml->createElement('cfdi:Impuestos');
            $trasladosConcepto = $xml->createElement('cfdi:Traslados');
            $trasladoConcepto  = $xml->createElement('cfdi:Traslado');
            $trasladoConcepto->setAttribute('Base', number_format($item['importe'], 2, '.', ''));
            $trasladoConcepto->setAttribute('Impuesto', '002'); // IVA
            $trasladoConcepto->setAttribute('TipoFactor', 'Tasa');
            $trasladoConcepto->setAttribute('TasaOCuota', '0.160000');
            $trasladoConcepto->setAttribute('Importe', number_format($item['iva_importe'], 2, '.', ''));
            $trasladosConcepto->appendChild($trasladoConcepto);
            $impuestosConcepto->appendChild($trasladosConcepto);
            $concepto->appendChild($impuestosConcepto);

            $conceptos->appendChild($concepto);
        }
        $comprobante->appendChild($conceptos);

        // Impuestos globales
        $impuestosGlobal = $xml->createElement('cfdi:Impuestos');
        $impuestosGlobal->setAttribute('TotalImpuestosTrasladados', number_format($data['iva_trasladado'], 2, '.', ''));
        $trasladosGlobal = $xml->createElement('cfdi:Traslados');
        $trasladoGlobal  = $xml->createElement('cfdi:Traslado');
        $trasladoGlobal->setAttribute('Base', number_format($data['subtotal'], 2, '.', ''));
        $trasladoGlobal->setAttribute('Impuesto', '002');
        $trasladoGlobal->setAttribute('TipoFactor', 'Tasa');
        $trasladoGlobal->setAttribute('TasaOCuota', '0.160000');
        $trasladoGlobal->setAttribute('Importe', number_format($data['iva_trasladado'], 2, '.', ''));
        $trasladosGlobal->appendChild($trasladoGlobal);
        $impuestosGlobal->appendChild($trasladosGlobal);
        $comprobante->appendChild($impuestosGlobal);

        // Complemento Timbre Fiscal Digital (PAC)
        $complemento = $xml->createElement('cfdi:Complemento');
        $tfd = $xml->createElementNS('http://www.sat.gob.mx/TimbreFiscalDigital', 'tfd:TimbreFiscalDigital');
        $tfd->setAttribute('Version', '1.1');
        $tfd->setAttribute('UUID', $data['uuid']);
        $tfd->setAttribute('FechaTimbrado', $data['fecha_timbrado']);
        $tfd->setAttribute('RfcProvCertif', self::PAC_RFC);
        $tfd->setAttribute('SelloCFD', $data['sello_emisor']);
        $tfd->setAttribute('NoCertificadoSAT', self::NO_CERTIFICADO_SAT);
        $tfd->setAttribute('SelloSAT', $data['sello_sat']);
        $complemento->appendChild($tfd);
        $comprobante->appendChild($complemento);

        $xml->appendChild($comprobante);

        return $xml->saveXML();
    }
}
