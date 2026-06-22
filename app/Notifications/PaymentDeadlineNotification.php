<?php

namespace App\Notifications;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class PaymentDeadlineNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Payment $payment,
        public string $kind
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $clientName = $this->payment->client?->full_name ?? 'Client';
        $dueDate = $this->payment->due_date
            ? Carbon::parse($this->payment->due_date)->format('d/m/Y')
            : '-';

        $amount = number_format((float) $this->payment->amount_due, 2, ',', ' ');

        $isLate = $this->kind === 'late';

        return [
            'type' => $isLate ? 'payment_late' : 'payment_due_soon',
            'kind' => $this->kind,
            'title' => $isLate ? 'Paiement en retard' : 'Rappel de paiement',
            'message' => $isLate
                ? "Le paiement de {$clientName} est en retard depuis le {$dueDate}."
                : "Un paiement de {$clientName} arrive à échéance le {$dueDate}.",
            'payment_id' => $this->payment->id,
            'client_id' => $this->payment->client_id,
            'client_name' => $clientName,
            'amount_due' => $amount,
            'due_date' => $dueDate,
            'url' => $this->urlFor($notifiable),
        ];
    }

    private function urlFor(object $notifiable): string
    {
        $role = $notifiable->role ?? null;

        if ($role === 'admin' && Route::has('admin.payments.show')) {
            return route('admin.payments.show', $this->payment);
        }

        if ($role === 'commercial' && Route::has('commercial.payments.show')) {
            return route('commercial.payments.show', $this->payment);
        }

        if ($role === 'client' && Route::has('client.dashboard')) {
            return route('client.dashboard');
        }

        return url('/');
    }
}