<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicSlugRedirectsTable extends Migration
{
    public function up()
    {
        Schema::create('public_slug_redirects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('from_path', 255)->unique();
            $table->string('to_path', 255);
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->unsignedSmallInteger('http_status')->default(301);
            $table->timestamps();

            $table->index('to_path');
        });
    }

    public function down()
    {
        Schema::dropIfExists('public_slug_redirects');
    }
}
