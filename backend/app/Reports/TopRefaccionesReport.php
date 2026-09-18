<?php

namespace App\Reports;

class TopRefaccionesReport
{
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
