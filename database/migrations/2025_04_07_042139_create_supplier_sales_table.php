<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('supplier_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('order_id');
            $table->date('sale_date');
            $table->decimal('subtotal');
            $table->decimal('discount')->default(0);
            $table->decimal('payable');
            $table->decimal('paid')->default(0);
            $table->decimal('due')->default(0);
            $table->text('note')->nullable();
            $table->string('payment_option')->nullable();
            $table->boolean('is_draft')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_sales');
    }
};
