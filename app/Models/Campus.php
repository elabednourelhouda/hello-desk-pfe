<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    protected $fillable = [
        'name',
        'code',
        'city',
        'address',
        'description',
        'is_active',
    ];

    public function floors()
    {
        return $this->hasMany(Floor::class);
    }

    public function spaces()
    {
        return $this->hasMany(Space::class);
    }
}