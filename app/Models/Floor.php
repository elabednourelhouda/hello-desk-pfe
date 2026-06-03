<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    protected $fillable = [
        'campus_id',
        'name',
        'code',
        'map_key',
        'description',
    ];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function spaces()
    {
        return $this->hasMany(Space::class);
    }
}