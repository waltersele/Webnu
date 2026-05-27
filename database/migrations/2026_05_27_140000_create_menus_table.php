<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenusTable extends Migration
{
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name', 120);
            $table->string('slug', 140)->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->string('subtitle', 140)->nullable();
            $table->string('includes', 200)->nullable();
            $table->string('image', 500)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('enabled')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->index(['company_id', 'enabled']);
            $table->index(['company_id', 'position']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('menus');
    }
}
