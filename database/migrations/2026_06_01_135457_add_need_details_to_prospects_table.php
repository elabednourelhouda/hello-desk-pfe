<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->unsignedInteger('people_count')->nullable()->after('preferred_space_type_id');
            $table->date('desired_start_date')->nullable()->after('budget');
            $table->string('desired_rental_period')->nullable()->after('desired_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn([
                'people_count',
                'desired_start_date',
                'desired_rental_period',
            ]);
        });
    }
};