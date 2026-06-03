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
}