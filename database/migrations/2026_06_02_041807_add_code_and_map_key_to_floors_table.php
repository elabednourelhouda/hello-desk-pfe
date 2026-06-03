<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('floors', function (Blueprint $table) {
            if (! Schema::hasColumn('floors', 'code')) {
                $table->string('code')->nullable();
            }

            if (! Schema::hasColumn('floors', 'map_key')) {
                $table->string('map_key')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('floors', function (Blueprint $table) {
            if (Schema::hasColumn('floors', 'map_key')) {
                $table->dropColumn('map_key');
            }

            if (Schema::hasColumn('floors', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
};