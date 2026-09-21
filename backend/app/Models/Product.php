<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================================
 * CLASE: Product (Modelo Eloquent)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Representa cualquier artículo disponible en el taller mecánico (tabla 'products'), 
 * abarcando tanto refacciones físicas en anaquel (filtros, aceites, balatas, amortiguadores)
 * como servicios de mano de obra (alineación y balanceo, escaneo por computadora, afinación).
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Soporte Bimodal Refacción / Servicio ('is_service'):
 *   Permite que el taller use un catálogo consolidado. Si 'is_service' es true, 
 *   el elemento actúa como mano de obra sin control de stock; si es false, se 
 *   convierte en un insumo físico sujeto a deducción automática en órdenes.
 * - Monitoreo de Stock Crítico ('min_stock'):
 *   Cada refacción posee un umbral mínimo de seguridad. Cuando 'stock' cae por 
 *   debajo de 'min_stock', el sistema dispara alertas automáticas de reposición.
 * - Casteo Booleano Estricto:
 *   Garantiza que 'is_service' siempre sea evaluado como tipo primitivo booleano 
 *   (true/false) tanto en PHP como en la carga JSON hacia Vue.
 *
 * PROPIEDADES DE LA TABLA 'products':
 * @property int            $id          Identificador único del producto
 * @property string         $name        Nombre descriptivo o comercial de la refacción/servicio
 * @property string|null    $description Observaciones, especificaciones técnicas o detalles del servicio
 * @property string         $sku         Clave o código alfanumérico único de inventario
 * @property float          $price       Precio unitario al público en MXN
 * @property int            $stock       Existencias físicas en almacén (0 para servicios)
 * @property int            $min_stock   Umbral mínimo para alertas de compra (0 para servicios)
 * @property bool           $is_service  Indica si es un servicio de taller (true) o refacción (false)
 * @property \Carbon\Carbon $created_at  Fecha de alta en catálogo
 * @property \Carbon\Carbon $updated_at  Fecha de última modificación
 * ============================================================================
 */
class Product extends Model
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
        'name',
        'description',
        'sku',
        'price',
        'stock',
        'min_stock',
        'is_service',
    ];

    // =========================================================================
    // SECCIÓN 2: TRANSFORMACIÓN DE TIPOS Y CASTS BOOLEANOS
    // =========================================================================

    /**
     * Reglas de casteo de atributos para serialización JSON precisa.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_service' => 'boolean',
    ];
}
