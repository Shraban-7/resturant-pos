<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kitchen_tickets')) {
            Schema::create('kitchen_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
                $table->foreignId('table_id')->nullable()->constrained('dining_tables')->onDelete('set null');
                $table->string('ticket_number');
                $table->enum('status', ['pending', 'preparing', 'ready', 'served'])->default('pending');
                $table->timestamp('prepared_at')->nullable();
                $table->timestamps();

                $table->index(['sale_id', 'status']);
            });
        }

        if (!Schema::hasTable('kitchen_ticket_items')) {
            Schema::create('kitchen_ticket_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('kitchen_tickets')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->integer('quantity');
                $table->json('modifiers_json')->nullable();
                $table->string('special_instructions')->nullable();
                $table->enum('status', ['pending', 'preparing', 'ready'])->default('pending');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_ticket_items');
        Schema::dropIfExists('kitchen_tickets');
    }
};
