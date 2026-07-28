<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationDurationType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Space types this duration type is bookable on. A space type with
     * no rows at all here is treated as "unrestricted" — see
     * Space::bookableDurationTypes() — so this pivot only needs to hold
     * explicit *inclusions*, matching how the original hardcoded
     * match() arms worked (three known types were restricted, every
     * other/future type allowed everything).
     */
    public function spaceTypes(): BelongsToMany
    {
        return $this->belongsToMany(SpaceType::class, 'space_type_duration_type');
    }

    /**
     * Every reservation booked with this duration type. Used to block
     * deletion (see ReservationDurationTypeController::destroy()) the
     * same way SpaceStatus/SpaceType guard against orphaning existing
     * records.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'duration_type', 'code');
    }

    /**
     * Prospects whose desired_rental_period uses this duration type's
     * code. desired_rental_period stores the same code vocabulary
     * (hourly/daily/monthly/custom) as reservations.duration_type —
     * see Commercial\ProspectController — so this table is the single
     * source of truth for both.
     */
    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class, 'desired_rental_period', 'code');
    }
}
