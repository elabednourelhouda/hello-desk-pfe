<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    public function spaces()
    {
        return $this->hasMany(Space::class);
    }

    public function durationTypes()
    {
        return $this->belongsToMany(ReservationDurationType::class, 'space_type_duration_type');
    }
}