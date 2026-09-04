<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id')->index();
                $table->string('name');
                $table->string('phone', 50)->nullable();
                $table->string('address')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['admin_id', 'is_active']);
            });
        }

        // Goods receipts: raw ingredients bought from a supplier.
        // Each purchase increments the ingredient's stock.
        if (! Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id')->index();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->decimal('quantity', 12, 3);
                $table->integer('buying_price');
                $table->integer('total_price');
                $table->date('purchase_date');
                $table->string('note', 500)->nullable();
                $table->timestamps();

                $table->index(['admin_id', 'purchase_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
    }
};
