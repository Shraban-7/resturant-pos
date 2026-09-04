<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'parent_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('parent_id')->nullable()->after('role')->constrained('users')->nullOnDelete();
                $table->string('phone')->nullable()->after('email');
                $table->json('permissions')->nullable()->after('password');
            });
        }

        // Legacy data: seller + supplier -> admin (full access, single panel).
        DB::table('users')->where('role', 'seller')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'supplier')->update(['role' => 'admin', 'parent_id' => null, 'permissions' => null]);

        // Widen enum where supported (MySQL). No-op on SQLite.
        try {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','employee') NOT NULL");
        } catch (\Throwable $e) {
            // SQLite / other drivers: skip.
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('users', 'permissions')) {
                $table->dropColumn('permissions');
            }
        });
    }
};
