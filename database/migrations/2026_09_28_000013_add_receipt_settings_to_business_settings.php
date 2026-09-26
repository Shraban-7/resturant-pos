<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Receipt customization: configurable footer/thank-you line and
     * an option to print the drawn signature on the thermal receipt.
     */
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('business_settings', 'receipt_footer')) {
                $table->string('receipt_footer', 255)->nullable()->after('signature');
            }
            if (! Schema::hasColumn('business_settings', 'receipt_show_signature')) {
                $table->boolean('receipt_show_signature')->default(false)->after('receipt_footer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            foreach (['receipt_show_signature', 'receipt_footer'] as $column) {
                if (Schema::hasColumn('business_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};