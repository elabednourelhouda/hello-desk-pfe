<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The list that used to be hardcoded in
     * Admin\ProspectController::prospectSources() and
     * Commercial\ProspectController::prospectSources(). Seeded here so
     * existing prospects' `source` values (which store the `code`, e.g.
     * "site_web") keep working exactly as before — only the *editing* of
     * this list moves to the database.
     *
     * @var array<int, array{name: string, code: string}>
     */
    private array $defaultSources = [
        ['name' => 'Passage direct', 'code' => 'passage_direct'],
        ['name' => 'Appel téléphonique', 'code' => 'appel_telephonique'],
        ['name' => 'WhatsApp', 'code' => 'whatsapp'],
        ['name' => 'Email', 'code' => 'email'],
        ['name' => 'Site web', 'code' => 'site_web'],
        ['name' => 'Instagram', 'code' => 'instagram'],
        ['name' => 'Facebook', 'code' => 'facebook'],
        ['name' => 'Google / Maps', 'code' => 'google_maps'],
        ['name' => 'Recommandation', 'code' => 'recommandation'],
        ['name' => 'Ancien client', 'code' => 'ancien_client'],
        ['name' => 'Événement / networking', 'code' => 'evenement'],
        ['name' => 'Autre', 'code' => 'autre'],
    ];

    public function up(): void
    {
        Schema::create('prospect_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        foreach ($this->defaultSources as $source) {
            DB::table('prospect_sources')->insert([
                'name' => $source['name'],
                'code' => $source['code'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_sources');
    }
};