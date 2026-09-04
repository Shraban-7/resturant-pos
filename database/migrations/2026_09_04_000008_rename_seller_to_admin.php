<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Single-seller site, admin-owned: rename every seller_id column to admin_id,
 * seller_employees table to employees, and sales.seller_employee_id to employee_id.
 */
return new class extends Migration
{
    private array $columns = [
        'products',
        'customers',
        'sales',
        'sale_items',
        'carts',
        'product_stocks',
        'product_categories',
        'dining_tables',
        'seller_employees',
        'floors',
        'reservations',
        'recipes',
        'modifiers',
        'kitchen_tickets',
        'loyalty_points',
        'gift_cards',
        'branches',
        'staff_notifications',
    ];

    private array $foreignKeys = [
        'branches',
        'floors',
        'reservations',
        'recipes',
        'modifiers',
        'kitchen_tickets',
        'loyalty_points',
        'gift_cards',
    ];

    public function up(): void
    {
        $isMysql = DB::getDriverName() === 'mysql';

        foreach ($this->columns as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'seller_id')) {
                continue;
            }

            if ($isMysql && in_array($table, $this->foreignKeys, true)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->dropForeign(["seller_id"]);
                });
            }

            Schema::table($table, function (Blueprint $t) {
                $t->renameColumn('seller_id', 'admin_id');
            });

            if ($isMysql && in_array($table, $this->foreignKeys, true)) {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreign('admin_id')->references('id')->on('users')->cascadeOnDelete();
                });
            }
        }

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'seller_employee_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->renameColumn('seller_employee_id', 'employee_id');
            });
        }

        if (Schema::hasTable('seller_employees') && ! Schema::hasTable('employees')) {
            Schema::rename('seller_employees', 'employees');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employees') && ! Schema::hasTable('seller_employees')) {
            Schema::rename('employees', 'seller_employees');
        }

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'employee_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->renameColumn('employee_id', 'seller_employee_id');
            });
        }

        $isMysql = DB::getDriverName() === 'mysql';

        foreach (array_reverse($this->columns) as $table) {
            $name = $table === 'seller_employees' ? 'seller_employees' : $table;
            if (! Schema::hasTable($name) || ! Schema::hasColumn($name, 'admin_id')) {
                continue;
            }

            if ($isMysql && in_array($table, $this->foreignKeys, true)) {
                Schema::table($name, function (Blueprint $t) {
                    $t->dropForeign(["admin_id"]);
                });
            }

            Schema::table($name, function (Blueprint $t) {
                $t->renameColumn('admin_id', 'seller_id');
            });

            if ($isMysql && in_array($table, $this->foreignKeys, true)) {
                Schema::table($name, function (Blueprint $t) {
                    $t->foreign('seller_id')->references('id')->on('users')->cascadeOnDelete();
                });
            }
        }
    }
};
