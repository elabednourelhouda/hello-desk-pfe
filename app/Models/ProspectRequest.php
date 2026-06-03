<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectRequest extends Model
{
    protected $fillable = [
        'prospect_id',
        'created_by',
        'request_date',
        'request_type',
        'campus_id',
        'space_type_id',
        'desired_start_date',
        'duration_type',
        'budget',
        'status',
        'description',
        'response_notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'desired_start_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function spaceType(): BelongsTo
    {
        return $this->belongsTo(SpaceType::class);
    }
}