<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the half-day (demi-journée) rate to spaces.
     *
     * Business rule: a "Salle de formation" booked for the morning only
     * must still be bookable by another client for the afternoon, at a
     * reduced rate. This column stores that rate explicitly (rather than
     * always deriving it as price_per_day / 2 on the fly) so an admin can
     * override it per space when needed, while still defaulting to 50%
     * for every existing space so no manual data entry is required.
     */
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->decimal('price_per_half_day', 10, 2)
                ->nullable()
                ->after('price_per_hour');
        });

        // Backfill existing rows: default every space's half-day price to
        // 50% of its current daily price, wherever a daily price exists.
        DB::table('spaces')
            ->whereNotNull('price_per_day')
            ->update([
                'price_per_half_day' => DB::raw('ROUND(price_per_day / 2, 2)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('price_per_half_day');
        });
    }
};
