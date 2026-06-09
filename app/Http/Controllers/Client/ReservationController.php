<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(): View
    {
        $client = $this->currentClient();

        $reservations = collect();

        if ($client) {
            $reservations = Reservation::with(['space', 'campus', 'floor', 'contract'])
                ->where('client_id', $client->id)
                ->latest('starts_at')
                ->paginate(10);
        }

        return view('client.reservations.index', compact('client', 'reservations'));
    }

    public function show(Reservation $reservation): View
    {
        $client = $this->currentClient();

        abort_if(!$client || $reservation->client_id !== $client->id, 403);

        $reservation->load([
            'space.spaceType',
            'space.campus',
            'space.floor',
            'campus',
            'floor',
            'contract',
        ]);

        return view('client.reservations.show', compact('client', 'reservation'));
    }

    private function currentClient(): ?Client
    {
        return Client::where('user_id', Auth::id())->first();
    }
}