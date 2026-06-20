<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropForeign(['converted_client_id']);
        });

        DB::statement("
            UPDATE prospects
            SET converted_client_id = NULL
            WHERE converted_client_id IS NOT NULL
            AND NOT EXISTS (
                SELECT 1 FROM clients WHERE clients.id = prospects.converted_client_id
            )
        ");

        Schema::table('prospects', function (Blueprint $table) {
            $table->foreign('converted_client_id')
                ->references('id')
                ->on('clients')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropForeign(['converted_client_id']);
        });

        DB::statement("
            UPDATE prospects
            SET converted_client_id = NULL
            WHERE converted_client_id IS NOT NULL
            AND NOT EXISTS (
                SELECT 1 FROM users WHERE users.id = prospects.converted_client_id
            )
        ");

        Schema::table('prospects', function (Blueprint $table) {
            $table->foreign('converted_client_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};