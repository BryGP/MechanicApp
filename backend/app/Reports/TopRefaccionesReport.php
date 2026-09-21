<?php

namespace App\Reports;

/**
 * ============================================================================
 * CLASE: TopRefaccionesReport (Reporte de Refacciones de Alta Rotación)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Genera el ranking de productos e insumos con mayor demanda en las bahías del 
 * taller mecánico. Permite negociar mejores precios de mayoreo con distribuidores 
 * para los insumos de mayor volumen de venta.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Métrica de Penetración en Órdenes (COUNT(DISTINCT oi.order_id)):
 *   Mide la frecuencia real de utilización independientemente del volumen, evitando 
 *   que una orden atípica con muchas piezas distorsione la popularidad del producto.
 * - Consolidación de Ingresos por Producto:
 *   Suma los subtotales cobrados para determinar no solo qué pieza se gasta más, 
 *   sino cuál aporta más margen y dinero directo al taller.
 * ============================================================================
 */
class TopRefaccionesReport
{
    // =========================================================================
    // SECCIÓN: DEFINICIÓN DE METADATOS Y SENTENCIA SQL ANALÍTICA
    // =========================================================================

    /**
     * // Función para obtener la configuración y consulta de refacciones populares
     * 
     * Retorna los metadatos y la consulta con métricas de demanda e ingresos acumulados.
     *
     * @return array
     */
    public static function info(): array
    {
        return [
            'id'          => 'top_refacciones',
            'title'       => 'Refacciones Más Demandadas en Bahías',
            'category'    => 'Ventas',
            'icon'        => 'revenue',
            'description' => 'Ranking de piezas con mayor rotación en el taller, en cuántas órdenes aparecen y total recaudado.',
            'query'       => "
                SELECT 
                    p.sku,
                    p.name AS refaccion,
                    COUNT(DISTINCT oi.order_id) AS ordenes_en_que_aparece,
                    SUM(oi.quantity) AS total_piezas_usadas,
                    SUM(oi.subtotal) AS ingreso_generado
                FROM products p
                INNER JOIN order_items oi ON p.id = oi.product_id
                GROUP BY p.id, p.sku, p.name
                ORDER BY total_piezas_usadas DESC
            ",
        ];
    }
}
