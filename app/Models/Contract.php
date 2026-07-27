<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'client_id',
        'reservation_id',
        'title',
        'start_date',
        'end_date',
        'status',
        'pdf_path',
        'uploaded_by',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Aggregates this contract's monthly installments (Payment rows) into
     * a single summary: how many are paid/late, the running totals, an
     * overall status badge, and the next installment the client/staff
     * should be looking at. Used to show "one row per contract" in the
     * payments index pages instead of "one row per installment", and to
     * drive the client-facing "past payment / current payment" alert.
     *
     * Overall status values: 'none' (no installments yet), 'paid' (every
     * installment settled), 'late' (at least one overdue/unpaid-past-due
     * installment), 'due' (in progress, nothing overdue).
     */
    public function paymentSummary(): array
    {
        $payments = $this->relationLoaded('payments')
            ? $this->payments
            : $this->payments()->orderBy('due_date')->get();

        $count = $payments->count();

        $paidCount = $payments->where('status', 'paid')->count();

        $lateCount = $payments->filter(function (Payment $payment) {
            return $payment->status === 'late'
                || ($payment->status === 'due' && $payment->due_date && $payment->due_date->lt(today()));
        })->count();

        $totalTtc = (float) $payments->sum(
            fn (Payment $payment) => (float) ($payment->amount_ttc ?? $payment->amount_due ?? 0)
        );

        $totalPaid = (float) $payments->sum(fn (Payment $payment) => (float) ($payment->amount_paid ?? 0));

        $remaining = max($totalTtc - $totalPaid, 0);

        if ($count === 0) {
            $overallStatus = 'none';
        } elseif ($lateCount > 0) {
            $overallStatus = 'late';
        } elseif ($paidCount === $count) {
            $overallStatus = 'paid';
        } else {
            $overallStatus = 'due';
        }

        // The installment the client/staff currently care about: the
        // earliest one that isn't settled yet (paid/cancelled). If every
        // installment is settled, there is no "current" one to flag.
        $currentPayment = $payments
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sortBy('due_date')
            ->first();

        return [
            'installments_count' => $count,
            'paid_installments_count' => $paidCount,
            'late_installments_count' => $lateCount,
            'total_ttc' => $totalTtc,
            'total_paid' => $totalPaid,
            'remaining' => $remaining,
            'overall_status' => $overallStatus,
            'current_payment' => $currentPayment,
        ];
    }
}