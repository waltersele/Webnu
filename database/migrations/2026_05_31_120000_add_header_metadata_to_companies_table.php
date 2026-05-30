<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHeaderMetadataToCompaniesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'header_luminance')) {
                $table->decimal('header_luminance', 4, 3)->nullable()->after('background_header');
            }
            if (! Schema::hasColumn('companies', 'header_overlay_mode')) {
                $table->string('header_overlay_mode', 8)->nullable()->after('header_luminance');
            }
            if (! Schema::hasColumn('companies', 'header_overlay_strength')) {
                $table->decimal('header_overlay_strength', 4, 3)->nullable()->after('header_overlay_mode');
            }
            if (! Schema::hasColumn('companies', 'header_dominant_hex')) {
                $table->string('header_dominant_hex', 7)->nullable()->after('header_overlay_strength');
            }
            if (! Schema::hasColumn('companies', 'header_crop')) {
                $table->json('header_crop')->nullable()->after('header_dominant_hex');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            foreach (['header_crop', 'header_dominant_hex', 'header_overlay_strength', 'header_overlay_mode', 'header_luminance'] as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
