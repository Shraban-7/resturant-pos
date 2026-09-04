<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
