<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $client = $this->currentClient();

        $contracts = collect();

        if ($client) {
            $contracts = Contract::with(['reservation.space', 'payments'])
                ->where('client_id', $client->id)
                ->whereHas('payments')
                ->latest('id')
                ->get()
                ->map(function (Contract $contract) {
                    return array_merge(['contract' => $contract], $contract->paymentSummary());
                });
        }

        return view('client.payments.index', compact('client', 'contracts'));
    }

    /**
     * The month-by-month history for one of the client's contracts —
     * status and receipt only, no HT/TVA accounting breakdown. The
     * index() alert already tells them what's due right now; this is
     * just "what have I already paid".
     */
    public function schedule(Contract $contract): View
    {
        $client = $this->currentClient();

        abort_if(!$client || $contract->client_id !== $client->id, 403);

        $contract->load([
            'reservation.space',
            'payments' => fn ($query) => $query->orderBy('due_date'),
        ]);

        $summary = $contract->paymentSummary();

        return view('client.payments.schedule', compact('client', 'contract', 'summary'));
    }

    public function show(Payment $payment): View
    {
        $client = $this->currentClient();

        abort_if(!$client || $payment->client_id !== $client->id, 403);

        $payment->load(['contract.reservation.space', 'reservation']);

        return view('client.payments.show', compact('client', 'payment'));
    }

    /**
     * Printable receipt for a settled payment. See
     * Admin\PaymentController::receipt() for why this is a print-styled
     * Blade view rather than a generated PDF binary.
     */
    public function receipt(Payment $payment): View|\Illuminate\Http\RedirectResponse
    {
        $client = $this->currentClient();

        abort_if(!$client || $payment->client_id !== $client->id, 403);

        if ($payment->status !== 'paid') {
            return back()->with('error', 'Le reçu n’est disponible qu’une fois l’échéance payée.');
        }

        $payment->load(['contract.reservation.space', 'reservation']);

        return view('client.payments.receipt', compact('client', 'payment'));
    }

    private function currentClient(): ?Client
    {
        return Client::where('user_id', Auth::id())->first();
    }
}