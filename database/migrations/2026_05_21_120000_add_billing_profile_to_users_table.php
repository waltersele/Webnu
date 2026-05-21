<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillingProfileToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 40)->nullable()->after('email');
            $table->string('legal_name', 255)->nullable()->after('phone');
            $table->string('tax_id', 32)->nullable()->after('legal_name');
            $table->string('billing_address', 255)->nullable()->after('tax_id');
            $table->string('billing_postal_code', 16)->nullable()->after('billing_address');
            $table->string('billing_city', 120)->nullable()->after('billing_postal_code');
            $table->string('billing_country', 2)->nullable()->default('ES')->after('billing_city');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'legal_name',
                'tax_id',
                'billing_address',
                'billing_postal_code',
                'billing_city',
                'billing_country',
            ]);
        });
    }
}
