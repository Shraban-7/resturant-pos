<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->json('modifiers_json')->nullable()->after('note');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->json('modifiers_json')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('modifiers_json');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('modifiers_json');
        });
    }
};
