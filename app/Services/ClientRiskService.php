<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Payment;
use Illuminate\Support\Str;

class ClientRiskService
{
    public function apply(Client $client): Client
    {
        $result = $this->check($client);

        $client->update([
            'risk_status' => $result['status'],
            'risk_reason' => $result['reason'],
            'risk_checked_at' => now(),
        ]);

        return $client->fresh();
    }

    public function check(Client $client): array
    {
        $cin = $this->normalize($client->identity_document_number);
        $representativeCin = $this->normalize($client->legal_representative_identity_document_number);
        $ice = $this->normalize($client->ice_number);

        if (! $cin && ! $representativeCin && ! $ice) {
            return [
                'status' => 'clear',
                'reason' => null,
            ];
        }

        $matchedClients = Client::query()
            ->where('id', '!=', $client->id)
            ->where(function ($query) use ($cin, $representativeCin, $ice) {
                if ($cin) {
                    $query
                        ->orWhereRaw("regexp_replace(upper(coalesce(identity_document_number, '')), '[^A-Z0-9]', '', 'g') = ?", [$cin])
                        ->orWhereRaw("regexp_replace(upper(coalesce(legal_representative_identity_document_number, '')), '[^A-Z0-9]', '', 'g') = ?", [$cin]);
                }

                if ($representativeCin) {
                    $query
                        ->orWhereRaw("regexp_replace(upper(coalesce(identity_document_number, '')), '[^A-Z0-9]', '', 'g') = ?", [$representativeCin])
                        ->orWhereRaw("regexp_replace(upper(coalesce(legal_representative_identity_document_number, '')), '[^A-Z0-9]', '', 'g') = ?", [$representativeCin]);
                }

                if ($ice) {
                    $query
                        ->orWhereRaw("regexp_replace(upper(coalesce(ice_number, '')), '[^A-Z0-9]', '', 'g') = ?", [$ice]);
                }
            })
            ->get();

        if ($matchedClients->isEmpty()) {
            return [
                'status' => 'clear',
                'reason' => null,
            ];
        }

        $riskyClient = $matchedClients->first(function (Client $matchedClient) {
            return Payment::query()
                ->where('client_id', $matchedClient->id)
                ->whereIn('status', ['due', 'late'])
                ->exists();
        });

        if ($riskyClient) {
            return [
                'status' => 'blocked',
                'reason' => "Identité légale déjà utilisée par un autre client avec des paiements impayés ou en retard : {$riskyClient->full_name}.",
            ];
        }

        return [
            'status' => 'watchlist',
            'reason' => 'Identité légale déjà utilisée par un autre client. Vérification manuelle recommandée avant réservation.',
        ];
    }

    private function normalize(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $normalized = preg_replace('/[^A-Z0-9]/', '', Str::upper($value));

        return $normalized ?: null;
    }
}