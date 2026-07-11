<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        $used = [];
        foreach (DB::table('blog_categories')->orderBy('id')->get() as $row) {
            $base = Str::slug((string) $row->name);
            if ($base === '') {
                $base = 'categoria';
            }
            $slug = $base;
            $suffix = 2;
            while (in_array($slug, $used, true) || DB::table('blog_categories')->where('slug', $slug)->where('id', '!=', $row->id)->exists()) {
                $slug = $base . '-' . $suffix;
                $suffix++;
            }
            $used[] = $slug;
            DB::table('blog_categories')->where('id', $row->id)->update(['slug' => $slug]);
        }

        Schema::table('blog_categories', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('blog_categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
