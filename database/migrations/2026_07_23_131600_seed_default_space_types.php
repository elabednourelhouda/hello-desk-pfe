<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The three space types required by the new configuration.
     *
     * @var array<int, array{name: string, code: string}>
     */
    private array $requiredTypes = [
        ['name' => 'Bureau', 'code' => 'bureau'],
        ['name' => 'Co-working', 'code' => 'co-working'],
        ['name' => 'Salle de formation', 'code' => 'salle-formation'],
    ];

    /**
     * Run the migrations.
     *
     * This is a DATA migration, not a schema migration: the `space_types`
     * table already has everything we need (name, code, description,
     * is_active). We upsert the three required rows and DEACTIVATE
     * (never delete) any pre-existing space type that is not in the new
     * list, so that:
     *   - existing `spaces` rows keep a valid `space_type_id` foreign key
     *   - historical data/reports are not lost
     *   - the old types simply stop showing up in active dropdowns
     */
    public function up(): void
    {
        $now = now();

        foreach ($this->requiredTypes as $type) {
            $existing = DB::table('space_types')->where('name', $type['name'])->first();

            if ($existing) {
                DB::table('space_types')
                    ->where('id', $existing->id)
                    ->update([
                        'code' => $existing->code ?? $type['code'],
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('space_types')->insert([
                'name' => $type['name'],
                'code' => $type['code'],
                'description' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $requiredNames = array_column($this->requiredTypes, 'name');

        DB::table('space_types')
            ->whereNotIn('name', $requiredNames)
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * We cannot know which rows were active before this migration ran
     * (that information was not stored anywhere), so the safe and
     * reversible action is to reactivate every space type row. This does
     * not delete the three required types we created — that is
     * intentional, since deleting them here could break spaces created
     * after this migration ran.
     */
    public function down(): void
    {
        DB::table('space_types')->update([
            'is_active' => true,
            'updated_at' => now(),
        ]);
    }
};
