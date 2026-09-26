<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Production already carries additions_json/addons_json on the order
     * line tables (applied out-of-band); the repo migrations never did,
     * so fresh installs silently drop that data. Ensure both columns
     * everywhere order lines are stored.
     */
    public function up(): void
    {
        foreach (['cart_items', 'sale_items', 'kitchen_ticket_items'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (! Schema::hasColumn($table, 'additions_json')) {
                    $t->json('additions_json')->nullable();
                }
                if (! Schema::hasColumn($table, 'addons_json')) {
                    $t->json('addons_json')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['cart_items', 'sale_items', 'kitchen_ticket_items'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'addons_json')) {
                    $t->dropColumn('addons_json');
                }
            });
        }
    }
};
