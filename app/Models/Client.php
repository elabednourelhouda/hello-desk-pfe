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

        'client_type',

        'first_name',
        'last_name',
        'identity_document_type',
        'identity_document_number',
        'nationality',

        'billing_email',
        'address',
        'city',
        'country',

        'legal_form',
        'ice_number',
        'if_number',
        'rc_number',
        'patente_number',
        'cnss_number',
        'headquarters_address',

        'legal_representative_full_name',
        'legal_representative_identity_document_type',
        'legal_representative_identity_document_number',
        'legal_representative_phone',
        'legal_representative_email',

        'legal_file_status',
        'legal_file_completed_at',
        'legal_file_notes',
    ];

    protected $casts = [
        'joined_at' => 'date',
        'registered_at' => 'date',
        'legal_file_completed_at' => 'datetime',
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

    public function hasCompleteLegalFile(): bool
    {
        if ($this->client_type === 'physique') {
            return filled($this->first_name)
                && filled($this->last_name)
                && filled($this->identity_document_type)
                && filled($this->identity_document_number);
        }

        if ($this->client_type === 'morale') {
            return filled($this->company_name)
                && filled($this->legal_form)
                && filled($this->ice_number)
                && filled($this->legal_representative_full_name)
                && filled($this->legal_representative_identity_document_type)
                && filled($this->legal_representative_identity_document_number);
        }

        return false;
    }

    public function legalFileStatusLabel(): string
    {
        return $this->hasCompleteLegalFile()
            ? 'Complete'
            : 'Incomplete';
    }
}