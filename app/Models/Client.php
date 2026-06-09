<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'prospect_id',
        'full_name',
        'email',
        'phone',
        'company_name',
        'main_campus_id',
        'status',
        'billing_info',
        'registered_at',
        'joined_at',
        'notes',
    ];

    protected $casts = [
        'joined_at' => 'date',
        'registered_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function mainCampus()
    {
        return $this->belongsTo(Campus::class, 'main_campus_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}