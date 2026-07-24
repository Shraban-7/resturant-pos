<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['seller_id', 'is_hold', 'created_at'], 'idx_sales_seller_hold_created');
            $table->index(['dining_table_id', 'status'], 'idx_sales_table_status');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->index(['sale_id', 'product_id'], 'idx_sale_items_sale_product');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['seller_id', 'is_active', 'category_id'], 'idx_products_seller_active_cat');
        });

        Schema::table('dining_tables', function (Blueprint $table) {
            $table->index(['seller_id', 'status'], 'idx_dining_tables_seller_status');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_seller_hold_created');
            $table->dropIndex('idx_sales_table_status');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('idx_sale_items_sale_product');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_seller_active_cat');
        });

        Schema::table('dining_tables', function (Blueprint $table) {
            $table->dropIndex('idx_dining_tables_seller_status');
        });
    }
};
