<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('risk_status')->default('clear')->after('status');
            $table->text('risk_reason')->nullable()->after('risk_status');
            $table->timestamp('risk_checked_at')->nullable()->after('risk_reason');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'risk_status',
                'risk_reason',
                'risk_checked_at',
            ]);
        });
    }
};
