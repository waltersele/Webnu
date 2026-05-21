<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDailyHighlightsToCompaniesTable extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('dish_of_day_product_id')->nullable()->after('enabled_locales');
            $table->unsignedBigInteger('chef_suggestion_product_id')->nullable()->after('dish_of_day_product_id');

            $table->foreign('dish_of_day_product_id')
                ->references('id')
                ->on('products')
                ->onDelete('set null');
            $table->foreign('chef_suggestion_product_id')
                ->references('id')
                ->on('products')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['dish_of_day_product_id']);
            $table->dropForeign(['chef_suggestion_product_id']);
            $table->dropColumn(['dish_of_day_product_id', 'chef_suggestion_product_id']);
        });
    }
}
