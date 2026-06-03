<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessorySeeder extends Seeder
{
    public function run(): void
    {
        $accessories = [
            'WiFi',
            'Climatisation',
            'Imprimante',
            'Écran',
            'Tableau',
            'Projecteur',
            'Prises électriques',
        ];

        foreach ($accessories as $accessory) {
            DB::table('accessories')->updateOrInsert(
                ['name' => $accessory],
                [
                    'icon' => null,
                    'description' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}