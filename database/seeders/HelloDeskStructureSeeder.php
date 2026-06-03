<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Floor;
use App\Models\Space;
use App\Models\SpaceType;
use Illuminate\Database\Seeder;

class HelloDeskStructureSeeder extends Seeder
{
    public function run(): void
    {
        $centreVille = Campus::firstOrCreate([
            'name' => 'Centre Ville Casablanca',
        ]);

        $sidiMaarouf = Campus::firstOrCreate([
            'name' => 'Sidi Maârouf',
        ]);

        $bureau = SpaceType::firstOrCreate([
            'name' => 'Bureau',
        ]);

        $meetingRoom = SpaceType::firstOrCreate([
            'name' => 'Salle de réunion',
        ]);

        $position = SpaceType::firstOrCreate([
            'name' => 'Position',
        ]);

        $cvRdc = Floor::firstOrCreate([
            'campus_id' => $centreVille->id,
            'name' => 'Rez-de-chaussée',
        ], [
            'code' => 'CV-RDC',
        ]);

        $cvFirst = Floor::updateOrCreate([
            'campus_id' => $centreVille->id,
            'name' => '1er étage',
        ], [
            'code' => 'CV-1',
            'map_key' => 'centre_ville_1',
        ]);

        $cvSecond = Floor::updateOrCreate([
            'campus_id' => $centreVille->id,
            'name' => '2ème étage',
        ], [
            'code' => 'CV-2',
            'map_key' => 'centre_ville_2',
        ]);

        $cvSixth = Floor::updateOrCreate([
            'campus_id' => $centreVille->id,
            'name' => '6ème étage',
        ], [
            'code' => 'CV-6',
            'map_key' => 'centre_ville_6',
        ]);

        $smRdc = Floor::firstOrCreate([
            'campus_id' => $sidiMaarouf->id,
            'name' => 'Rez-de-chaussée',
        ], [
            'code' => 'SM-RDC',
        ]);

        $spaces = [
            [
                'campus_id' => $centreVille->id,
                'floor_id' => $cvRdc->id,
                'space_type_id' => $bureau->id,
                'name' => 'Bureau A1',
                'code' => 'CV-RDC-A1',
                'capacity' => 2,
                'surface' => 12,
                'price_per_day' => 350,
                'price_per_month' => 4500,
                'status' => 'Disponible',
            ],
            [
                'campus_id' => $centreVille->id,
                'floor_id' => $cvRdc->id,
                'space_type_id' => $bureau->id,
                'name' => 'Bureau A2',
                'code' => 'CV-RDC-A2',
                'capacity' => 1,
                'surface' => 9,
                'price_per_day' => 250,
                'price_per_month' => 3500,
                'status' => 'Occupé',
            ],
            [
                'campus_id' => $centreVille->id,
                'floor_id' => $cvRdc->id,
                'space_type_id' => $meetingRoom->id,
                'name' => 'Salle Réunion 1',
                'code' => 'CV-RDC-SR1',
                'capacity' => 8,
                'surface' => 20,
                'price_per_hour' => 150,
                'price_per_day' => 900,
                'status' => 'Disponible',
            ],
            [
                'campus_id' => $centreVille->id,
                'floor_id' => $cvFirst->id,
                'space_type_id' => $position->id,
                'name' => 'Position P1',
                'code' => 'CV-1-P1',
                'capacity' => 1,
                'surface' => 4,
                'price_per_day' => 120,
                'price_per_month' => 1600,
                'status' => 'Disponible',
            ],
            [
                'campus_id' => $centreVille->id,
                'floor_id' => $cvFirst->id,
                'space_type_id' => $position->id,
                'name' => 'Position P2',
                'code' => 'CV-1-P2',
                'capacity' => 1,
                'surface' => 4,
                'price_per_day' => 120,
                'price_per_month' => 1600,
                'status' => 'En maintenance',
            ],
            [
                'campus_id' => $sidiMaarouf->id,
                'floor_id' => $smRdc->id,
                'space_type_id' => $bureau->id,
                'name' => 'Bureau SM1',
                'code' => 'SM-RDC-B1',
                'capacity' => 2,
                'surface' => 14,
                'price_per_day' => 350,
                'price_per_month' => 4800,
                'status' => 'Disponible',
            ],
            [
                'campus_id' => $sidiMaarouf->id,
                'floor_id' => $smRdc->id,
                'space_type_id' => $meetingRoom->id,
                'name' => 'Salle Réunion SM',
                'code' => 'SM-RDC-SR1',
                'capacity' => 10,
                'surface' => 24,
                'price_per_hour' => 180,
                'price_per_day' => 1000,
                'status' => 'Réservé',
            ],
        ];

        foreach ($spaces as $space) {
            Space::updateOrCreate(
                ['code' => $space['code']],
                $space
            );
        }
    }
}