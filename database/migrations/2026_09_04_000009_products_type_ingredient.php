<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL enum needs the new value declared; other drivers store plain strings.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE products MODIFY type ENUM('dish','buffet','ingredient') NOT NULL DEFAULT 'dish'");
        }
    }

    public function down(): void
    {
        DB::table('products')->where('type', 'ingredient')->update(['type' => 'dish']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE products MODIFY type ENUM('dish','buffet') NOT NULL DEFAULT 'dish'");
        }
    }
};
