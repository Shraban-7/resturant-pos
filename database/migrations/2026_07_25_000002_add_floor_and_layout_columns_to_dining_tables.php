<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            if (!Schema::hasColumn('dining_tables', 'floor_id')) {
                $table->foreignId('floor_id')->nullable()->after('seller_id')->constrained('floors')->onDelete('set null');
            }
            if (!Schema::hasColumn('dining_tables', 'qr_code_token')) {
                $table->string('qr_code_token', 64)->nullable()->unique();
            }
            if (!Schema::hasColumn('dining_tables', 'x_position')) {
                $table->integer('x_position')->default(0);
            }
            if (!Schema::hasColumn('dining_tables', 'y_position')) {
                $table->integer('y_position')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            if (Schema::hasColumn('dining_tables', 'floor_id')) {
                $table->dropForeign(['floor_id']);
            }
            $cols = array_filter(['floor_id', 'qr_code_token', 'x_position', 'y_position'], fn ($c) => Schema::hasColumn('dining_tables', $c));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
