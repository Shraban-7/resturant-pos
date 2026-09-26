<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('store_settings');

        if (Schema::hasTable('dining_tables') && Schema::hasColumn('dining_tables', 'qr_code_token')) {
            Schema::table('dining_tables', function (Blueprint $table) {
                try {
                    $table->dropUnique(['qr_code_token']);
                } catch (\Throwable $e) {
                    // Index may already be gone on some drivers.
                }

                try {
                    $table->dropColumn('qr_code_token');
                } catch (\Throwable $e) {
                    // Column may already be gone.
                }
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('store_settings')) {
            Schema::create('store_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id')->index();
                $table->string('key', 100);
                $table->text('value')->nullable();
                $table->timestamps();

                $table->unique(['admin_id', 'key']);
            });
        }

        if (Schema::hasTable('dining_tables') && ! Schema::hasColumn('dining_tables', 'qr_code_token')) {
            Schema::table('dining_tables', function (Blueprint $table) {
                $table->string('qr_code_token', 64)->nullable()->unique();
            });
        }
    }
};
