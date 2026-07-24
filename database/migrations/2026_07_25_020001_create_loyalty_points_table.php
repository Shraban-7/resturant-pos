<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('set null');
            $table->enum('type', ['earned', 'redeemed', 'adjusted'])->default('earned');
            $table->integer('points');
            $table->decimal('equivalent_amount', 10, 2)->default(0.00);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'customer_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->integer('loyalty_points_balance')->default(0)->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('loyalty_points_balance');
        });
        Schema::dropIfExists('loyalty_points');
    }
};
