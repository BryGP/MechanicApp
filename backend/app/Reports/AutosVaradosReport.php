<?php

namespace App\Reports;

class AutosVaradosReport
{
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
