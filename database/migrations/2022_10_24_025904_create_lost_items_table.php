<?php

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
        Schema::create('lost_items', function (Blueprint $table) {
            $table->id('lost_item_id');
            $table->string('lost_item_name')->nullable();
            $table->string('lost_item_image')->nullable();
            $table->string('lost_item_description')->nullable();
            $table->string('lost_item_location')->nullable();
            $table->string('lost_item_color')->nullable();
            $table->string('lost_item_status')->nullable();
            $table->integer('created_by')->default(-1);
            $table->integer('updated_by')->default(-1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lost_items');
    }
};
