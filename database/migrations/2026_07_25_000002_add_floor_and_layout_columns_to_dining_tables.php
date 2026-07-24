<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->foreignId('floor_id')->nullable()->after('seller_id')->constrained('floors')->onDelete('set null');
            $table->string('qr_code_token', 64)->nullable()->unique()->after('capacity');
            $table->integer('x_position')->default(0)->after('qr_code_token');
            $table->integer('y_position')->default(0)->after('x_position');
        });
    }

    public function down(): void
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->dropForeign(['floor_id']);
            $table->dropColumn(['floor_id', 'qr_code_token', 'x_position', 'y_position']);
        });
    }
};
