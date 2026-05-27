<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SwapCourseForMenuSectionIdOnMenuItems extends Migration
{
    public function up()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_section_id')->nullable()->after('menu_id');
            $table->foreign('menu_section_id')->references('id')->on('menu_sections')->onDelete('cascade');
            $table->index(['menu_section_id', 'position']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex(['menu_id', 'course', 'position']);
            $table->dropColumn('course');
        });
    }

    public function down()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('course', 16)->nullable()->after('menu_id');
            $table->index(['menu_id', 'course', 'position']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['menu_section_id']);
            $table->dropIndex(['menu_section_id', 'position']);
            $table->dropColumn('menu_section_id');
        });
    }
}
