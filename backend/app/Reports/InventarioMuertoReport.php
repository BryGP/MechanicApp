<?php

namespace App\Reports;

/**
 * ============================================================================
 * CLASE: InventarioMuertoReport (Reporte de Refacciones sin Rotación)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Identifica las piezas, fluidos y refacciones que tienen existencias en los 
 * anaqueles pero que jamás han sido instaladas en ninguna orden de servicio.
 * Es crucial para liquidar mercancía estancada y recuperar flujo de efectivo.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Detección por Anti-Join (LEFT JOIN ... WHERE oi.id IS NULL):
 *   Aprovecha el índice foráneo de 'order_items.product_id' para aislar 
 *   instantáneamente los productos con cero movimientos históricos de salida.
 * - Cálculo de Capital Congelado:
 *   Multiplica el stock estancado por su precio unitario para mostrar el dinero 
 *   líquido exacto que el taller tiene retenido sin rentabilidad.
 * ============================================================================
 */
class InventarioMuertoReport
{
    // =========================================================================
    // SECCIÓN: DEFINICIÓN DE METADATOS Y SENTENCIA SQL ANALÍTICA
    // =========================================================================

    /**
     * // Función para obtener la configuración y consulta de inventario muerto
     * 
     * Retorna los metadatos y la consulta SQL que detecta piezas sin ventas.
     *
     * @return array
     */
    public static function info(): array
    {
        return [
            'id'          => 'inventario_muerto',
            'title'       => 'Inventario Muerto (Refacciones sin Rotación)',
            'category'    => 'Inventario',
            'icon'        => 'alert',
            'description' => 'Detecta refacciones almacenadas que nunca se han instalado en ninguna orden de servicio (dinero congelado en anaqueles).',
            'query'       => "
                SELECT 
                    p.sku,
                    p.name AS refaccion,
                    p.stock AS piezas_estancadas,
                    p.price AS precio_unitario,
                    ROUND(p.stock * p.price, 2) AS dinero_congelado,
                    DATE_FORMAT(p.created_at, '%Y-%m-%d') AS fecha_ingreso_catalogo
                FROM products p
                LEFT JOIN order_items oi ON p.id = oi.product_id
                WHERE oi.id IS NULL AND p.stock > 0 AND p.is_service = 0
                ORDER BY dinero_congelado DESC
            ",
        ];
    }
}
