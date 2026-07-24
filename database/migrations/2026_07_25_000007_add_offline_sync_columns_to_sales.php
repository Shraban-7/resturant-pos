<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'client_order_id')) {
                $table->uuid('client_order_id')->nullable()->after('order_id');
                $table->unique(['seller_id', 'client_order_id'], 'sales_seller_client_order_unique');
            }

            if (! Schema::hasColumn('sales', 'device_id')) {
                $table->uuid('device_id')->nullable()->after('client_order_id');
            }

            if (! Schema::hasColumn('sales', 'created_at_client')) {
                $table->timestamp('created_at_client')->nullable()->after('device_id');
            }

            if (! Schema::hasColumn('sales', 'synced_at')) {
                $table->timestamp('synced_at')->nullable()->after('created_at_client');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'client_order_id')) {
                $table->dropUnique('sales_seller_client_order_unique');
            }

            $columns = collect(['client_order_id', 'device_id', 'created_at_client', 'synced_at'])
                ->filter(fn (string $column) => Schema::hasColumn('sales', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
