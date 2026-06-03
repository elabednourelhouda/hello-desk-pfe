<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CampusSeeder::class,
            FloorSeeder::class,
            SpaceTypeSeeder::class,
            AccessorySeeder::class,
            DemoSpaceSeeder::class,
        ]);
    }
}