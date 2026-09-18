<?php

namespace App\Reports;

class ValoracionInventarioReport
{
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
