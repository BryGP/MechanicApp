<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * ProductSeeder
 *
 * Seeds the products table with a realistic set of workshop inventory items.
 * Run via: php artisan db:seed
 *          php artisan db:seed --class=ProductSeeder
 *
 * These are sample products representing typical auto-repair shop supplies.
 * Each entry includes a min_stock value used to trigger low-inventory alerts.
 */
class ProductSeeder extends Seeder
{
    /**
     * Insert seed products into the database.
     * Uses bulk insert (single query) for performance.
     */
    public function run(): void
    {
        \App\Models\Product::insert([
            // Engine oil — high turnover, low minimum threshold
            [
                'name'       => 'Aceite 10W-30',
                'sku'        => 'ACE-10W30',
                'price'      => 180.00,
                'stock'      => 20,
                'min_stock'  => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Oil filter — paired with every oil change service
            [
                'name'       => 'Filtro de aceite',
                'sku'        => 'FILT-OIL',
                'price'      => 95.00,
                'stock'      => 35,
                'min_stock'  => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Air filter — replaced every 15,000–30,000 km
            [
                'name'       => 'Filtro de aire',
                'sku'        => 'FILT-AIR',
                'price'      => 120.00,
                'stock'      => 15,
                'min_stock'  => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
