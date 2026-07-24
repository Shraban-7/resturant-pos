<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kitchen_tickets')) {
            Schema::create('kitchen_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
                $table->foreignId('dining_table_id')->nullable()->constrained('dining_tables')->onDelete('set null');
                $table->string('ticket_number');
                $table->string('status')->default('pending');
                $table->string('station')->nullable();
                $table->timestamp('fired_at')->nullable();
                $table->timestamp('prepared_at')->nullable();
                $table->timestamp('served_at')->nullable();
                $table->timestamps();

                $table->index(['seller_id', 'status']);
                $table->index(['sale_id', 'status']);
            });
        }

        if (! Schema::hasTable('kitchen_ticket_items')) {
            Schema::create('kitchen_ticket_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kitchen_ticket_id')->constrained('kitchen_tickets')->onDelete('cascade');
                $table->foreignId('sale_item_id')->nullable()->constrained('sale_items')->onDelete('set null');
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
                $table->string('product_name');
                $table->decimal('quantity', 10, 2)->default(1);
                $table->json('modifiers_json')->nullable();
                $table->string('special_instructions')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();

                $table->index('kitchen_ticket_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_ticket_items');
        Schema::dropIfExists('kitchen_tickets');
    }
};
