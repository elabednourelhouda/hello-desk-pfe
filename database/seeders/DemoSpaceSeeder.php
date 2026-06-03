<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSpaceSeeder extends Seeder
{
    public function run(): void
    {
        $campus = DB::table('campuses')->where('code', 'CVC')->first();
        $floor = DB::table('floors')
            ->where('campus_id', $campus?->id)
            ->where('level', 0)
            ->first();

        $officeType = DB::table('space_types')->where('code', 'office')->first();
        $meetingType = DB::table('space_types')->where('code', 'meeting_room')->first();
        $positionType = DB::table('space_types')->where('code', 'desk_position')->first();

        if (!$campus || !$floor || !$officeType || !$meetingType || !$positionType) {
            return;
        }

        $spaces = [
            [
                'name' => 'Bureau A1',
                'internal_code' => 'CVC-RDC-BUR-A1',
                'space_type_id' => $officeType->id,
                'capacity' => 2,
                'area_m2' => 14,
                'price_per_hour' => null,
                'price_per_day' => 300,
                'price_per_month' => 4500,
                'status' => 'available',
                'description' => 'Bureau privé pour 1 à 2 personnes.',
                'map_x' => 80,
                'map_y' => 80,
                'map_width' => 160,
                'map_height' => 110,
            ],
            [
                'name' => 'Salle Réunion R1',
                'internal_code' => 'CVC-RDC-SR-R1',
                'space_type_id' => $meetingType->id,
                'capacity' => 8,
                'area_m2' => 24,
                'price_per_hour' => 150,
                'price_per_day' => 900,
                'price_per_month' => null,
                'status' => 'available',
                'description' => 'Salle de réunion équipée pour les rendez-vous professionnels.',
                'map_x' => 280,
                'map_y' => 80,
                'map_width' => 220,
                'map_height' => 140,
            ],
            [
                'name' => 'Position P1',
                'internal_code' => 'CVC-RDC-POS-P1',
                'space_type_id' => $positionType->id,
                'capacity' => 1,
                'area_m2' => 4,
                'price_per_hour' => 50,
                'price_per_day' => 120,
                'price_per_month' => 1800,
                'status' => 'available',
                'description' => 'Position individuelle dans l’open space.',
                'map_x' => 90,
                'map_y' => 250,
                'map_width' => 90,
                'map_height' => 70,
            ],
        ];

        foreach ($spaces as $space) {
            DB::table('spaces')->updateOrInsert(
                ['internal_code' => $space['internal_code']],
                array_merge($space, [
                    'campus_id' => $campus->id,
                    'floor_id' => $floor->id,
                    'internal_notes' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}