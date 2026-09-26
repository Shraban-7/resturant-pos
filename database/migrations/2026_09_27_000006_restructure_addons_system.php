<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Historical placeholder: the original restructure was committed
     * as an empty file (see "add addons funtion with bugs").
     * Real additions schema lives in 2026_09_27_000007_create_additions_system.
     * Kept as a no-op so fresh installs (sqlite tests) can migrate.
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
