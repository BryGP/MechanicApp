<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Invoice
 * 
 * Represents an official CFDI 4.0 fiscal invoice with cryptographic stamps,
 * customer tax identification, and SAT verification references.
 * 
 * @package App\Models
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'series',
        'folio',
        'order_id',
        'rfc_receptor',
        'razon_social_receptor',
        'regimen_fiscal_receptor',
        'codigo_postal_receptor',
        'uso_cfdi',
        'forma_pago',
        'metodo_pago',
        'moneda',
        'tipo_cambio',
        'subtotal',
        'iva_trasladado',
        'total',
        'no_certificado_emisor',
        'no_certificado_sat',
        'fecha_emision',
        'fecha_timbrado',
        'sello_emisor',
        'sello_sat',
        'cadena_original',
        'qr_code_url',
        'xml_content',
        'status',
        'fecha_cancelacion',
        'motivo_cancelacion',
    ];

    protected $casts = [
        'folio'             => 'integer',
        'subtotal'          => 'float',
        'iva_trasladado'    => 'float',
        'total'             => 'float',
        'tipo_cambio'       => 'float',
        'fecha_emision'     => 'datetime',
        'fecha_timbrado'    => 'datetime',
        'fecha_cancelacion' => 'datetime',
    ];

    /**
     * Associated workshop repair order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
