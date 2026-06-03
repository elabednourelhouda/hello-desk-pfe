<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FloorSeeder extends Seeder
{
    public function run(): void
    {
        $centreVille = DB::table('campuses')->where('code', 'CVC')->first();
        $sidiMaarouf = DB::table('campuses')->where('code', 'SM')->first();

        if ($centreVille) {
            DB::table('floors')->updateOrInsert(
                ['campus_id' => $centreVille->id, 'name' => 'Rez-de-chaussée'],
                [
                    'level' => 0,
                    'description' => 'Rez-de-chaussée du campus Centre Ville.',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('floors')->updateOrInsert(
                ['campus_id' => $centreVille->id, 'name' => '1er étage'],
                [
                    'level' => 1,
                    'description' => 'Premier étage du campus Centre Ville.',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        if ($sidiMaarouf) {
            DB::table('floors')->updateOrInsert(
                ['campus_id' => $sidiMaarouf->id, 'name' => 'Rez-de-chaussée'],
                [
                    'level' => 0,
                    'description' => 'Rez-de-chaussée du campus Sidi Maârouf.',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('floors')->updateOrInsert(
                ['campus_id' => $sidiMaarouf->id, 'name' => '1er étage'],
                [
                    'level' => 1,
                    'description' => 'Premier étage du campus Sidi Maârouf.',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}