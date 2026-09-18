<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder
 *
 * Main entry point for all database seeders.
 * Run with: php artisan db:seed
 *
 * Execution order matters — seeders that depend on other tables
 * (e.g. OrderSeeder would need products first) should be listed
 * after their dependencies.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run all application seeders in dependency order.
     */
    public function run(): void
    {
        // Seed workshop inventory products first (orders depend on products)
        $this->call(ProductSeeder::class);
    }
}
