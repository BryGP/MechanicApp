<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================================
 * CLASE: Expense (Modelo Eloquent)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Representa la entidad contable de un egreso o gasto operativo en la base de datos 
 * (tabla 'expenses'). Mapea cada salida de efectivo o transferencia efectuada 
 * por el taller para nóminas, compra de refacciones de urgencia, renta o insumos.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Casteo Financiero de Precisión ('decimal:2'):
 *   Evita los errores clásicos de precisión de punto flotante de PHP al forzar 
 *   que el atributo 'amount' se maneje siempre con 2 posiciones decimales exactas.
 * - Normalización Temporal ISO ('date:Y-m-d'):
 *   Convierte de manera transparente la fecha de la base de datos en una cadena 
 *   homogénea para consumo inmediato en el frontend sin desfase de huso horario.
 *
 * PROPIEDADES DE LA TABLA 'expenses':
 * @property int            $id              Clave primaria auto-incremental
 * @property string         $concept         Descripción o concepto del gasto
 * @property string         $category        Categoría contable (Refacciones, Nómina, etc.)
 * @property float          $amount          Importe desembolsado en MXN (2 decimales)
 * @property string|null    $payment_method  Método de pago (Efectivo, Transferencia, etc.)
 * @property string|null    $reference       Número de factura, ticket o folio bancario
 * @property string         $expense_date    Fecha de aplicación contable (Y-m-d)
 * @property \Carbon\Carbon $created_at      Timestamp de registro en el sistema
 * @property \Carbon\Carbon $updated_at      Timestamp de última modificación
 * ============================================================================
 */
class Expense extends Model
{
    // =========================================================================
    // SECCIÓN 1: CONFIGURACIÓN DE CAMPOS Y ASIGNACIÓN MASIVA
    // =========================================================================

    /**
     * Campos habilitados para inserción masiva segura (Mass Assignment).
     * Protege la integridad de la base de datos contra campos no permitidos.
     *
     * @var list<string>
     */
    protected $fillable = [
        'concept',
        'category',
        'amount',
        'payment_method',
        'reference',
        'expense_date',
    ];

    // =========================================================================
    // SECCIÓN 2: TRANSFORMACIÓN DE TIPOS Y CASTS NATIVOS
    // =========================================================================

    /**
     * Reglas de casteo de atributos para serialización y consistencia de datos.
     * Garantiza precisión decimal monetaria y formato de fecha estándar.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date:Y-m-d',
    ];
}
