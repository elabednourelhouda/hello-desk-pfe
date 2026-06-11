<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentDueCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $role = $notifiable->role ?? 'client';

        $routeName = match ($role) {
            'admin' => 'admin.payments.show',
            'commercial' => 'commercial.payments.show',
            'client' => 'client.payments.show',
            default => null,
        };

        return [
            'title' => 'Nouvelle échéance de paiement',
            'message' => 'Une nouvelle échéance de paiement a été ajoutée. Montant : '
                . number_format((float) $this->payment->amount_due, 2, ',', ' ')
                . ' DH. Date limite : '
                . optional($this->payment->due_date)->format('d/m/Y')
                . '.',
            'type' => 'warning',
            'url' => $routeName ? route($routeName, $this->payment) : null,

            'payment_id' => $this->payment->id,
            'contract_id' => $this->payment->contract_id,
            'reservation_id' => $this->payment->reservation_id,
            'amount_due' => $this->payment->amount_due,
            'due_date' => optional($this->payment->due_date)->format('d/m/Y'),
        ];
    }
}