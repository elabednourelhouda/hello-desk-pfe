<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Duplicate migration kept intentionally.
        // The must_change_password column is already handled by another migration.
    }

    public function down(): void
    {
        // Do nothing to avoid dropping the same column twice.
    }
};