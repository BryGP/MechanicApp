<?php

namespace App\Reports;

/**
 * ============================================================================
 * CLASE: AutosVaradosReport (Reporte Operativo de Cuellos de Botella)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Supervisa el tiempo de permanencia de los vehículos en las bahías de servicio 
 * cuyas órdenes permanecen con estatus 'open' o 'in_progress'. Permite al jefe 
 * de taller detectar autos rezagados, evitar sobrecupo y acelerar las entregas.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Semáforo Automatizado en SQL (CASE WHEN):
 *   Clasifica los vehículos en tiempo real según su estancia calculada con DATEDIFF:
 *   * CRÍTICO (>7 DÍAS) : Riesgo de cliente insatisfecho o refacción faltante.
 *   * ATENCIÓN (3-6 DÍAS) : Trabajo en curso que requiere seguimiento.
 *   * EN TIEMPO (<3 DÍAS) : Ritmo de trabajo óptimo en taller.
 * - Formateo directo de fechas y ordenamiento por urgencia de entrega.
 * ============================================================================
 */
class AutosVaradosReport
{
    // =========================================================================
    // SECCIÓN: DEFINICIÓN DE METADATOS Y SENTENCIA SQL ANALÍTICA
    // =========================================================================

    /**
     * // Función para obtener la configuración y consulta de autos varados
     * 
     * Retorna los metadatos de presentación (título, categoría, icono) y la 
     * consulta SQL pura ejecutada directamente sobre la tabla 'orders'.
     *
     * @return array
     */
    public static function info(): array
    {
        return [
            'id'          => 'autos_varados',
            'title'       => 'Autos Varados y Cuello de Botella en Bahías',
            'category'    => 'Operaciones',
            'icon'        => 'tools',
            'description' => 'Supervisa autos con órdenes abiertas o en proceso, calculando cuántos días llevan ocupando una bahía para evitar retrasos.',
            'query'       => "
                SELECT 
                    id AS orden_folio,
                    customer_name AS cliente,
                    vehicle AS vehiculo,
                    status AS estatus_actual,
                    DATEDIFF(NOW(), created_at) AS dias_en_taller,
                    DATE_FORMAT(created_at, '%Y-%m-%d') AS fecha_ingreso,
                    total AS presupuesto_estimado,
                    CASE 
                        WHEN DATEDIFF(NOW(), created_at) >= 7 THEN 'CRÍTICO (>7 DÍAS)'
                        WHEN DATEDIFF(NOW(), created_at) >= 3 THEN 'ATENCIÓN (3-6 DÍAS)'
                        ELSE 'EN TIEMPO (<3 DÍAS)'
                    END AS semaforo_entrega
                FROM orders
                WHERE status IN ('open', 'in_progress')
                ORDER BY dias_en_taller DESC
            ",
        ];
    }
}
