<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class InvoiceResource
 * 
 * Transforms Invoice Eloquent model into a clean, typed JSON fiscal payload.
 * 
 * @package App\Http\Resources
 */
class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'uuid'                    => $this->uuid,
            'series'                  => $this->series,
            'folio'                   => $this->folio,
            'full_folio'              => $this->series . '-' . str_pad($this->folio, 4, '0', STR_PAD_LEFT),
            'order_id'                => $this->order_id,
            'order_vehicle'           => $this->order?->vehicle,
            'order_customer'          => $this->order?->customer_name,
            'rfc_receptor'            => $this->rfc_receptor,
            'razon_social_receptor'   => $this->razon_social_receptor,
            'regimen_fiscal_receptor' => $this->regimen_fiscal_receptor,
            'codigo_postal_receptor'  => $this->codigo_postal_receptor,
            'uso_cfdi'                => $this->uso_cfdi,
            'forma_pago'              => $this->forma_pago,
            'metodo_pago'             => $this->metodo_pago,
            'moneda'                  => $this->moneda,
            'tipo_cambio'             => (float) $this->tipo_cambio,
            'subtotal'                => (float) $this->subtotal,
            'iva_trasladado'          => (float) $this->iva_trasladado,
            'total'                   => (float) $this->total,
            'no_certificado_emisor'   => $this->no_certificado_emisor,
            'no_certificado_sat'      => $this->no_certificado_sat,
            'fecha_emision'           => $this->fecha_emision?->toISOString(),
            'fecha_timbrado'          => $this->fecha_timbrado?->toISOString(),
            'sello_emisor'            => $this->sello_emisor,
            'sello_sat'               => $this->sello_sat,
            'cadena_original'         => $this->cadena_original,
            'qr_code_url'             => $this->qr_code_url,
            'status'                  => $this->status,
            'fecha_cancelacion'       => $this->fecha_cancelacion?->toISOString(),
            'motivo_cancelacion'      => $this->motivo_cancelacion,
            'created_at'              => $this->created_at?->toISOString(),
            'order'                   => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
