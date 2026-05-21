<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalesFieldsToCompaniesTable extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_rep_user_id')->nullable()->after('user_id');
            $table->timestamp('sales_converted_at')->nullable()->after('sales_rep_user_id');

            $table->foreign('sales_rep_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('sales_rep_user_id');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['sales_rep_user_id']);
            $table->dropColumn(['sales_rep_user_id', 'sales_converted_at']);
        });
    }
}
