<?php

namespace App\Reports;

class InventarioMuertoReport
{
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
                WHERE oi.id IS NULL AND p.stock > 0
                ORDER BY dinero_congelado DESC
            ",
        ];
    }
}
