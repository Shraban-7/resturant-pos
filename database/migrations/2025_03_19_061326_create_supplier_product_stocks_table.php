<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('supplier_product_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('supplier_id');
            $table->enum('type', ['increment', 'decrement']);
            $table->integer('quantity');
            $table->integer('old_stock')->default(0);
            $table->integer('new_stock');
            $table->integer('buying_price');
            $table->integer('selling_price');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_product_stocks');
    }
};


