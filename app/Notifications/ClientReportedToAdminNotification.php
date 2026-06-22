<?php

namespace App\Notifications;

use App\Models\Client;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClientReportedToAdminNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Client $client,
        public User $commercial,
        public string $reason,
        public string $requestType = 'fraud_suspicion'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'client_admin_action_requested',
            'request_type' => $this->requestType,
            'title' => 'Demande admin : ' . $this->requestTypeLabel(),
            'message' => $this->commercial->name . ' demande une action admin pour le client ' . $this->client->full_name . '.',
            'reason' => $this->reason,
            'client_id' => $this->client->id,
            'commercial_id' => $this->commercial->id,
            'url' => route('admin.clients.show', $this->client, false),
        ];
    }

    private function requestTypeLabel(): string
    {
        return match ($this->requestType) {
            'password_reset' => 'Réinitialisation du mot de passe client',
            'fraud_suspicion' => 'Risque de fraude / comportement suspect',
            'payment_block' => 'Blocage pour impayé',
            'reactivation' => 'Réactivation / déblocage',
            default => 'Autre demande admin',
        };
    }
}
