<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProspectVisit extends Model
{
    protected $fillable = [
        'prospect_id',
        'commercial_id',
        'created_by',
        'campus_id',
        'space_type_id',
        'visit_date',
        'visit_time',
        'next_followup_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'next_followup_at' => 'date',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function commercial()
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function spaceType()
    {
        return $this->belongsTo(SpaceType::class);
    }
}