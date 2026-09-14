<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'order_type')) {
                $table->string('order_type', 20)->default('dine_in')->after('employee_id');
            }
            if (!Schema::hasColumn('sales', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('sales', 'customer_phone')) {
                $table->string('customer_phone', 50)->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('sales', 'address')) {
                $table->string('address')->nullable()->after('customer_phone');
            }
            if (!Schema::hasColumn('sales', 'gift_card_id')) {
                $table->foreignId('gift_card_id')->nullable()->after('branch_id')->constrained('gift_cards')->nullOnDelete();
            }
            if (!Schema::hasColumn('sales', 'amount_paid_by_gift_card')) {
                $table->decimal('amount_paid_by_gift_card', 10, 2)->default(0)->after('due');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'gift_card_id')) {
                $table->dropForeign(['gift_card_id']);
                $table->dropColumn('gift_card_id');
            }
            $table->dropColumn([
                'order_type', 'customer_name', 'customer_phone', 'address', 'amount_paid_by_gift_card',
            ]);
        });
    }
};
