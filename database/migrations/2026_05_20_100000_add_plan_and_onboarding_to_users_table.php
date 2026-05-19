<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPlanAndOnboardingToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('plan', 32)->default('free')->after('password');
            $table->unsignedTinyInteger('onboarding_step')->default(0)->after('plan');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_step');
        });

        DB::table('users')->whereNull('onboarding_completed_at')->update([
            'onboarding_completed_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['plan', 'onboarding_step', 'onboarding_completed_at']);
        });
    }
}
