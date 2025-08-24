<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Product::insert([
            // Producto 1
            [
                'name' => 'Aceite 10W-30',
                'sku' => 'ACE-10W30',
                'price' => 180.00,
                'stock' => 20,
                'min_stock' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Producto 2
            [
                'name' => 'Filtro de aceite',
                'sku' => 'FILT-OIL',
                'price' => 95.00,
                'stock' => 35,
                'min_stock' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Producto 3
            [
                'name' => 'Filtro de aire',
                'sku' => 'FILT-AIR',
                'price' => 120.00,
                'stock' => 15,
                'min_stock' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}