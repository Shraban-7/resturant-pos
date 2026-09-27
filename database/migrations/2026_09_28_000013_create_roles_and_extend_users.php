<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('slug', 100);
                $table->json('permissions')->nullable();
                $table->boolean('is_system')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['admin_id', 'slug']);
                $table->index(['admin_id', 'is_active']);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->after('parent_id')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('active')->after('role_id');
            }
            if (! Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title', 100)->nullable()->after('status');
            }
            if (! Schema::hasColumn('users', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('job_title');
            }
        });

        // Backfill: existing employees are active.
        try {
            DB::table('users')->whereNull('status')->update(['status' => 'active']);
        } catch (\Throwable $e) {
            // Fresh install — nothing to backfill.
        }

        $this->seedDefaultRoles();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) {
                try {
                    $table->dropConstrainedForeignId('role_id');
                } catch (\Throwable $e) {
                    $table->dropColumn('role_id');
                }
            }
            foreach (['status', 'job_title', 'branch_id'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('roles');
    }

    private function seedDefaultRoles(): void
    {
        try {
            $admins = DB::table('users')->where('role', 'admin')->get(['id']);
        } catch (\Throwable $e) {
            return;
        }

        $templates = [
            'Manager' => ['dashboard', 'pos', 'products', 'stocks', 'sales', 'kds', 'floors', 'branches', 'reservations', 'gift-cards', 'customers', 'employees', 'reports', 'settings'],
            'Cashier' => ['dashboard', 'pos', 'sales', 'customers', 'gift-cards', 'reservations'],
            'Waiter' => ['pos', 'kds', 'floors', 'reservations', 'sales'],
            'Chef' => ['kds', 'dashboard'],
        ];

        foreach ($admins as $admin) {
            foreach ($templates as $name => $permissions) {
                $slug = Str::slug($name);
                $exists = DB::table('roles')
                    ->where('admin_id', $admin->id)
                    ->where('slug', $slug)
                    ->exists();

                if (! $exists) {
                    DB::table('roles')->insert([
                        'admin_id' => $admin->id,
                        'name' => $name,
                        'slug' => $slug,
                        'permissions' => json_encode($permissions),
                        'is_system' => false,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
};
