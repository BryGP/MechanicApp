<?php

namespace App\Reports;

class ClientesFrecuentesReport
{
    public static function info(): array
    {
        return [
            'id'          => 'clientes_vip',
            'title'       => 'Clientes VIP y Rentabilidad por Flotilla',
            'category'    => 'Ventas',
            'icon'        => 'orders',
            'description' => 'Identifica a los clientes que más visitas registran y mayor facturación aportan al taller, junto con su ticket promedio.',
            'query'       => "
                SELECT 
                    customer_name AS cliente,
                    COUNT(*) AS total_visitas,
                    ROUND(SUM(total), 2) AS facturacion_total,
                    ROUND(AVG(total), 2) AS ticket_promedio,
                    MAX(vehicle) AS ultimo_vehiculo_atendido,
                    DATE_FORMAT(MAX(created_at), '%Y-%m-%d') AS fecha_ultima_visita
                FROM orders
                WHERE customer_name IS NOT NULL AND customer_name != ''
                GROUP BY customer_name
                ORDER BY facturacion_total DESC
            ",
        ];
    }
}
