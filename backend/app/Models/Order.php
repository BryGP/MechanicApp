<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================================
 * CLASE: Order (Modelo Eloquent)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Representa la orden maestra de trabajo o servicio mecánico asignada a un 
 * vehículo y cliente (tabla 'orders'). Es el eje central que coordina el 
 * diagnóstico, las refacciones instaladas, la mano de obra aplicada y el 
 * cobro total de la reparación automotriz.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Ciclo de Vida y Trazabilidad Operativa de Taller:
 *   Modela el flujo de reparación del taller mediante estados bien definidos:
 *   'open' (ingresado) -> 'in_progress' (en bahía) -> 'done' (listo) -> 'delivered' (entregado).
 * - Scopes Locales Reutilizables (Local Scopes):
 *   Facilita consultas semánticas como 'Order::open()->get()' para el tablero activo
 *   o 'Order::closed()->get()' para el historial de ventas cerradas.
 * - Relación Uno a Muchos con Cascade Delete:
 *   Vincula directamente las partidas hijas de 'OrderItem'. Al removerse una orden,
 *   sus líneas de detalle se limpian de manera segura sin dejar registros huérfanos.
 *
 * PROPIEDADES DE LA TABLA 'orders':
 * @property int            $id             Folio numérico de la orden
 * @property string         $customer_name  Nombre completo del cliente o flotilla
 * @property string         $vehicle        Descripción del vehículo (modelo, año, placas)
 * @property string|null    $notes          Observaciones, peticiones especiales o diagnóstico previo
 * @property string         $status         Estatus actual: open | in_progress | done | delivered
 * @property float          $total          Total consolidado de la orden en MXN
 * @property \Carbon\Carbon $created_at     Fecha y hora de apertura de la orden
 * @property \Carbon\Carbon $updated_at     Fecha y hora del último movimiento
 * ============================================================================
 */
class Order extends Model
{
    // =========================================================================
    // SECCIÓN 1: CONFIGURACIÓN DE CAMPOS Y ASIGNACIÓN MASIVA
    // =========================================================================

    /**
     * Atributos asignables de forma masiva para creación y actualización.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_name',
        'vehicle',
        'notes',
        'status',
        'total',
    ];

    // =========================================================================
    // SECCIÓN 2: RELACIONES ELOQUENT ENTRE MODELOS
    // =========================================================================

    /**
     * // Función de relación con las partidas (ítems) de la orden
     * 
     * Define la relación 1:N con OrderItem. Una orden contiene múltiples refacciones
     * o servicios facturados, y su borrado en cascada está asegurado a nivel de motor DB.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // =========================================================================
    // SECCIÓN 3: SCOPES LOCALES DE CONSULTA Y FILTRADO
    // =========================================================================

    /**
     * // Función de scope para filtrar órdenes abiertas o pendientes
     * 
     * Permite consultar de forma fluida los vehículos que aún no han concluido:
     * Ejemplo de uso: Order::open()->get()
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * // Función de scope para filtrar órdenes cerradas o entregadas
     * 
     * Permite consultar órdenes finalizadas para reportes de facturación histórica:
     * Ejemplo de uso: Order::closed()->get()
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
}
