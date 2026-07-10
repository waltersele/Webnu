<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('blog_category_id')
                ->nullable()
                ->after('connector_group_id')
                ->constrained('blog_categories')
                ->nullOnDelete();
        });

        Schema::table('blog_post_translations', function (Blueprint $table) {
            $table->json('faq_schema')->nullable()->after('meta_description');
        });

        $now = now();
        foreach (config('blog.default_categories', []) as $name) {
            DB::table('blog_categories')->insert([
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('blog_post_translations', function (Blueprint $table) {
            $table->dropColumn('faq_schema');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('blog_category_id');
        });

        Schema::dropIfExists('blog_categories');
    }
};
