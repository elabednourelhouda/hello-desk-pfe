<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Demo passwords
        |--------------------------------------------------------------------------
        |
        | These passwords are only for local demo/testing.
        | The users will still be asked to change password if must_change_password = true.
        |
        */

        $clientPassword = 'Client@2026';
        $commercialPassword = 'Commercial@2026';

        /*
        |--------------------------------------------------------------------------
        | Campuses
        |--------------------------------------------------------------------------
        */

        $centreVilleCampusId = $this->campusIdLike('Centre Ville');
        $sidiMaaroufCampusId = $this->campusIdLike('Sidi');

        /*
        |--------------------------------------------------------------------------
        | Commercial users
        |--------------------------------------------------------------------------
        */

        $commercials = [
            [
                'name' => 'Yassine Amrani',
                'email' => 'yassine.amrani@hellodesk.ma',
            ],
            [
                'name' => 'Imane Bennani',
                'email' => 'imane.bennani@hellodesk.ma',
            ],
            [
                'name' => 'Mehdi El Fassi',
                'email' => 'mehdi.elfassi@hellodesk.ma',
            ],
        ];

        foreach ($commercials as $commercialData) {
            User::updateOrCreate(
                ['email' => $commercialData['email']],
                [
                    'name' => $commercialData['name'],
                    'password' => Hash::make($commercialPassword),
                    'role' => 'commercial',
                    'must_change_password' => true,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Prospects
        |--------------------------------------------------------------------------
        */

        $prospects = [
            [
                'full_name' => 'Nadia El Mansouri',
                'email' => 'nadia.elmansouri@example.com',
                'phone' => '0611223344',
                'company_name' => 'Nadia Consulting',
                'need' => 'Bureau privé pour une consultante indépendante',
                'preferred_campus_id' => $centreVilleCampusId,
                'preferred_space_type' => 'bureau',
                'budget' => 3500,
                'source' => 'Visite directe',
                'crm_status' => 'new',
                'notes' => 'Intéressée par un bureau calme au Centre Ville.',
            ],
            [
                'full_name' => 'Omar Lahlou',
                'email' => 'omar.lahlou@example.com',
                'phone' => '0622334455',
                'company_name' => 'Lahlou Digital',
                'need' => 'Open space pour deux personnes',
                'preferred_campus_id' => $sidiMaaroufCampusId,
                'preferred_space_type' => 'position',
                'budget' => 2500,
                'source' => 'Site web',
                'crm_status' => 'contacted',
                'notes' => 'À rappeler pour confirmer le nombre de positions.',
            ],
            [
                'full_name' => 'Salma Berrada',
                'email' => 'salma.berrada@example.com',
                'phone' => '0633445566',
                'company_name' => 'Berrada Events',
                'need' => 'Salle de réunion pour formations',
                'preferred_campus_id' => $centreVilleCampusId,
                'preferred_space_type' => 'salle_reunion',
                'budget' => 1200,
                'source' => 'Instagram',
                'crm_status' => 'proposal_sent',
                'notes' => 'Proposition envoyée pour une salle de réunion à la journée.',
            ],
            [
                'full_name' => 'Karim Tazi',
                'email' => 'karim.tazi@example.com',
                'phone' => '0644556677',
                'company_name' => 'Tazi Finance',
                'need' => 'Bureau privé mensuel',
                'preferred_campus_id' => $sidiMaaroufCampusId,
                'preferred_space_type' => 'bureau',
                'budget' => 4500,
                'source' => 'Recommandation',
                'crm_status' => 'negotiation',
                'notes' => 'En négociation sur le prix mensuel.',
            ],
            [
                'full_name' => 'Hind Alaoui',
                'email' => 'hind.alaoui@example.com',
                'phone' => '0655667788',
                'company_name' => 'Alaoui Studio',
                'need' => 'Position open space flexible',
                'preferred_campus_id' => $centreVilleCampusId,
                'preferred_space_type' => 'position',
                'budget' => 1800,
                'source' => 'Appel téléphonique',
                'crm_status' => 'lost',
                'notes' => 'Prospect perdu : budget insuffisant.',
            ],
            [
                'full_name' => 'Anas Chraibi',
                'email' => 'anas.chraibi@example.com',
                'phone' => '0666778899',
                'company_name' => 'Chraibi Tech',
                'need' => 'Bureau privé pour startup',
                'preferred_campus_id' => $sidiMaaroufCampusId,
                'preferred_space_type' => 'bureau',
                'budget' => 5200,
                'source' => 'LinkedIn',
                'crm_status' => 'converted',
                'notes' => 'Converti en client après visite.',
            ],
            [
                'full_name' => 'Meryem Kettani',
                'email' => 'meryem.kettani@example.com',
                'phone' => '0677889900',
                'company_name' => 'Kettani Legal',
                'need' => 'Bureau privé proche du centre',
                'preferred_campus_id' => $centreVilleCampusId,
                'preferred_space_type' => 'bureau',
                'budget' => 4800,
                'source' => 'Visite directe',
                'crm_status' => 'converted',
                'notes' => 'Convertie en cliente avec bureau mensuel.',
            ],
        ];

        $prospectIds = [];

        foreach ($prospects as $prospectData) {
            $email = $prospectData['email'];

            $data = array_merge($prospectData, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('prospects')->updateOrInsert(
                ['email' => $email],
                $this->onlyExistingColumns('prospects', $data)
            );

            $prospectIds[$email] = DB::table('prospects')
                ->where('email', $email)
                ->value('id');
        }

        /*
        |--------------------------------------------------------------------------
        | Client users + client records
        |--------------------------------------------------------------------------
        */

        $clients = [
            [
                'full_name' => 'Anas Chraibi',
                'email' => 'anas.chraibi@example.com',
                'phone' => '0666778899',
                'company_name' => 'Chraibi Tech',
                'main_campus_id' => $sidiMaaroufCampusId,
                'registered_at' => now()->subDays(8)->toDateString(),
                'status' => 'active',
                'notes' => 'Client converti depuis le CRM. Intéressé par un bureau privé.',
                'prospect_email' => 'anas.chraibi@example.com',
            ],
            [
                'full_name' => 'Meryem Kettani',
                'email' => 'meryem.kettani@example.com',
                'phone' => '0677889900',
                'company_name' => 'Kettani Legal',
                'main_campus_id' => $centreVilleCampusId,
                'registered_at' => now()->subDays(5)->toDateString(),
                'status' => 'active',
                'notes' => 'Cliente active. Besoin principal : bureau privé mensuel.',
                'prospect_email' => 'meryem.kettani@example.com',
            ],
            [
                'full_name' => 'Sofia Idrissi',
                'email' => 'sofia.idrissi@example.com',
                'phone' => '0688990011',
                'company_name' => 'Idrissi Marketing',
                'main_campus_id' => $centreVilleCampusId,
                'registered_at' => now()->subDays(20)->toDateString(),
                'status' => 'active',
                'notes' => 'Cliente ajoutée directement par l’administration.',
                'prospect_email' => null,
            ],
            [
                'full_name' => 'Rachid Benomar',
                'email' => 'rachid.benomar@example.com',
                'phone' => '0699001122',
                'company_name' => 'Benomar Services',
                'main_campus_id' => $sidiMaaroufCampusId,
                'registered_at' => now()->subDays(40)->toDateString(),
                'status' => 'inactive',
                'notes' => 'Compte inactif pour test de désactivation.',
                'prospect_email' => null,
            ],
        ];

        foreach ($clients as $clientData) {
            $user = User::updateOrCreate(
                ['email' => $clientData['email']],
                [
                    'name' => $clientData['full_name'],
                    'password' => Hash::make($clientPassword),
                    'role' => 'client',
                    'must_change_password' => true,
                ]
            );

            $clientRecord = [
                'user_id' => $user->id,
                'full_name' => $clientData['full_name'],
                'email' => $clientData['email'],
                'phone' => $clientData['phone'],
                'company_name' => $clientData['company_name'],
                'main_campus_id' => $clientData['main_campus_id'],
                'registered_at' => $clientData['registered_at'],
                'status' => $clientData['status'],
                'notes' => $clientData['notes'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (
                !empty($clientData['prospect_email'])
                && isset($prospectIds[$clientData['prospect_email']])
            ) {
                $clientRecord['prospect_id'] = $prospectIds[$clientData['prospect_email']];
            }

            DB::table('clients')->updateOrInsert(
                ['email' => $clientData['email']],
                $this->onlyExistingColumns('clients', $clientRecord)
            );

            $clientId = DB::table('clients')
                ->where('email', $clientData['email'])
                ->value('id');

            if (
                !empty($clientData['prospect_email'])
                && isset($prospectIds[$clientData['prospect_email']])
            ) {
                $prospectUpdate = [
                    'crm_status' => 'converted',
                    'updated_at' => now(),
                    'converted_client_id' => $clientId,
                    'converted_at' => now(),
                ];

                DB::table('prospects')
                    ->where('id', $prospectIds[$clientData['prospect_email']])
                    ->update($this->onlyExistingColumns('prospects', $prospectUpdate));
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Console summary
        |--------------------------------------------------------------------------
        */

        echo PHP_EOL;
        echo "Demo data inserted successfully." . PHP_EOL;
        echo PHP_EOL;

        echo "Commercial accounts:" . PHP_EOL;
        echo "- yassine.amrani@hellodesk.ma / {$commercialPassword}" . PHP_EOL;
        echo "- imane.bennani@hellodesk.ma / {$commercialPassword}" . PHP_EOL;
        echo "- mehdi.elfassi@hellodesk.ma / {$commercialPassword}" . PHP_EOL;
        echo PHP_EOL;

        echo "Client accounts:" . PHP_EOL;
        echo "- anas.chraibi@example.com / {$clientPassword}" . PHP_EOL;
        echo "- meryem.kettani@example.com / {$clientPassword}" . PHP_EOL;
        echo "- sofia.idrissi@example.com / {$clientPassword}" . PHP_EOL;
        echo "- rachid.benomar@example.com / {$clientPassword} (inactive client)" . PHP_EOL;
        echo PHP_EOL;
    }

    private function campusIdLike(string $name): ?int
    {
        if (!Schema::hasTable('campuses')) {
            return null;
        }

        return DB::table('campuses')
            ->where('name', 'like', "%{$name}%")
            ->value('id');
    }

    private function onlyExistingColumns(string $table, array $data): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        $columns = Schema::getColumnListing($table);

        return array_intersect_key($data, array_flip($columns));
    }
}