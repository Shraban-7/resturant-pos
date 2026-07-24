<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
                $table->string('name');
                $table->string('code', 32)->nullable();
                $table->string('address')->nullable();
                $table->string('phone', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index(['seller_id', 'is_active']);
                $table->unique(['seller_id', 'name']);
            });
        }

        foreach (['floors', 'seller_employees', 'sales', 'reservations'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'seller_id')) {
                        $table->unsignedBigInteger('branch_id')->nullable()->after('seller_id');
                    } else {
                        $table->unsignedBigInteger('branch_id')->nullable();
                    }

                    $table->index(['seller_id', 'branch_id']);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['floors', 'seller_employees', 'sales', 'reservations'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropIndex(['seller_id', 'branch_id']);
                    $table->dropColumn('branch_id');
                });
            }
        }

        Schema::dropIfExists('branches');
    }
};
