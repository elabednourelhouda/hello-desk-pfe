<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('floors', 'level')) {
            Schema::table('floors', function (Blueprint $table) {
                $table->integer('level')->default(0)->after('code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('floors', 'level')) {
            Schema::table('floors', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        }
    }
};