<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('connector_group_id')->nullable()->index();
            $table->unsignedBigInteger('author_user_id')->nullable();
            $table->timestamps();

            $table->foreign('author_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('blog_post_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('slug', 120);
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('body_format', 20)->default('html');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->unique(['blog_post_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_translations');
        Schema::dropIfExists('blog_posts');
    }
};
