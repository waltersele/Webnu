<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileWizardDismissedToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'profile_wizard_dismissed_at')) {
                $table->timestamp('profile_wizard_dismissed_at')->nullable()->after('onboarding_completed_at');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'profile_wizard_dismissed_at')) {
                $table->dropColumn('profile_wizard_dismissed_at');
            }
        });
    }
}
