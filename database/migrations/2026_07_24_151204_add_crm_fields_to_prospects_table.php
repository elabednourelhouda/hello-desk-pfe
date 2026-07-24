<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            // Task 4: "Local" / "Étranger"
            $table->string('origin')->nullable()->after('source');

            // Task 4: "Personne Physique" / "Personne Morale" (mirrors
            // clients.client_type, but prospects has no such column yet)
            $table->string('customer_type')->nullable()->after('origin');

            // Task 4: "Secteur d'activité", configurable via activity_sectors
            $table->foreignId('activity_sector_id')
                ->nullable()
                ->after('customer_type')
                ->constrained('activity_sectors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropForeign(['activity_sector_id']);
            $table->dropColumn(['origin', 'customer_type', 'activity_sector_id']);
        });
    }
};