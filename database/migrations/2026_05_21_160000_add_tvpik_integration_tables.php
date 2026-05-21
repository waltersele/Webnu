<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTvpikIntegrationTables extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('tvpik_api_token')->nullable()->after('api_token');
            $table->timestamp('tvpik_connected_at')->nullable()->after('tvpik_api_token');
            $table->string('tvpik_org_id', 64)->nullable()->after('tvpik_connected_at');
        });

        Schema::create('tvpik_screen_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');
            $table->string('tvpik_screen_id', 64);
            $table->string('tvpik_screen_name')->nullable();
            $table->string('tvpik_gallery_id', 64)->nullable();
            $table->string('template_key', 32)->default('menu');
            $table->string('published_url', 500)->nullable();
            $table->string('sync_version', 64)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unique(['user_id', 'tvpik_screen_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tvpik_screen_links');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tvpik_api_token', 'tvpik_connected_at', 'tvpik_org_id']);
        });
    }
}
