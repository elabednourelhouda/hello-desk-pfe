<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Types de durée de réservation" (par heure / par jour / au mois /
     * personnalisé) — was previously a hardcoded array in
     * Space::bookableDurationTypes(), Admin\ReservationController,
     * Commercial\ReservationController, and Commercial\ProspectController.
     * Moving it to a real table so an admin can rename an existing period
     * or add a new one (e.g. "Au trimestre") from Configuration, exactly
     * like space_types / space_statuses / prospect_sources.
     *
     * `sort_order` drives both display order and the up/down "reorder"
     * controls on the settings page.
     */
    public function up(): void
    {
        Schema::create('reservation_duration_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_duration_types');
    }
};
