<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReplaceDailyHighlightsWithSpotlightOnCompanies extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('companies', 'daily_spotlight')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE companies ADD COLUMN daily_spotlight VARCHAR(500) NULL');
            DB::statement('ALTER TABLE companies ADD COLUMN daily_spotlight_price VARCHAR(32) NULL');

            return;
        }

        if (Schema::hasColumn('companies', 'dish_of_day_product_id')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropForeign(['dish_of_day_product_id']);
                $table->dropForeign(['chef_suggestion_product_id']);
                $table->dropColumn(['dish_of_day_product_id', 'chef_suggestion_product_id']);
            });
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->string('daily_spotlight', 500)->nullable();
            $table->string('daily_spotlight_price', 32)->nullable();
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('companies', 'daily_spotlight')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['daily_spotlight', 'daily_spotlight_price']);
        });
    }
}
