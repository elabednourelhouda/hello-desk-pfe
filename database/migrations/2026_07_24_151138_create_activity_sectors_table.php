<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Secteur d'activité" (Task 4) — a simple admin-editable list, same
     * spirit as prospect_sources but linked by a real foreign key
     * (activity_sector_id) instead of a string code, since there is no
     * pre-existing "activity_sector" column to stay compatible with.
     */
    private array $defaultSectors = [
        'Commerce / Distribution',
        'Technologie / IT',
        'Finance / Assurance',
        'Immobilier',
        'Santé',
        'Éducation / Formation',
        'Industrie / BTP',
        'Conseil / Services',
        'Tourisme / Hôtellerie',
        'Autre',
    ];

    public function up(): void
    {
        Schema::create('activity_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        foreach ($this->defaultSectors as $name) {
            DB::table('activity_sectors')->insert([
                'name' => $name,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_sectors');
    }
};