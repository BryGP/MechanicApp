<?php

namespace App\Reports;

class StockCriticoReport
{
    public static function info(): array
    {
        return [
            'id'          => 'stock_critico',
            'title'       => 'Semáforo de Stock Crítico y Compras',
            'category'    => 'Inventario',
            'icon'        => '⚠️',
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
                ORDER BY stock ASC
            ",
        ];
    }
}
