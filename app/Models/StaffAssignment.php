<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAssignment extends Model
{
    protected $fillable = [
        'commercial_id',
        'campus_id',
        'floor_id',
        'assigned_by',
        'notes',
    ];

    public function commercial()
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}