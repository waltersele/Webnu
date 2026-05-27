<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLogoChipMetadataToCompaniesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'logo_luminance')) {
                $table->decimal('logo_luminance', 4, 3)->nullable()->after('logo');
            }
            if (! Schema::hasColumn('companies', 'logo_has_solid_bg')) {
                $table->boolean('logo_has_solid_bg')->nullable()->after('logo_luminance');
            }
            if (! Schema::hasColumn('companies', 'logo_dominant_hex')) {
                $table->string('logo_dominant_hex', 7)->nullable()->after('logo_has_solid_bg');
            }
            if (! Schema::hasColumn('companies', 'logo_chip_variant')) {
                $table->string('logo_chip_variant', 8)->nullable()->after('logo_dominant_hex');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            foreach (['logo_chip_variant', 'logo_dominant_hex', 'logo_has_solid_bg', 'logo_luminance'] as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
