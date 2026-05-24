<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuPreRegistrationsTable extends Migration
{
    public function up()
    {
        Schema::create('menu_pre_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('restaurant_name');
            $table->json('menu_json');
            $table->string('public_slug', 32)->unique();
            $table->string('claim_token_hash', 64)->unique();
            $table->string('status', 20)->default('pending');
            $table->json('media_manifest')->nullable();
            $table->json('source_meta')->nullable();
            $table->unsignedBigInteger('claimed_user_id')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->foreign('claimed_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_pre_registrations');
    }
}
