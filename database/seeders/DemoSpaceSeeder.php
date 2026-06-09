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
            ->where('map_key', 'centre_ville_2')
            ->first();

        $officeType = DB::table('space_types')->where('code', 'office')->first();
        $meetingType = DB::table('space_types')->where('code', 'meeting_room')->first();
        $positionType = DB::table('space_types')->where('code', 'desk_position')->first();

        if (!$campus || !$floor || !$officeType || !$meetingType || !$positionType) {
            return;
        }

        $spaces = [
            ['internal_code' => 'CV-2-B1', 'name' => 'Bureau B1', 'space_type_id' => $officeType->id, 'capacity' => 2, 'area_m2' => 14, 'price_per_hour' => null, 'price_per_day' => 300, 'price_per_month' => 4500, 'status' => 'available', 'description' => 'Bureau privé pour 1 à 2 personnes.'],
            ['internal_code' => 'CV-2-B2', 'name' => 'Bureau B2', 'space_type_id' => $officeType->id, 'capacity' => 2, 'area_m2' => 15, 'price_per_hour' => null, 'price_per_day' => 320, 'price_per_month' => 4800, 'status' => 'occupied', 'description' => 'Bureau privé actuellement occupé.'],
            ['internal_code' => 'CV-2-SR1', 'name' => 'Salle réunion', 'space_type_id' => $meetingType->id, 'capacity' => 8, 'area_m2' => 24, 'price_per_hour' => 150, 'price_per_day' => 900, 'price_per_month' => null, 'status' => 'available', 'description' => 'Salle de réunion équipée.'],
            ['internal_code' => 'CV-2-B3', 'name' => 'Bureau B3', 'space_type_id' => $officeType->id, 'capacity' => 1, 'area_m2' => 12, 'price_per_hour' => null, 'price_per_day' => 280, 'price_per_month' => 4200, 'status' => 'maintenance', 'description' => 'Bureau privé temporairement en maintenance.'],

            ['internal_code' => 'CV-2-P1', 'name' => 'Position P1', 'space_type_id' => $positionType->id, 'capacity' => 1, 'area_m2' => 4, 'price_per_hour' => 50, 'price_per_day' => 120, 'price_per_month' => 1800, 'status' => 'available', 'description' => 'Position individuelle dans l’open space.'],
            ['internal_code' => 'CV-2-P2', 'name' => 'Position P2', 'space_type_id' => $positionType->id, 'capacity' => 1, 'area_m2' => 4, 'price_per_hour' => 50, 'price_per_day' => 120, 'price_per_month' => 1800, 'status' => 'available', 'description' => 'Position individuelle dans l’open space.'],
            ['internal_code' => 'CV-2-P3', 'name' => 'Position P3', 'space_type_id' => $positionType->id, 'capacity' => 1, 'area_m2' => 4, 'price_per_hour' => 50, 'price_per_day' => 120, 'price_per_month' => 1800, 'status' => 'occupied', 'description' => 'Position open space occupée.'],
            ['internal_code' => 'CV-2-P4', 'name' => 'Position P4', 'space_type_id' => $positionType->id, 'capacity' => 1, 'area_m2' => 4, 'price_per_hour' => 50, 'price_per_day' => 120, 'price_per_month' => 1800, 'status' => 'available', 'description' => 'Position individuelle dans l’open space.'],
            ['internal_code' => 'CV-2-P5', 'name' => 'Position P5', 'space_type_id' => $positionType->id, 'capacity' => 1, 'area_m2' => 4, 'price_per_hour' => 50, 'price_per_day' => 120, 'price_per_month' => 1800, 'status' => 'available', 'description' => 'Position individuelle dans l’open space.'],
            ['internal_code' => 'CV-2-P6', 'name' => 'Position P6', 'space_type_id' => $positionType->id, 'capacity' => 1, 'area_m2' => 4, 'price_per_hour' => 50, 'price_per_day' => 120, 'price_per_month' => 1800, 'status' => 'available', 'description' => 'Position individuelle dans l’open space.'],

            ['internal_code' => 'CV-2-B4', 'name' => 'Bureau B4', 'space_type_id' => $officeType->id, 'capacity' => 2, 'area_m2' => 14, 'price_per_hour' => null, 'price_per_day' => 300, 'price_per_month' => 4500, 'status' => 'available', 'description' => 'Bureau privé pour 1 à 2 personnes.'],
            ['internal_code' => 'CV-2-B5', 'name' => 'Bureau B5', 'space_type_id' => $officeType->id, 'capacity' => 2, 'area_m2' => 14, 'price_per_hour' => null, 'price_per_day' => 300, 'price_per_month' => 4500, 'status' => 'available', 'description' => 'Bureau privé pour 1 à 2 personnes.'],
            ['internal_code' => 'CV-2-B6', 'name' => 'Bureau B6', 'space_type_id' => $officeType->id, 'capacity' => 1, 'area_m2' => 11, 'price_per_hour' => null, 'price_per_day' => 260, 'price_per_month' => 3900, 'status' => 'unavailable', 'description' => 'Bureau momentanément indisponible.'],
            ['internal_code' => 'CV-2-B7', 'name' => 'Bureau B7', 'space_type_id' => $officeType->id, 'capacity' => 2, 'area_m2' => 15, 'price_per_hour' => null, 'price_per_day' => 320, 'price_per_month' => 4800, 'status' => 'available', 'description' => 'Bureau privé pour 1 à 2 personnes.'],
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