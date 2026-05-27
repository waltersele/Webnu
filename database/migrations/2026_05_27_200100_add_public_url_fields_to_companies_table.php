<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublicUrlFieldsToCompaniesTable extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'public_url_format')) {
                $table->string('public_url_format', 16)->nullable()->after('slug');
            }
            if (! Schema::hasColumn('companies', 'public_slug_locked_at')) {
                $table->timestamp('public_slug_locked_at')->nullable()->after('public_url_format');
            }
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'public_slug_locked_at')) {
                $table->dropColumn('public_slug_locked_at');
            }
            if (Schema::hasColumn('companies', 'public_url_format')) {
                $table->dropColumn('public_url_format');
            }
        });
    }
}
