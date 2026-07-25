<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Admin\SpaceController used to let an admin manually pick "Réservé"
     * (code 'reserved') on a space. In practice this was already inert:
     * InteractiveMapController@index only ever matches 'occupé',
     * 'indisponible', or 'maintenance' variants against the stored
     * `status` column and otherwise falls through to checking active
     * reservations, so a manually-set 'reserved' value was silently
     * ignored and displayed exactly like 'available' unless the space
     * also happened to have a real reservation.
     *
     * Now that "Réservé" is exclusively a computed, time-bound display
     * status (never a database value on spaces.status), this normalizes
     * any pre-existing 'reserved' rows back to 'available' — a purely
     * cosmetic cleanup with zero effect on what the map already showed.
     */
    public function up(): void
    {
        DB::table('spaces')
            ->whereIn('status', ['reserved', 'réservé', 'reserve'])
            ->update(['status' => 'available']);
    }

    public function down(): void
    {
        // Intentionally irreversible: we can no longer tell which of the
        // now-'available' rows were originally 'reserved' before this
        // migration ran.
    }
};