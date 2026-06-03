<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'client_id',
        'contract_id',
        'reservation_id',
        'due_date',
        'amount_due',
        'amount_paid',
        'paid_at',
        'status',
        'payment_method',
        'reference',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getRealStatusAttribute(): string
    {
        if ($this->status === 'paid') {
            return 'Payé';
        }

        if ($this->status === 'cancelled') {
            return 'Annulé';
        }

        if ($this->due_date && $this->due_date->lt(today())) {
            return 'En retard';
        }

        return 'À payer';
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount_due - (float) $this->amount_paid);
    }
}