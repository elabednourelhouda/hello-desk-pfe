<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accessory extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'description',
        'is_active',
    ];

    public function spaces()
    {
        return $this->belongsToMany(Space::class, 'accessory_space')
            ->withTimestamps();
    }
}