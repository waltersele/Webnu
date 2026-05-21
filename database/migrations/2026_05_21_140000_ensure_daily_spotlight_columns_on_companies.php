<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureDailySpotlightColumnsOnCompanies extends Migration
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

        Schema::table('companies', function ($table) {
            $table->string('daily_spotlight', 500)->nullable();
            $table->string('daily_spotlight_price', 32)->nullable();
        });
    }

    public function down()
    {
    }
}
