<?php

namespace App\Reports;

/**
 * ============================================================================
 * CLASE: GastosPorCategoriaReport (Reporte Financiero de Egresos y Fuga de Gastos)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Proporciona una radiografía financiera detallada de las salidas de dinero del 
 * taller. Agrupa los egresos por categoría operativa (nóminas, refacciones de 
 * urgencia, herramientas, renta, servicios) para auditoría de costos fijos y variables.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Dispersión y Análisis Estadístico de Egresos:
 *   Calcula el total desembolsado, el promedio por partida y los extremos (gasto 
 *   mínimo y gasto máximo), permitiendo identificar anomalías o pagos atípicos.
 * - Priorización por Impacto Contable:
 *   Ordena automáticamente de mayor a menor gasto acumulado para enfocar de 
 *   inmediato las decisiones de optimización y reducción de costos.
 * ============================================================================
 */
class GastosPorCategoriaReport
{
    // =========================================================================
    // SECCIÓN: DEFINICIÓN DE METADATOS Y SENTENCIA SQL ANALÍTICA
    // =========================================================================

    /**
     * // Función para obtener la configuración y consulta de gastos operativos
     * 
     * Retorna los metadatos y la sentencia SQL agregada sobre la tabla 'expenses'.
     *
     * @return array
     */
    public static function info(): array
    {
        return [
            'id'          => 'gastos_por_categoria',
            'title'       => 'Radiografía de Fuga de Gastos Operativos',
            'category'    => 'Finanzas',
            'icon'        => 'expenses',
            'description' => 'Desglosa en qué conceptos operativos se drena el dinero del taller (nómina, refacciones de urgencia, renta, herramientas).',
            'query'       => "
                SELECT 
                    category AS categoria,
                    COUNT(*) AS total_desembolsos,
                    ROUND(SUM(amount), 2) AS total_gastado,
                    ROUND(AVG(amount), 2) AS promedio_por_gasto,
                    ROUND(MIN(amount), 2) AS gasto_menor,
                    ROUND(MAX(amount), 2) AS gasto_mayor
                FROM expenses
                GROUP BY category
                ORDER BY total_gastado DESC
            ",
        ];
    }
}
