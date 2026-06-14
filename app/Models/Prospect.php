<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProspectRequest;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prospect extends Model
{
    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'company_name',
        'registered_at',
        'need',
        'preferred_campus_id',
        'preferred_space_type_id',
        'people_count',
        'desired_start_date',
        'desired_rental_period',
        'budget',
        'source',
        'crm_status',
        'lost_reason',
        'notes',
        'assigned_to',
        'converted_client_id',
        'converted_at',
    ];

    protected $casts = [
        'registered_at' => 'date',
        'converted_at' => 'datetime',
        'desired_start_date' => 'date',
        'desired_rental_period' => 'string',
        'lost_reason' => 'string',
    ];
    public function preferredCampus()
    {
        return $this->belongsTo(Campus::class, 'preferred_campus_id');
    }

    public function preferredSpaceType()
    {
        return $this->belongsTo(SpaceType::class, 'preferred_space_type_id');
    }

    public function assignedCommercial()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedClient()
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }
    public function visits()
    {
        return $this->hasMany(ProspectVisit::class);
    }

    public function prospectRequests()
    {
        return $this->hasMany(ProspectRequest::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ProspectRequest::class);
    }
}