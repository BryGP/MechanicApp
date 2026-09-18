<?php

namespace App\Reports;

class GastosPorCategoriaReport
{
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
