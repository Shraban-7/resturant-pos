<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('additions')) {
            Schema::create('additions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
                $table->string('name');
                $table->decimal('price', 10, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['admin_id', 'is_active']);
                $table->index('admin_id');
            });
        }

        if (! Schema::hasTable('addition_product')) {
            Schema::create('addition_product', function (Blueprint $table) {
                $table->id();
                $table->foreignId('addition_id')->constrained('additions')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['addition_id', 'product_id']);
            });
        }

        // Migrate data from the pre-restructure tables (if they exist).
        if (Schema::hasTable('addons') && Schema::hasTable('additions')) {
            try {
                $rows = DB::table('addons')->get();
                foreach ($rows as $row) {
                    DB::table('additions')->updateOrInsert(
                        ['id' => $row->id],
                        [
                            'admin_id' => $row->admin_id,
                            'name' => $row->name,
                            'price' => $row->price,
                            'is_active' => $row->is_active,
                            'sort_order' => $row->sort_order ?? 0,
                            'created_at' => $row->created_at,
                            'updated_at' => $row->updated_at,
                        ]
                    );
                }
            } catch (Throwable $e) {
                // Best-effort backfill; new tables are already usable.
            }
        }

        if (Schema::hasTable('addon_product') && Schema::hasTable('addition_product')) {
            try {
                $links = DB::table('addon_product')->get();
                foreach ($links as $link) {
                    if (! DB::table('additions')->whereKey($link->addon_id)->exists()) {
                        continue;
                    }
                    if (! DB::table('products')->whereKey($link->product_id)->exists()) {
                        continue;
                    }
                    DB::table('addition_product')->updateOrInsert(
                        ['addition_id' => $link->addon_id, 'product_id' => $link->product_id],
                        ['created_at' => $link->created_at, 'updated_at' => $link->updated_at]
                    );
                }
            } catch (Throwable $e) {
                // Best-effort backfill.
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('addition_product');
        Schema::dropIfExists('additions');
    }
};
