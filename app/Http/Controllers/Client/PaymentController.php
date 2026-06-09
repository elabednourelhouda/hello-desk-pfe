<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $client = $this->currentClient();

        $payments = collect();

        if ($client) {
            $payments = Payment::with(['contract.reservation.space'])
                ->where('client_id', $client->id)
                ->orderBy('due_date')
                ->paginate(10);
        }

        return view('client.payments.index', compact('client', 'payments'));
    }

    public function show(Payment $payment): View
    {
        $client = $this->currentClient();

        abort_if(!$client || $payment->client_id !== $client->id, 403);

        $payment->load(['contract.reservation.space', 'reservation']);

        return view('client.payments.show', compact('client', 'payment'));
    }

    private function currentClient(): ?Client
    {
        return Client::where('user_id', Auth::id())->first();
    }
}