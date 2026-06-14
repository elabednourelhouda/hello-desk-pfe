<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            if (! Schema::hasColumn('prospects', 'lost_reason')) {
                $table->text('lost_reason')->nullable()->after('crm_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            if (Schema::hasColumn('prospects', 'lost_reason')) {
                $table->dropColumn('lost_reason');
            }
        });
    }
};