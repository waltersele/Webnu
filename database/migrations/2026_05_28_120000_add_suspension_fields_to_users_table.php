<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable()->after('trial_ends_at');
            $table->string('suspended_reason', 64)->nullable()->after('suspended_at');
            $table->json('suspension_snapshot')->nullable()->after('suspended_reason');
            $table->unsignedBigInteger('suspended_by')->nullable()->after('suspension_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'suspended_at',
                'suspended_reason',
                'suspension_snapshot',
                'suspended_by',
            ]);
        });
    }
};
