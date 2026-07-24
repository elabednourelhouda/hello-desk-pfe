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
        'origin',
        'customer_type',
        'activity_sector_id',
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
    public function activitySector()
    {
        return $this->belongsTo(ActivitySector::class);
    }
    public function visits()
    {
        return $this->hasMany(ProspectVisit::class);
    }

    /**
     * The single most recent follow-up entry, used to read the next
     * planned contact date. Eager-loadable with ->with('latestVisit')
     * so Task 5's prospect list doesn't run N extra queries per row.
     */
    public function latestVisit()
    {
        return $this->hasOne(ProspectVisit::class)->latestOfMany('visit_date');
    }

    /**
     * Task 8: the CRM follow-up indicator. Intentionally NOT a database
     * column — always computed from the latest visit's next_followup_at,
     * per the spec ("do not store Green/Yellow/Red, compute it dynamically").
     *
     * Returns null for prospects that no longer need following up
     * (converted or lost), so the badge can simply be omitted for them.
     */
    public function getCrmFollowupColorAttribute(): ?string
    {
        if (in_array($this->crm_status, ['converted', 'lost'], true)) {
            return null;
        }

        $nextFollowupAt = $this->latestVisit?->next_followup_at;

        // No follow-up has ever been scheduled for this prospect yet —
        // treated as needing attention, same as an overdue one.
        if (! $nextFollowupAt) {
            return 'red';
        }

        $today = now()->startOfDay();
        $next = \Carbon\Carbon::parse($nextFollowupAt)->startOfDay();

        if ($next->lt($today)) {
            return 'red';
        }

        if ($today->diffInDays($next) <= 7) {
            return 'yellow';
        }

        return 'green';
    }

    /**
     * Human-readable label matching the color, for tooltips/screen readers.
     */
    public function getCrmFollowupLabelAttribute(): ?string
    {
        return match ($this->crm_followup_color) {
            'green' => 'Aucune action requise',
            'yellow' => 'Relance à venir sous 7 jours',
            'red' => 'Relance en retard',
            default => null,
        };
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