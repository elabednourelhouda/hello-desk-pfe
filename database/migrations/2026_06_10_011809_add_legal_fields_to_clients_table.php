<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('client_type')->nullable(); // physique / morale

            // Personne physique
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('identity_document_type')->nullable(); // CIN / Passport / Carte séjour
            $table->string('identity_document_number')->nullable();
            $table->string('nationality')->nullable();

            // Coordonnées / facturation
            $table->string('billing_email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable()->default('Maroc');

            // Personne morale
            $table->string('legal_form')->nullable(); // SARL / SA / SNC / etc.
            $table->string('ice_number')->nullable();
            $table->string('if_number')->nullable();
            $table->string('rc_number')->nullable();
            $table->string('patente_number')->nullable();
            $table->string('cnss_number')->nullable();
            $table->string('headquarters_address')->nullable();

            // Représentant légal
            $table->string('legal_representative_full_name')->nullable();
            $table->string('legal_representative_identity_document_type')->nullable();
            $table->string('legal_representative_identity_document_number')->nullable();
            $table->string('legal_representative_phone')->nullable();
            $table->string('legal_representative_email')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'client_type',
                'first_name',
                'last_name',
                'identity_document_type',
                'identity_document_number',
                'nationality',
                'billing_email',
                'address',
                'city',
                'country',
                'legal_form',
                'ice_number',
                'if_number',
                'rc_number',
                'patente_number',
                'cnss_number',
                'headquarters_address',
                'legal_representative_full_name',
                'legal_representative_identity_document_type',
                'legal_representative_identity_document_number',
                'legal_representative_phone',
                'legal_representative_email',
            ]);
        });
    }
};