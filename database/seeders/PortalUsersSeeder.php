<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PortalUsersSeeder extends Seeder
{
    public function run(): void
    {
        // --- Admin portal ---------------------------------------------------
        $admin = User::updateOrCreate(
            ['email' => 'admin@hellodesk.ma'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('ChangeMe!Admin1'),
                'role' => 'admin',
                'must_change_password' => true,
            ]
        );

        // --- Commercial portal ----------------------------------------------
        $commercial = User::updateOrCreate(
            ['email' => 'commercial@hellodesk.ma'],
            [
                'name' => 'Commercial Agent',
                'password' => Hash::make('ChangeMe!Commercial1'),
                'role' => 'commercial',
                'must_change_password' => true,
            ]
        );

        // --- Client portal ----------------------------------------------------
        $clientUser = User::updateOrCreate(
            ['email' => 'client@hellodesk.ma'],
            [
                'name' => 'Test Client',
                'password' => Hash::make('ChangeMe!Client1'),
                'role' => 'client',
                'must_change_password' => true,
            ]
        );

        // A "client" role user in this app also expects a related Client
        // profile row (see User::clientProfile()). Create one so the
        // client portal doesn't break on first login due to a missing
        // profile relation.
        Client::updateOrCreate(
            ['user_id' => $clientUser->id],
            [
                'user_id' => $clientUser->id,
                'full_name' => $clientUser->name,   // NOT NULL in clients table
                'email' => $clientUser->email,       // NOT NULL in clients table
                'status' => 'active',
            ]
        );

        $this->command->info('Portal accounts recreated:');
        $this->command->table(
            ['Role', 'Email', 'Temporary password'],
            [
                ['admin', 'admin@hellodesk.ma', 'ChangeMe!Admin1'],
                ['commercial', 'commercial@hellodesk.ma', 'ChangeMe!Commercial1'],
                ['client', 'client@hellodesk.ma', 'ChangeMe!Client1'],
            ]
        );
    }
}