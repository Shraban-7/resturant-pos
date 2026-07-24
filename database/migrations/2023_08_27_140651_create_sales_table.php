<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->boolean('is_hold')->default(0);

            $table->foreignId('dining_table_id')->nullable(); #->constrained('dining_tables')->nullOnDelete();
            $table->foreignId('seller_employee_id')->nullable(); #->constrained('seller_employees')->nullOnDelete();

            $table->foreignIdFor(Customer::class)->nullable();
            $table->string('order_id');

            // Offline-first sync / idempotency columns.
            $table->uuid('client_order_id')->nullable();
            $table->uuid('device_id')->nullable();
            $table->timestamp('created_at_client')->nullable();
            $table->timestamp('synced_at')->nullable();

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

            $table->unique(['seller_id', 'client_order_id'], 'sales_seller_client_order_unique');
            $table->index(['seller_id', 'sale_date']);
            $table->index(['seller_id', 'is_hold']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
};
