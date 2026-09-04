<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')->where('role', 'employee')->get(['id', 'name', 'permissions']);

        foreach ($users as $user) {
            $permissions = $user->permissions ? json_decode($user->permissions, true) : [];

            if (! is_array($permissions)) {
                $permissions = [];
            }

            // Dashboard is for admin + manager + cashier roles only.
            if (in_array($user->name, ['Manager', 'Cashier'], true) && ! in_array('dashboard', $permissions, true)) {
                $permissions[] = 'dashboard';
                DB::table('users')->where('id', $user->id)->update([
                    'permissions' => json_encode(array_values($permissions)),
                ]);
            }
        }
    }

    public function down(): void
    {
        // No rollback of permission grants.
    }
};
