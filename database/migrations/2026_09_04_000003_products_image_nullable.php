<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Raw SQL on MySQL (doctrine/dbal is not installed, so ->change() is unavailable there).
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products MODIFY image VARCHAR(255) NULL');
        } else {
            Schema::table('products', function (Blueprint $table) {
                $table->string('image')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        DB::table('products')->whereNull('image')->update(['image' => '']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products MODIFY image VARCHAR(255) NOT NULL');
        } else {
            Schema::table('products', function (Blueprint $table) {
                $table->string('image')->nullable(false)->change();
            });
        }
    }
};
