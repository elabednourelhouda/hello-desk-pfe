<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'reservation_id',
        'contract_id',
        'subject',
        'type',
        'description',
        'status',
        'priority',
        'admin_response',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'Nouvelle',
            'in_progress' => 'En cours',
            'waiting' => 'En attente',
            'resolved' => 'Résolue',
            'closed' => 'Fermée',
            'rejected' => 'Rejetée',
            default => 'Inconnu',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'material_issue' => 'Problème matériel',
            'internet_issue' => 'Problème internet',
            'air_conditioning' => 'Climatisation',
            'equipment_request' => 'Demande d’équipement',
            'reservation_issue' => 'Problème de réservation',
            'other' => 'Autre',
            default => 'Autre',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Faible',
            'normal' => 'Normale',
            'high' => 'Élevée',
            'urgent' => 'Urgente',
            default => 'Normale',
        };
    }
}