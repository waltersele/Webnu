<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMenuSlugFields extends Migration
{
    public function up()
    {
        Schema::table('menus', function (Blueprint $table) {
            if (! Schema::hasColumn('menus', 'public_slug_locked_at')) {
                $table->timestamp('public_slug_locked_at')->nullable()->after('slug');
            }
        });

        try {
            Schema::table('menus', function (Blueprint $table) {
                $table->unique(['company_id', 'slug'], 'menus_company_slug_unique');
            });
        } catch (\Throwable $e) {
            // Puede fallar si hay duplicados en datos legacy.
        }
    }

    public function down()
    {
        try {
            Schema::table('menus', function (Blueprint $table) {
                $table->dropUnique('menus_company_slug_unique');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::table('menus', function (Blueprint $table) {
            if (Schema::hasColumn('menus', 'public_slug_locked_at')) {
                $table->dropColumn('public_slug_locked_at');
            }
        });
    }
}
