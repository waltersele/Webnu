<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMenuFavoritesEnabledToCompaniesTable extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'menu_favorites_enabled')) {
                $table->boolean('menu_favorites_enabled')->default(true)->after('combine_menus');
            }
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'menu_favorites_enabled')) {
                $table->dropColumn('menu_favorites_enabled');
            }
        });
    }
}
