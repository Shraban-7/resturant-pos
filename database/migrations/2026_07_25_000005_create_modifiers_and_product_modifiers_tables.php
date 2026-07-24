<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('group_name');
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['seller_id', 'group_name']);
        });

        Schema::create('product_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('modifier_id')->constrained('modifiers')->onDelete('cascade');
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'modifier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_modifiers');
        Schema::dropIfExists('modifiers');
    }
};
