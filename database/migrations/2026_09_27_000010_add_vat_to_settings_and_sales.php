<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Configurable VAT: mode/rate live on business_settings (per admin),
     * the frozen calculation lives on each sale for invoices/reports.
     */
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('business_settings', 'vat_enabled')) {
                $table->boolean('vat_enabled')->default(false)->after('vat_number');
            }
            if (! Schema::hasColumn('business_settings', 'vat_rate')) {
                $table->decimal('vat_rate', 5, 2)->default(0)->after('vat_enabled');
            }
            if (! Schema::hasColumn('business_settings', 'vat_mode')) {
                $table->string('vat_mode', 20)->default('exclusive')->after('vat_rate');
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'vat_mode')) {
                $table->string('vat_mode', 20)->default('disabled');
            }
            if (! Schema::hasColumn('sales', 'vat_rate')) {
                $table->decimal('vat_rate', 5, 2)->default(0);
            }
            if (! Schema::hasColumn('sales', 'vat_amount')) {
                $table->decimal('vat_amount', 12, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            foreach (['vat_amount', 'vat_rate', 'vat_mode'] as $column) {
                if (Schema::hasColumn('sales', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('business_settings', function (Blueprint $table) {
            foreach (['vat_mode', 'vat_rate', 'vat_enabled'] as $column) {
                if (Schema::hasColumn('business_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
