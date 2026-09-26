<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Discount type: percentage (% off subtotal) or flat (fixed ৳ off).
     */
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('business_settings', 'global_discount_type')) {
                $table->string('global_discount_type', 20)->default('percentage')->after('global_discount_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            if (Schema::hasColumn('business_settings', 'global_discount_type')) {
                $table->dropColumn('global_discount_type');
            }
        });
    }
};
