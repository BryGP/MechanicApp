<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run all application seeders in dependency order.
     */
    public function run(): void
    {
        $this->call(ProductSeeder::class);
        $this->call(OrderSeeder::class);
    }
}
