<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesHandoffsTable extends Migration
{
    public function up()
    {
        Schema::create('sales_handoffs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sales_rep_user_id');
            $table->unsignedBigInteger('company_id');
            $table->string('prospect_email');
            $table->string('prospect_name')->nullable();
            $table->string('plan_key', 32);
            $table->unsignedSmallInteger('trial_days')->default(30);
            $table->unsignedBigInteger('restaurant_user_id')->nullable();
            $table->string('status', 20)->default('sent');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->foreign('sales_rep_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('restaurant_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('sales_rep_user_id');
            $table->index('sent_at');
            $table->index('prospect_email');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_handoffs');
    }
}
