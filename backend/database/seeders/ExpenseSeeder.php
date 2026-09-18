<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('expenses')->truncate();

        DB::table('expenses')->insert([
            [
                'concept'        => 'Pago de nómina semanal mecánicos (2 maestros + 1 chalán)',
                'category'       => 'nomina',
                'amount'         => 6800.00,
                'payment_method' => 'transferencia',
                'reference'      => 'NOM-SEM37',
                'expense_date'   => '2026-09-15',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'concept'        => 'Renta mensual nave industrial y bahías taller',
                'category'       => 'renta',
                'amount'         => 8500.00,
                'payment_method' => 'transferencia',
                'reference'      => 'REC-RENT-SEP',
                'expense_date'   => '2026-09-01',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'concept'        => 'Tambo de aceite 20W-50 (200L) Distribuidora Mobil',
                'category'       => 'refacciones',
                'amount'         => 4250.00,
                'payment_method' => 'transferencia',
                'reference'      => 'FAC-MOBIL-8841',
                'expense_date'   => '2026-09-05',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'concept'        => 'Recibo de luz CFE (compresores y elevador electrohidráulico)',
                'category'       => 'servicios',
                'amount'         => 1890.00,
                'payment_method' => 'tarjeta',
                'reference'      => 'CFE-BIM-SEP',
                'expense_date'   => '2026-09-10',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'concept'        => 'Juego de dados de impacto 1/2 y matraca neumática',
                'category'       => 'herramientas',
                'amount'         => 1450.00,
                'payment_method' => 'efectivo',
                'reference'      => 'TKT-FERR-4421',
                'expense_date'   => '2026-09-12',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'concept'        => 'Garrafones de agua y artículos de limpieza taller',
                'category'       => 'otros',
                'amount'         => 380.00,
                'payment_method' => 'efectivo',
                'reference'      => 'CH-CAJA-09',
                'expense_date'   => '2026-09-14',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }
}
