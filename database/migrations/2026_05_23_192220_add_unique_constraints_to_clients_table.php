<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Duplicate migration kept intentionally.
        // clients_user_id_unique and clients_prospect_id_unique are already
        // created by 2026_05_23_190118_create_clients_table.php.
    }

    public function down(): void
    {
        // Do nothing — the base migration owns these constraints.
    }
};