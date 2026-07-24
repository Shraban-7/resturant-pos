<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('code', 32)->unique();
            $table->decimal('initial_value', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'code', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
    }
};
