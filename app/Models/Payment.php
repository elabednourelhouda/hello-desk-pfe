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
        'duration_label',

        'amount_ht',
        'tax_rate',
        'tax_amount',
        'amount_ttc',

        'amount_due',
        'amount_paid',

        'paid_at',
        'status',
        'payment_method',
        'reference',

        'cheque_number',
        'cheque_bank',
        'cheque_date',

        'bank_transfer_reference',
        'bank_name',

        'tpe_transaction_reference',

        'receipt_number',
        'receipt_file',

        'invoice_number',
        'invoice_file',

        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'cheque_date' => 'date',

        'amount_ht' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'amount_ttc' => 'decimal:2',

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

    public function getPaymentMethodLabelAttribute(): string
    {
        return [
            'cash' => 'Espèces',
            'cheque' => 'Chèque',
            'bank_transfer' => 'Virement bancaire',
            'tpe' => 'TPE',
            'other' => 'Autre',
        ][$this->payment_method] ?? 'Non précisé';
    }

    public function getAmountTtcValueAttribute(): float
    {
        return (float) ($this->amount_ttc ?? $this->amount_due ?? 0);
    }
}