<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivitySector extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function prospects()
    {
        return $this->hasMany(Prospect::class, 'activity_sector_id');
    }
}