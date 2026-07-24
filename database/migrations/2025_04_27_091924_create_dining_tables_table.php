<?php

use App\Models\DiningTable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dining_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('seller_id');
            // floor_id is a plain nullable key (floors table is created later); the
            // application layer nulls it on floor deletion.
            $table->unsignedBigInteger('floor_id')->nullable();
            $table->string('name');
            $table->string('status')->default(DiningTable::FREE);
            $table->string('qr_code_token', 64)->nullable()->unique();
            $table->integer('x_position')->default(0);
            $table->integer('y_position')->default(0);
            $table->timestamps();

            $table->index(['seller_id', 'status']);
            $table->index(['seller_id', 'floor_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dining_tables');
    }
};
