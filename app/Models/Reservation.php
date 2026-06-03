<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'client_id',
        'space_id',
        'campus_id',
        'floor_id',
        'starts_at',
        'ends_at',
        'duration_type',
        'negotiated_price',
        'status',
        'created_by',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'negotiated_price' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }
}