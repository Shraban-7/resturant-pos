<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('unit_id')->constrained('suppliers')->nullOnDelete();
            }

            if (! Schema::hasColumn('products', 'reorder_level')) {
                $table->decimal('reorder_level', 12, 3)->default(0)->after('stock_out');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'supplier_id')) {
                try {
                    $table->dropConstrainedForeignId('supplier_id');
                } catch (\Throwable $e) {
                    try {
                        $table->dropColumn('supplier_id');
                    } catch (\Throwable $e) {
                        // Already gone.
                    }
                }
            }

            if (Schema::hasColumn('products', 'reorder_level')) {
                try {
                    $table->dropColumn('reorder_level');
                } catch (\Throwable $e) {
                    // Already gone.
                }
            }
        });
    }
};
