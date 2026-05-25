<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManualPlanUntilToUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'manual_plan_key')) {
                $table->string('manual_plan_key', 32)->nullable()->after('plan');
            }
            if (! Schema::hasColumn('users', 'manual_plan_until')) {
                $table->dateTime('manual_plan_until')->nullable()->after('manual_plan_key');
            }
            if (! Schema::hasColumn('users', 'manual_plan_note')) {
                $table->text('manual_plan_note')->nullable()->after('manual_plan_until');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['manual_plan_note', 'manual_plan_until', 'manual_plan_key'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
