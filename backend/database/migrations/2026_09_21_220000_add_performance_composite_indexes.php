<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to add composite performance indexes across high-frequency query tables.
 *
 * Optimizes:
 * - orders: filtering by status and ordering by creation date.
 * - products: stock warning queries filtering out services and comparing stock levels.
 * - expenses: date range and categorical aggregation queries.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'orders_status_created_at_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_service', 'stock', 'min_stock'], 'products_is_service_stock_min_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['expense_date', 'category'], 'expenses_date_category_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_created_at_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_is_service_stock_min_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_date_category_idx');
        });
    }
};
