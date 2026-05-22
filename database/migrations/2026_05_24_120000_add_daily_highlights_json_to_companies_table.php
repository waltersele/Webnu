<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDailyHighlightsJsonToCompaniesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        if (! Schema::hasColumn('companies', 'daily_highlights')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->json('daily_highlights')->nullable();
            });
        }

        if (Schema::hasColumn('companies', 'daily_spotlight')) {
            $rows = DB::table('companies')
                ->whereNotNull('daily_spotlight')
                ->where('daily_spotlight', '!=', '')
                ->whereNull('daily_highlights')
                ->get(['id', 'daily_spotlight', 'daily_spotlight_price']);

            foreach ($rows as $row) {
                DB::table('companies')->where('id', $row->id)->update([
                    'daily_highlights' => json_encode([[
                        'type' => 'spotlight',
                        'label' => 'Especial de hoy',
                        'text' => trim((string) $row->daily_spotlight),
                        'price' => trim((string) $row->daily_spotlight_price) ?: null,
                    ]], JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('companies') && Schema::hasColumn('companies', 'daily_highlights')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('daily_highlights');
            });
        }
    }
}
