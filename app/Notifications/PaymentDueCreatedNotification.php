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
        return [
            'type' => 'payment_due_created',
            'title' => 'Nouvelle échéance de paiement',
            'message' => 'Une nouvelle échéance de paiement a été ajoutée à votre contrat.',
            'payment_id' => $this->payment->id,
            'contract_id' => $this->payment->contract_id,
            'reservation_id' => $this->payment->reservation_id,
            'amount_due' => $this->payment->amount_due,
            'due_date' => $this->payment->due_date?->format('d/m/Y'),
        ];
    }
}