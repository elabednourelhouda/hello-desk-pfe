<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Until now, "Type de contact" was never actually stored as its own
     * column: both ProspectVisitController@store methods validated it,
     * resolved it to a French label, and concatenated that label
     * straight into the free-text `notes` column:
     *
     *   "Type de contact : {label}\nRésumé : {summary}"
     *
     * This migration gives it a real column, and does a best-effort
     * backfill for existing rows: it parses that old line, maps the
     * label back to a contact_types.code, and — only when a match is
     * found — strips that line (and the "Résumé : " label) out of
     * `notes`, so the summary isn't duplicated once the UI starts
     * rendering contact_type as its own field. Rows that don't match
     * this exact historical format are left untouched (contact_type
     * stays null, notes stays as-is); the UI falls back gracefully.
     */
    public function up(): void
    {
        Schema::table('prospect_visits', function (Blueprint $table) {
            $table->string('contact_type')->nullable()->after('visit_time');
        });

        $labelToCode = DB::table('contact_types')->pluck('code', 'name');

        DB::table('prospect_visits')
            ->whereNull('contact_type')
            ->whereNotNull('notes')
            ->orderBy('id')
            ->chunkById(100, function ($visits) use ($labelToCode) {
                foreach ($visits as $visit) {
                    if (! preg_match('/^Type de contact\s*:\s*(.+?)\R(.*)$/su', $visit->notes, $matches)) {
                        continue;
                    }

                    $label = trim($matches[1]);
                    $code = $labelToCode[$label] ?? null;

                    if (! $code) {
                        continue;
                    }

                    $remainder = preg_replace('/^Résumé\s*:\s*/u', '', trim($matches[2]));

                    DB::table('prospect_visits')
                        ->where('id', $visit->id)
                        ->update([
                            'contact_type' => $code,
                            'notes' => $remainder !== '' ? $remainder : null,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('prospect_visits', function (Blueprint $table) {
            $table->dropColumn('contact_type');
        });
    }
};