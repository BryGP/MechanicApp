<?php

namespace App\Reports;

/**
 * ============================================================================
 * CLASE: StockCriticoReport (Semáforo de Abastecimiento y Órdenes de Compra)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Alerta al personal de almacén y compras sobre insumos agotados o cercanos a 
 * terminarse. Evalúa las existencias actuales frente al umbral mínimo de seguridad 
 * establecido para cada refacción y calcula la sugerencia de compra.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Algoritmo de Sugerencia de Reorden en SQL:
 *   Calcula automáticamente la cantidad de piezas a pedir al proveedor mediante 
 *   la fórmula de reposición: '(min_stock - stock) + 5' piezas de colchón de seguridad.
 * - Semáforo Visual de Tres Niveles:
 *   Etiqueta cada artículo como 'AGOTADO' (stock = 0), 'STOCK CRITICO' (<= min_stock)
 *   o 'OK' para agilizar las decisiones del jefe de refacciones.
 * ============================================================================
 */
class StockCriticoReport
{
    // =========================================================================
    // SECCIÓN: DEFINICIÓN DE METADATOS Y SENTENCIA SQL ANALÍTICA
    // =========================================================================

    /**
     * // Función para obtener la configuración y consulta de stock crítico
     * 
     * Retorna los metadatos y la consulta con cálculo dinámico de pedido sugerido.
     *
     * @return array
     */
    public static function info(): array
    {
        return [
            'id'          => 'stock_critico',
            'title'       => 'Semáforo de Stock Crítico y Compras',
            'category'    => 'Inventario',
            'icon'        => 'alert',
            'description' => 'Detecta refacciones agotadas o por debajo del stock mínimo y calcula cuántas piezas pedir al proveedor.',
            'query'       => "
                SELECT 
                    sku,
                    name AS producto,
                    stock AS stock_actual,
                    min_stock AS stock_minimo,
                    CASE 
                        WHEN stock = 0 THEN 'AGOTADO'
                        WHEN stock <= min_stock THEN 'STOCK CRITICO'
                        ELSE 'OK'
                    END AS semaforo,
                    CASE 
                        WHEN stock <= min_stock THEN (min_stock - stock) + 5
                        ELSE 0
                    END AS sugerido_a_comprar
                FROM products
                WHERE is_service = 0
                ORDER BY stock ASC
            ",
        ];
    }
}
