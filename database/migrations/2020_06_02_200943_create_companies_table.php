<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('chef_name')->nullable();
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('background_header')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code', 5)->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('web')->nullable();
            $table->boolean('reservation');
            $table->string('whatsapp')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->text('comments')->nullable();
            $table->text('schedule')->nullable();
            $table->string('template')->nullable();
            $table->integer('menu_type');
            $table->string('menu_type_2_pdf')->nullable();
            $table->boolean('enabled');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('companies');
    }
}
