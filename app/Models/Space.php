<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    protected $fillable = [
        'campus_id',
        'floor_id',
        'space_type_id',
        'name',
        'code',
        'capacity',
        'surface',
        'price_per_hour',
        'price_per_half_day',
        'price_per_day',
        'price_per_month',
        'status',
        'description',
        'notes',
        'is_active',
        'grid_column',
        'grid_row',
        'grid_width',
        'grid_height',
    ];

    protected $casts = [
        'surface' => 'decimal:2',
        'price_per_hour' => 'decimal:2',
        'price_per_half_day' => 'decimal:2',
        'price_per_day' => 'decimal:2',
        'price_per_month' => 'decimal:2',
        'is_active' => 'boolean',
        'grid_column' => 'integer',
        'grid_row' => 'integer',
        'grid_width' => 'integer',
        'grid_height' => 'integer',
    ];

    /**
     * Apply temporary grid coordinates to spaces without a valid saved
     * layout. Existing valid, non-overlapping coordinates are preserved and
     * no values are persisted by this method.
     */
    public static function withTemporaryGridLayout(Collection $spaces): Collection
    {
        $occupied = [];
        $layoutSpaces = collect();
        $unplacedSpaces = collect();

        foreach ($spaces as $space) {
            $savedColumn = (int) $space->grid_column;
            $savedRow = (int) $space->grid_row;
            $savedWidth = (int) $space->grid_width;
            $savedHeight = (int) $space->grid_height;

            $hasValidSize = $savedWidth >= 1 && $savedWidth <= 9
                && $savedHeight >= 1 && $savedHeight <= 5;
            $width = $hasValidSize ? $savedWidth : 1;
            $height = $hasValidSize ? $savedHeight : 1;
            $hasValidPosition = $savedColumn >= 1 && $savedColumn <= 9
                && $savedRow >= 1 && $savedRow <= 5
                && $savedColumn + $width - 1 <= 9
                && $savedRow + $height - 1 <= 5;

            if (! $hasValidPosition) {
                $unplacedSpaces->push($space);
                continue;
            }

            self::markGridCells($occupied, $savedColumn, $savedRow, $width, $height);
            $layoutSpaces->push(self::withGridValues($space, $savedColumn, $savedRow, $width, $height));
        }

        foreach ($unplacedSpaces as $space) {
            $width = (int) $space->grid_width;
            $height = (int) $space->grid_height;
            $width = $width >= 1 && $width <= 9 ? $width : 1;
            $height = $height >= 1 && $height <= 5 ? $height : 1;
            $positionFound = false;

            for ($row = 1; $row <= 5 && ! $positionFound; $row++) {
                for ($column = 1; $column <= 9; $column++) {
                    if ($column + $width - 1 > 9 || $row + $height - 1 > 5) {
                        continue;
                    }

                    $fits = true;
                    for ($candidateRow = $row; $candidateRow < $row + $height; $candidateRow++) {
                        for ($candidateColumn = $column; $candidateColumn < $column + $width; $candidateColumn++) {
                            if (isset($occupied[$candidateRow][$candidateColumn])) {
                                $fits = false;
                                break 2;
                            }
                        }
                    }

                    if ($fits) {
                        self::markGridCells($occupied, $column, $row, $width, $height);
                        $layoutSpaces->push(self::withGridValues($space, $column, $row, $width, $height));
                        $positionFound = true;
                        break;
                    }
                }
            }
        }

        return $layoutSpaces;
    }

    private static function markGridCells(array &$occupied, int $column, int $row, int $width, int $height): void
    {
        for ($currentRow = $row; $currentRow < $row + $height; $currentRow++) {
            for ($currentColumn = $column; $currentColumn < $column + $width; $currentColumn++) {
                $occupied[$currentRow][$currentColumn] = true;
            }
        }
    }

    private static function withGridValues(self $space, int $column, int $row, int $width, int $height): self
    {
        $space->grid_column = $column;
        $space->grid_row = $row;
        $space->grid_width = $width;
        $space->grid_height = $height;

        return $space;
    }

    /**
     * Which `reservations.duration_type` values are bookable for this
     * space, based on which ReservationDurationType rows are linked to
     * its space type via the `space_type_duration_type` pivot —
     * configurable from Configuration -> Types de durée de réservation
     * instead of hardcoded here.
     *
     * A space type with NO rows in that pivot (an admin added a new
     * space type from Configuration -> Types d'espaces and hasn't
     * restricted its durations yet, or hasn't been seeded at all) falls
     * back to every active duration type — this mirrors the original
     * hardcoded `default => [...]` arm: a newly created dropdown value
     * should never silently block bookings, it just isn't restricted
     * yet.
     */
    public function bookableDurationTypes(): array
    {
        if ($this->spaceType) {
            $linked = $this->spaceType->durationTypes()
                ->where('reservation_duration_types.is_active', true)
                ->orderBy('sort_order')
                ->pluck('code')
                ->all();

            if (! empty($linked)) {
                return $linked;
            }
        }

        return ReservationDurationType::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->all();
    }

    /**
     * Which `reservations.engagement_duration_unit` values are bookable
     * for this space. Mirrors bookableDurationTypes() at a finer grain
     * (adds/removes the half_day option).
     *
     * Same code-vs-name caveat as bookableDurationTypes() above applies:
     * matched against `code = 'salle-reunion'`, not the display name
     * "Salle de formation".
     */
    public function bookableEngagementUnits(): array
    {
        return match ($this->spaceType?->code) {
            'salle-reunion' => ['hour', 'half_day', 'day'],
            'bureau', 'co-working' => ['day', 'month'],
            default => ['hour', 'half_day', 'day', 'month'],
        };
    }

    /**
     * The rate to use for a given engagement unit, falling back sensibly
     * so the reservation forms always have something to prefill with.
     */
    public function priceForUnit(string $unit): ?string
    {
        return match ($unit) {
            'hour' => $this->price_per_hour,
            'half_day' => $this->price_per_half_day ?? ($this->price_per_day ? round($this->price_per_day / 2, 2) : null),
            'day' => $this->price_per_day,
            'month' => $this->price_per_month,
            default => null,
        };
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function spaceType()
    {
        return $this->belongsTo(SpaceType::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Was already referenced by Admin\SpaceController::map() via
     * Space::with([..., 'accessories']) but was never actually defined
     * here — this would have thrown "Call to undefined relationship"
     * the first time that map view ran with real data.
     */
    public function accessories()
    {
        return $this->belongsToMany(Accessory::class, 'accessory_space');
    }

    /**
     * The reservation currently occupying this space right now, if any.
     * Used to show "who's booking it" at a glance in the admin space view.
     */
    public function currentReservation()
    {
        return $this->hasOne(Reservation::class)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->latest('starts_at');
    }

    /**
     * The next upcoming reservation for this space, used when no one is
     * currently in it.
     */
    public function nextReservation()
    {
        return $this->hasOne(Reservation::class)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('starts_at', '>', now())
            ->oldest('starts_at');
    }
}