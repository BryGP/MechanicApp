<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================================
 * CLASE: OrderItem (Modelo Eloquent)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Representa cada partida individual o renglón de cobro dentro de una orden de 
 * servicio (tabla 'order_items'). Asocia una refacción o servicio de catálogo a 
 * la orden, registrando la cantidad empleada, el precio unitario y el subtotal.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Patrón Snapshot Pricing (Congelamiento de Precio Histórico):
 *   Almacena en 'unit_price' el importe unitario exacto al momento de generar 
 *   la orden. Si el taller incrementa el costo de un filtro o galón de aceite 
 *   meses después, las órdenes previas conservan intacto su valor contable.
 * - Atributo Virtual / Interoperabilidad de API ('qty' <-> 'quantity'):
 *   Implementa un Accessor & Mutator con el arreglo '$appends' que mapea de 
 *   forma transparente la columna SQL 'quantity' con la propiedad JSON 'qty' 
 *   esperada por los componentes y formularios del frontend.
 * - Integridad Referencial Cruzada:
 *   Pertenece a una Orden ('belongsTo') y a un Producto ('belongsTo'). La base 
 *   de datos restringe la eliminación del producto si existe alguna orden vinculada.
 *
 * PROPIEDADES DE LA TABLA 'order_items':
 * @property int            $id          Clave primaria auto-incremental
 * @property int            $order_id    Clave foránea hacia orders.id (ON DELETE CASCADE)
 * @property int            $product_id  Clave foránea hacia products.id (ON DELETE RESTRICT)
 * @property int            $quantity    Número de piezas instaladas o unidades de servicio
 * @property int            $qty         Alias virtual calculado de 'quantity'
 * @property float          $unit_price  Precio unitario congelado al momento de la venta
 * @property float          $subtotal    Importe calculado (qty * unit_price)
 * @property \Carbon\Carbon $created_at  Timestamp de creación
 * @property \Carbon\Carbon $updated_at  Timestamp de actualización
 * ============================================================================
 */
class OrderItem extends Model
{
    // =========================================================================
    // SECCIÓN 1: CONFIGURACIÓN DE CAMPOS Y SERIALIZACIÓN
    // =========================================================================

    /**
     * Atributos permitidos para asignación masiva segura.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'qty',
        'unit_price',
        'subtotal',
    ];

    /**
     * Atributos dinámicos calculados que se agregan a la salida JSON.
     *
     * @var list<string>
     */
    protected $appends = ['qty'];

    // =========================================================================
    // SECCIÓN 2: ACCESORES Y MUTADORES DE COMPATIBILIDAD (QTY <-> QUANTITY)
    // =========================================================================

    /**
     * // Función de acceso (getter) para resolver el alias 'qty'
     * 
     * Retorna el valor de la columna 'quantity' al consultar la propiedad 'qty'.
     *
     * @return int|null
     */
    public function getQtyAttribute()
    {
        return $this->attributes['quantity'] ?? null;
    }

    /**
     * // Función de mutación (setter) para guardar mediante el alias 'qty'
     * 
     * Asigna el valor directamente a la columna física 'quantity' en la base de datos.
     *
     * @param  int  $value
     * @return void
     */
    public function setQtyAttribute($value)
    {
        $this->attributes['quantity'] = $value;
    }

    // =========================================================================
    // SECCIÓN 3: RELACIONES ELOQUENT INVERSAS
    // =========================================================================

    /**
     * // Función de relación con el producto o servicio del catálogo
     * 
     * Retorna la instancia de 'Product' asociada a este renglón.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * // Función de relación con la orden de servicio padre
     * 
     * Retorna la orden principal a la que pertenece esta partida de trabajo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
