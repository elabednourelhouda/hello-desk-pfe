<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedInteger('engagement_duration_value')
                ->nullable()
                ->after('duration_type');

            $table->string('engagement_duration_unit', 20)
                ->nullable()
                ->after('engagement_duration_value');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'engagement_duration_value',
                'engagement_duration_unit',
            ]);
        });
    }
};