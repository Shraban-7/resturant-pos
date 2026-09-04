<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'branch_id')) {
            Schema::table('products', function (Blueprint $table) {
                // NULL = chain-wide (available at every branch).
                $table->foreignId('branch_id')->nullable()->after('seller_id')->constrained('branches')->nullOnDelete();
                $table->index(['seller_id', 'branch_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
