<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMenuIdToTvpikScreenLinksTable extends Migration
{
    public function up()
    {
        Schema::table('tvpik_screen_links', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id')->nullable()->after('template_key');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('set null');
            $table->index('menu_id');
        });
    }

    public function down()
    {
        Schema::table('tvpik_screen_links', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
            $table->dropIndex(['menu_id']);
            $table->dropColumn('menu_id');
        });
    }
}
