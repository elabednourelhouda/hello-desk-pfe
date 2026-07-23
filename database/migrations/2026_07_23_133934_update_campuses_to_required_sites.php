<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The required sites, expressed as: final values, plus a list of
     * "old identifiers" (old code or a fragment of the old name) used to
     * find and update a pre-existing row instead of creating a duplicate.
     *
     * @var array<int, array{name: string, code: string, city: string, address: string, old_codes: array<int, string>, old_name_like: string}>
     */
    private array $requiredSites = [
        [
            'name' => 'Centre Ville (A)',
            'code' => 'A',
            'city' => 'Casablanca',
            'address' => 'Centre Ville, Casablanca',
            'old_codes' => ['CVC', 'A'],
            'old_name_like' => '%Centre Ville%',
        ],
        [
            'name' => 'Sidi Maarouf (B)',
            'code' => 'B',
            'city' => 'Casablanca',
            'address' => 'Sidi Maarouf, Casablanca',
            'old_codes' => ['SM', 'B'],
            'old_name_like' => '%Sidi Ma%rouf%',
        ],
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->requiredSites as $site) {
            $existing = DB::table('campuses')
                ->where(function ($query) use ($site) {
                    $query->whereIn('code', $site['old_codes'])
                        ->orWhere('name', 'like', $site['old_name_like']);
                })
                ->first();

            if ($existing) {
                DB::table('campuses')
                    ->where('id', $existing->id)
                    ->update([
                        'name' => $site['name'],
                        'code' => $site['code'],
                        'city' => $existing->city ?? $site['city'],
                        'address' => $existing->address ?? $site['address'],
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('campuses')->insert([
                'name' => $site['name'],
                'code' => $site['code'],
                'city' => $site['city'],
                'address' => $site['address'],
                'description' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Deactivate (never delete) any other pre-existing site so historical
        // spaces/prospects/clients pointing at it keep a valid foreign key.
        $requiredNames = array_column($this->requiredSites, 'name');

        DB::table('campuses')
            ->whereNotIn('name', $requiredNames)
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        // Data-only migration: the safe reversible action is to reactivate
        // every site row, since we cannot know which ones were active
        // before this migration ran.
        DB::table('campuses')->update([
            'is_active' => true,
            'updated_at' => now(),
        ]);
    }
};
