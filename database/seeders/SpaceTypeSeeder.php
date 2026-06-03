<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpaceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Bureau',
                'code' => 'office',
                'description' => 'Espace privé fermé pour une ou plusieurs personnes.',
            ],
            [
                'name' => 'Salle de réunion',
                'code' => 'meeting_room',
                'description' => 'Espace réservé aux réunions, rendez-vous ou présentations.',
            ],
            [
                'name' => 'Position',
                'code' => 'desk_position',
                'description' => 'Poste de travail individuel dans un open space.',
            ],
        ];

        foreach ($types as $type) {
            DB::table('space_types')->updateOrInsert(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}