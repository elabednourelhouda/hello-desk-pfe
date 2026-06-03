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
}