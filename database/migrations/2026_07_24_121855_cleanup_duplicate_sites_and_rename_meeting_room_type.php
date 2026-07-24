<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The only two sites that should ever exist right now.
     */
    private array $requiredSiteNames = [
        'Centre Ville (A)',
        'Sidi Maarouf (B)',
    ];

    /**
     * Cleans up duplicate campuses/floors/spaces created by an older,
     * mismatched seeder (HelloDeskStructureSeeder), and renames the
     * "Salle de formation" space type to "Salle de réunion" to match
     * what's actually needed (Bureau / Co-working / Salle de réunion).
     *
     * This is a one-way data cleanup: down() cannot restore deleted rows,
     * since we have no record of what they were before deletion.
     */
    public function up(): void
    {
        // Reservations should not exist yet on this fresh setup, but
        // clear them first just in case, so the space delete below is
        // never blocked by a foreign key.
        DB::table('reservations')->delete();

        // Deleting floors cascades to spaces (spaces.floor_id has
        // cascadeOnDelete), which in turn cascades to accessory_space
        // pivot rows (accessory_space.space_id has cascadeOnDelete).
        // This removes every floor and every space, real or test data,
        // across every campus — the fresh, blank slate that was asked for.
        DB::table('floors')->delete();

        // Now safe to remove any campus that isn't one of the two real
        // sites (e.g. the duplicate "Centre Ville Casablanca" / "Sidi
        // Maârouf" rows created by the old seeder).
        DB::table('campuses')
            ->whereNotIn('name', $this->requiredSiteNames)
            ->delete();

        // Rename "Salle de formation" -> "Salle de réunion" so the three
        // required space types are exactly: Bureau, Co-working, Salle de
        // réunion. If a "Salle de réunion" row already exists (created by
        // the old mismatched seeder), merge into it instead of renaming,
        // to avoid a unique constraint collision on `name`.
        $formation = DB::table('space_types')->where('name', 'Salle de formation')->first();
        $reunion = DB::table('space_types')->where('name', 'Salle de réunion')->first();

        if ($reunion) {
            // The correct row already exists — just make sure it's active
            // and has the right code, and drop the now-redundant one.
            DB::table('space_types')
                ->where('id', $reunion->id)
                ->update([
                    'code' => 'salle-reunion',
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            if ($formation) {
                DB::table('space_types')->where('id', $formation->id)->delete();
            }
        } elseif ($formation) {
            DB::table('space_types')
                ->where('id', $formation->id)
                ->update([
                    'name' => 'Salle de réunion',
                    'code' => 'salle-reunion',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Data-only, destructive cleanup: nothing meaningful to reverse.
    }
};