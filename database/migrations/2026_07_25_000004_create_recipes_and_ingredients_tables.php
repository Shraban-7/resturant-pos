<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('recipes')) {
            Schema::create('recipes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('product_id')->unique()->constrained('products')->onDelete('cascade');
                $table->text('instructions')->nullable();
                $table->integer('preparation_time_minutes')->default(15);
                $table->boolean('is_active')->default(true);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('recipe_ingredients')) {
            Schema::create('recipe_ingredients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('recipe_id')->constrained('recipes')->onDelete('cascade');
                $table->foreignId('ingredient_product_id')->constrained('products')->onDelete('cascade');
                $table->decimal('quantity', 10, 3);
                $table->foreignId('unit_id')->nullable()->constrained('product_units')->onDelete('set null');
                $table->timestamps();

                $table->index(['recipe_id', 'ingredient_product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('recipes');
    }
};
