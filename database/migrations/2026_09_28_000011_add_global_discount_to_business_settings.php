<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Global discount: when enabled, a percentage discount auto-applies
     * to all POS products/orders (combined with manual discount).
     */
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('business_settings', 'global_discount_enabled')) {
                $table->boolean('global_discount_enabled')->default(false)->after('vat_mode');
            }
            if (! Schema::hasColumn('business_settings', 'global_discount_rate')) {
                $table->decimal('global_discount_rate', 5, 2)->default(0)->after('global_discount_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            foreach (['global_discount_rate', 'global_discount_enabled'] as $column) {
                if (Schema::hasColumn('business_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
