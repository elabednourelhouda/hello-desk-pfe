<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@hellodesk.ma'],
            [
                'name' => 'Administrateur Hello Desk',
                'phone' => '+212600000001',
                'password' => 'HelloDesk123',
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'commercial@hellodesk.ma'],
            [
                'name' => 'Commercial Hello Desk',
                'phone' => '+212600000002',
                'password' => 'HelloDesk123',
                'role' => 'commercial',
            ]
        );

        User::updateOrCreate(
            ['email' => 'client@hellodesk.ma'],
            [
                'name' => 'Client Démo',
                'phone' => '+212600000003',
                'password' => 'HelloDesk123',
                'role' => 'client',
            ]
        );
    }
}