<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('campuses')->updateOrInsert(
            ['name' => 'Centre Ville Casablanca'],
            [
                'code' => 'CVC',
                'city' => 'Casablanca',
                'address' => 'Centre Ville, Casablanca',
                'description' => 'Campus Hello Desk situé au centre-ville de Casablanca.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('campuses')->updateOrInsert(
            ['name' => 'Sidi Maârouf'],
            [
                'code' => 'SM',
                'city' => 'Casablanca',
                'address' => 'Sidi Maârouf, Casablanca',
                'description' => 'Campus Hello Desk situé à Sidi Maârouf.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}