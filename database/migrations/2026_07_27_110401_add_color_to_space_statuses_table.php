<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Colors previously hardcoded per-status in every map blade file
     * (admin & commercial index.blade.php + centre_ville_2.blade.php).
     * Backfilling the 4 existing rows with these exact values keeps the
     * map's appearance identical for anyone upgrading — only *new*
     * statuses created after this migration need a color picked in
     * Configuration -> Statuts d'espace.
     *
     * @var array<string, string>
     */
    private array $defaultColors = [
        'available' => '#16a34a',   // green-600  (was: emerald/green across the map views)
        'occupied' => '#64748b',    // slate-500  (was: slate/gray across the map views)
        'unavailable' => '#dc2626', // red-600    (was: rose/red across the map views)
        'maintenance' => '#d97706', // amber-600  (was: orange/yellow across the map views)
    ];

    public function up(): void
    {
        Schema::table('space_statuses', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('code');
        });

        foreach ($this->defaultColors as $code => $color) {
            DB::table('space_statuses')->where('code', $code)->update(['color' => $color]);
        }

        // Any status created between the 2026_07_25 seed migration and this
        // one (i.e. an admin already added a custom status before this
        // upgrade ran) won't be in $defaultColors above and would be left
        // with color = null. Give it a neutral gray rather than leaving it
        // null, since the map/model always expects a color to render with.
        DB::table('space_statuses')
            ->whereNull('color')
            ->update(['color' => '#6b7280']); // gray-500
    }

    public function down(): void
    {
        Schema::table('space_statuses', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
