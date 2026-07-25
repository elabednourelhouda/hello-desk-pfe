<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Type de contact" (CRM > Suivi) — same spirit as prospect_sources
     * and activity_sectors: a simple admin-editable name/code list.
     *
     * Seeded here with the exact codes that were previously hardcoded
     * in both ProspectVisitControllers, so existing prospect_visits
     * rows keep resolving to the exact same label — only the *editing*
     * of this list moves to the database.
     *
     * @var array<int, array{name: string, code: string}>
     */
    private array $defaultContactTypes = [
        ['name' => 'Appel téléphonique', 'code' => 'appel_telephonique'],
        ['name' => 'Message WhatsApp', 'code' => 'whatsapp'],
        ['name' => 'Email', 'code' => 'email'],
        ['name' => 'Message reçu', 'code' => 'message_recu'],
        ['name' => 'Note interne', 'code' => 'note_interne'],
    ];

    public function up(): void
    {
        Schema::create('contact_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        foreach ($this->defaultContactTypes as $type) {
            DB::table('contact_types')->insert([
                'name' => $type['name'],
                'code' => $type['code'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_types');
    }
};