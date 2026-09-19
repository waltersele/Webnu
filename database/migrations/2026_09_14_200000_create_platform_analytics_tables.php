<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlatformAnalyticsTables extends Migration
{
    public function up()
    {
        Schema::create('platform_analytics_dailies', function (Blueprint $table) {
            $table->id();
            $table->date('day');
            $table->string('metric', 64);
            $table->string('dimension', 191)->default('');
            $table->unsignedBigInteger('count')->default(0);
            $table->timestamps();

            $table->unique(['day', 'metric', 'dimension'], 'platform_analytics_dailies_unique');
            $table->index(['metric', 'day']);
        });

        Schema::create('platform_analytics_visitors', function (Blueprint $table) {
            $table->id();
            $table->date('day');
            $table->string('visitor_hash', 64);
            $table->timestamps();

            $table->unique(['day', 'visitor_hash'], 'platform_analytics_visitors_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('platform_analytics_visitors');
        Schema::dropIfExists('platform_analytics_dailies');
    }
}
