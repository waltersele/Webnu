<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMenuTranslations extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('default_locale', 8)->default('es');
            $table->json('enabled_locales')->nullable();
        });

        Schema::create('section_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->string('locale', 8);
            $table->string('name');
            $table->string('source', 16)->default('manual');
            $table->timestamps();

            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->unique(['section_id', 'locale']);
        });

        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('locale', 8);
            $table->string('name');
            $table->string('description', 1000)->nullable();
            $table->string('source', 16)->default('manual');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['product_id', 'locale']);
        });

        Schema::create('translation_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->string('target_locale', 8);
            $table->string('status', 32)->default('processing');
            $table->string('provider', 32)->nullable();
            $table->unsignedInteger('items_total')->default(0);
            $table->unsignedInteger('items_done')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('translation_jobs');
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('section_translations');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['default_locale', 'enabled_locales']);
        });
    }
}
