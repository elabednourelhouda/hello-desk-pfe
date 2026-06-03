<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unique('user_id', 'clients_user_id_unique');
            $table->unique('prospect_id', 'clients_prospect_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_user_id_unique');
            $table->dropUnique('clients_prospect_id_unique');
        });
    }
};