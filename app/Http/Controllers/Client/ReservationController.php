<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $client = $this->currentClient();

        $reservations = collect();

        $stats = [
            'total' => 0,
            'pending' => 0,
            'confirmed' => 0,
            'active' => 0,
        ];

        if ($client) {
            $baseQuery = Reservation::where('client_id', $client->id);

            $stats = [
                'total' => (clone $baseQuery)->count(),
                'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
                'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
                'active' => (clone $baseQuery)->whereIn('status', ['active', 'in_progress'])->count(),
            ];

            $reservations = Reservation::with([
                    'space.spaceType',
                    'campus',
                    'floor',
                    'contract',
                ])
                ->where('client_id', $client->id)
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = $request->search;

                    $query->where(function ($q) use ($search) {
                        $q->whereHas('space', function ($spaceQuery) use ($search) {
                            $spaceQuery->where('name', 'ilike', "%{$search}%")
    ->orWhere('code', 'ilike', "%{$search}%");
                        })
                        ->orWhereHas('campus', function ($campusQuery) use ($search) {
                            $campusQuery->where('name', 'ilike', "%{$search}%");
                        })
                        ->orWhereHas('floor', function ($floorQuery) use ($search) {
                            $floorQuery->where('name', 'ilike', "%{$search}%");
                        })
                        ->orWhereHas('contract', function ($contractQuery) use ($search) {
                            $contractQuery->where('title', 'ilike', "%{$search}%");
                        });
                    });
                })
                ->when($request->filled('status') && $request->status !== 'all', function ($query) use ($request) {
                    if ($request->status === 'active') {
                        $query->whereIn('status', ['active', 'in_progress']);
                    } else {
                        $query->where('status', $request->status);
                    }
                })
                ->latest('starts_at')
                ->paginate(10)
                ->withQueryString();
        }

        return view('client.reservations.index', compact('client', 'reservations', 'stats'));
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