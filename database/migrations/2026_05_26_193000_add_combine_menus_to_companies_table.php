<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCombineMenusToCompaniesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'combine_menus')) {
                $table->boolean('combine_menus')->default(false)->after('menu_type_2_pdf');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'combine_menus')) {
                $table->dropColumn('combine_menus');
            }
        });
    }
}
