<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('prospects', 'registered_at')) {
            Schema::table('prospects', function (Blueprint $table) {
                $table->date('registered_at')->nullable()->after('company_name');
            });
        }

        if (!Schema::hasColumn('clients', 'joined_at')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->date('joined_at')->nullable()->after('company_name');
            });
        }

        DB::table('prospects')
            ->whereNull('registered_at')
            ->update(['registered_at' => now()->toDateString()]);

        DB::table('clients')
            ->whereNull('joined_at')
            ->update(['joined_at' => now()->toDateString()]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('prospects', 'registered_at')) {
            Schema::table('prospects', function (Blueprint $table) {
                $table->dropColumn('registered_at');
            });
        }

        if (Schema::hasColumn('clients', 'joined_at')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('joined_at');
            });
        }
    }
};