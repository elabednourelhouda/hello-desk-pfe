<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds an is_active flag to users so an admin can deactivate a
     * commercial (or, in principle, any account) without deleting it —
     * deleting a commercial user cascades onto staff_assignments
     * (commercial_id has ->cascadeOnDelete()) and would silently wipe
     * their whole assignment history, which is never what "this person
     * no longer works here" should mean.
     *
     * Defaults to true so every existing account (including the ones
     * seeded before this migration ever ran) stays exactly as usable
     * as it was.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
