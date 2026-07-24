<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Superseded by 2026_07_24_232500_add_composite_indexes_to_pos_core_tables
 * (correct column names). Kept as no-op so migrate history can complete.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Indexes already applied by earlier TASK-105 migration on live schema.
    }

    public function down(): void
    {
        //
    }
};
