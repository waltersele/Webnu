<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuScanJobsTable extends Migration
{
    public function up()
    {
        Schema::create('menu_scan_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status', 20)->default('pending');
            $table->string('provider', 32)->nullable();
            $table->boolean('fallback_used')->default(false);
            $table->json('source_files')->nullable();
            $table->json('parsed_menu')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['company_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_scan_jobs');
    }
}
