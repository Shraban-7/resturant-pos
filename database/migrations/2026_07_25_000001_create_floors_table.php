<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('floors')) {
            return;
        }

        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->index(['seller_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};
