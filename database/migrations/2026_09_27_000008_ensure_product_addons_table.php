<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original 000002 migration is marked as ran but the table
     * is missing on some installs (manual drop / partial fresh).
     * Re-create idempotently; safe on fresh DBs and CI sqlite.
     */
    public function up(): void
    {
        if (! Schema::hasTable('product_addons')) {
            Schema::create('product_addons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('addon_product_id')->constrained('products')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['product_id', 'addon_product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_addons');
    }
};
