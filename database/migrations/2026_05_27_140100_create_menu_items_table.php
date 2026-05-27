<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuItemsTable extends Migration
{
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->string('course', 16);
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('label', 200)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->index(['menu_id', 'course', 'position']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_items');
    }
}
