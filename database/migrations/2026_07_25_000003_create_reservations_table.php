<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('table_id')->constrained('dining_tables')->onDelete('cascade');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->integer('guest_count')->default(1);
            $table->dateTime('reservation_time');
            $table->enum('status', ['pending', 'confirmed', 'seated', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'reservation_time', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
