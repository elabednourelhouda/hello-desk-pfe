<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Task 7/8: rather than creating a brand new "CRM follow-up" table,
     * we extend the existing prospect_visits table (the "Ajouter un
     * suivi" archive), since a "suivi" entry already represents exactly
     * one contact event. This column stores the date the commercial
     * picks for the *next* contact when logging the current one —
     * i.e. "Date relance" / "Date prochaine relance" from the spec.
     *
     * The automatic Green/Yellow/Red indicator (Task 8) is computed
     * on the fly from the most recent visit's next_followup_at — it is
     * never stored, per the spec's explicit instruction.
     */
    public function up(): void
    {
        Schema::table('prospect_visits', function (Blueprint $table) {
            $table->date('next_followup_at')->nullable()->after('visit_time');
        });
    }

    public function down(): void
    {
        Schema::table('prospect_visits', function (Blueprint $table) {
            $table->dropColumn('next_followup_at');
        });
    }
};