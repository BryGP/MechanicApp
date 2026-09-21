<?php

namespace App\Reports;

/**
 * ============================================================================
 * CLASE: ValoracionInventarioReport (Reporte de Valoración Económica y Capital)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Cuantifica el valor patrimonial total de todas las refacciones y fluidos 
 * resguardados en las estanterías del taller. Permite al dueño del taller y al 
 * contador conocer el capital activo inmovilizado en almacén para balances contables.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Segmentación de Riesgo de Capital (CASE WHEN):
 *   Clasifica los productos según el valor monetario retenido:
 *   * ALTO CAPITAL (>= $5,000 MXN) : Requiere resguardo de alta seguridad e inventario cíclico.
 *   * CAPITAL MEDIO (>= $2,000 MXN): Refacciones de costo moderado.
 *   * BAJO CAPITAL (< $2,000 MXN)  : Consumibles menores (tornillería, grapas, abrazaderas).
 * - Filtro de existencias activas (stock > 0) y ordenamiento por capital descendente.
 * ============================================================================
 */
class ValoracionInventarioReport
{
    // =========================================================================
    // SECCIÓN: DEFINICIÓN DE METADATOS Y SENTENCIA SQL ANALÍTICA
    // =========================================================================

    /**
     * // Función para obtener la configuración y consulta de valoración de almacén
     * 
     * Retorna los metadatos y la sentencia SQL para valoración económica de existencias.
     *
     * @return array
     */
    public static function info(): array
    {
        return [
            'id'          => 'valoracion_inventario',
            'title'       => 'Valoración y Capital Inmovilizado en Almacén',
            'category'    => 'Inventario',
            'icon'        => 'box',
            'description' => 'Calcula el dinero total invertido en refacciones en los anaqueles del taller, clasificando el nivel de capital retenido.',
            'query'       => "
                SELECT 
                    sku,
                    name AS refaccion,
                    stock AS piezas_en_estante,
                    price AS precio_unitario,
                    ROUND(stock * price, 2) AS capital_inmovilizado,
                    CASE 
                        WHEN (stock * price) >= 5000 THEN 'ALTO CAPITAL'
                        WHEN (stock * price) >= 2000 THEN 'CAPITAL MEDIO'
                        ELSE 'BAJO CAPITAL'
                    END AS prioridad_capital
                FROM products
                WHERE stock > 0
                ORDER BY capital_inmovilizado DESC
            ",
        ];
    }
}
