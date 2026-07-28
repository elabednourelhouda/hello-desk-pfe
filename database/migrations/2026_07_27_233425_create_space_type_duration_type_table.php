<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which duration types are bookable for which space type. A plain
     * many-to-many — no extra pivot data — because
     * `engagement_duration_unit` (hour/half_day/day/month) is validated
     * completely independently via Space::bookableEngagementUnits(),
     * which stays hardcoded (it was never part of this request; the
     * two whitelists were already unrelated in the original code, not a
     * pairing keyed by duration type).
     *
     * A space type with ZERO rows here (any future type an admin adds
     * from Configuration -> Types d'espaces without configuring this)
     * falls back to "every active duration type is bookable" — see
     * Space::bookableDurationTypes(). That preserves the original
     * `default => [...]` arm's behavior: a newly created dropdown value
     * should never silently block bookings.
     */
    public function up(): void
    {
        Schema::create('space_type_duration_type', function (Blueprint $table) {
            $table->id();

            $table->foreignId('space_type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('reservation_duration_type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['space_type_id', 'reservation_duration_type_id'],
                'space_type_duration_type_unique'
            );
        });

        $this->seedDefaults();
    }

    public function down(): void
    {
        Schema::dropIfExists('space_type_duration_type');
        // Intentionally NOT dropping reservation_duration_types rows here
        // (handled by the down() of the table's own creation migration) —
        // this migration only owns the pivot table.
    }

    /**
     * Data seed, run once as part of this migration (same "create table,
     * then seed the rows the app already assumed existed" pattern as
     * seed_default_space_types / create_prospect_sources_table).
     *
     * IMPORTANT: matched against space_types.code, not .name — same
     * caveat Space::bookableDurationTypes() has always carried: the
     * "Salle de formation" row's live `code` is `salle-reunion` as of
     * the 2026_07_24_121855 migration, not `salle-formation`. Verified
     * directly against that migration before writing this, rather than
     * trusted from memory.
     */
    private function seedDefaults(): void
    {
        $now = now();

        $durationTypes = [
            ['name' => 'À l’heure', 'code' => 'hourly', 'sort_order' => 1],
            ['name' => 'À la journée', 'code' => 'daily', 'sort_order' => 2],
            ['name' => 'Au mois', 'code' => 'monthly', 'sort_order' => 3],
            ['name' => 'Personnalisé', 'code' => 'custom', 'sort_order' => 4],
        ];

        foreach ($durationTypes as $type) {
            // Defensive upsert: if this migration is ever re-run after a
            // partial failure, don't blow up on the unique `code` index.
            $existing = DB::table('reservation_duration_types')->where('code', $type['code'])->first();

            if ($existing) {
                continue;
            }

            DB::table('reservation_duration_types')->insert(array_merge($type, [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $durationTypeIds = DB::table('reservation_duration_types')->pluck('id', 'code');

        // Mirrors the exact match() arms Space::bookableDurationTypes()
        // used to hardcode, so behavior is unchanged the moment this
        // migration finishes: a training/meeting room still can't be
        // booked monthly, an office/co-working desk still can't be
        // booked hourly.
        $bookableByCode = [
            'salle-reunion' => ['hourly', 'daily', 'custom'],
            'bureau' => ['daily', 'monthly', 'custom'],
            'co-working' => ['daily', 'monthly', 'custom'],
        ];

        foreach ($bookableByCode as $spaceTypeCode => $codes) {
            $spaceTypeId = DB::table('space_types')->where('code', $spaceTypeCode)->value('id');

            if (! $spaceTypeId) {
                // Defensive, not expected: if a fresh install seeds space
                // types under different codes later, skip rather than
                // fatal-error the whole migration.
                continue;
            }

            foreach ($codes as $code) {
                if (! isset($durationTypeIds[$code])) {
                    continue;
                }

                DB::table('space_type_duration_type')->insertOrIgnore([
                    'space_type_id' => $spaceTypeId,
                    'reservation_duration_type_id' => $durationTypeIds[$code],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
