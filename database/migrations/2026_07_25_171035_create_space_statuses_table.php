<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Statut de l'espace" — same spirit as space_types/contact_types:
     * a simple admin-editable name/code list.
     *
     * IMPORTANT: "Réservé" is deliberately NOT included here. Unlike the
     * other four statuses, it is never manually assigned to a space —
     * both InteractiveMapController@index (admin & commercial) compute
     * it dynamically from whether an active reservation currently
     * overlaps the viewed period. Making it a normal CRUD-able status
     * an admin could pick by hand would let it get "stuck" forever,
     * defeating the whole point of it being time-bound.
     *
     * Seeded with the exact codes previously hardcoded in
     * Admin\SpaceController::$statuses, so every existing space's
     * `status` column keeps resolving to the exact same label — only
     * the *editing* of this list moves to the database.
     *
     * @var array<int, array{name: string, code: string}>
     */
    private array $defaultStatuses = [
        ['name' => 'Disponible', 'code' => 'available'],
        ['name' => 'Occupé', 'code' => 'occupied'],
        ['name' => 'Indisponible', 'code' => 'unavailable'],
        ['name' => 'Maintenance', 'code' => 'maintenance'],
    ];

    public function up(): void
    {
        Schema::create('space_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        foreach ($this->defaultStatuses as $status) {
            DB::table('space_statuses')->insert([
                'name' => $status['name'],
                'code' => $status['code'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('space_statuses');
    }
};