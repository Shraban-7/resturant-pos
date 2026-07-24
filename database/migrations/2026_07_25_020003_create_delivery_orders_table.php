<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->text('delivery_address');
            $table->string('customer_phone');
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            $table->enum('status', ['pending', 'assigned', 'out_for_delivery', 'delivered', 'cancelled'])->default('pending');
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
