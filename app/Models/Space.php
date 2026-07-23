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
        'price_per_day' => 'decimal:2',
        'price_per_month' => 'decimal:2',
        'is_active' => 'boolean',
    ];

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