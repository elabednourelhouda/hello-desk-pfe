<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'surface' => 'decimal:2',
        'price_per_hour' => 'decimal:2',
        'price_per_half_day' => 'decimal:2',
        'price_per_day' => 'decimal:2',
        'price_per_month' => 'decimal:2',
        'is_active' => 'boolean',
    ];

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
