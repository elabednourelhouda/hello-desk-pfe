<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['client', 'space', 'contract'])
            ->latest()
            ->paginate(10);

        return view('admin.reservations.index', compact('reservations'));
    }

    public function create(Request $request)
    {
        $clients = Client::orderBy('full_name')->get();

        $spaces = Space::with(['campus', 'floor', 'spaceType'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedSpace = null;

        if ($request->filled('space_id')) {
            $selectedSpace = Space::with(['campus', 'floor', 'spaceType'])
                ->findOrFail($request->space_id);
        }

        return view('admin.reservations.create', compact(
            'clients',
            'spaces',
            'selectedSpace'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'space_id' => ['required', 'exists:spaces,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'duration_type' => ['required', 'in:hourly,daily,monthly,custom'],
            'negotiated_price' => ['nullable', 'numeric', 'min:0'],
            'contract_title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $space = Space::findOrFail($data['space_id']);

        $normalizedStatus = mb_strtolower($space->status ?? 'disponible');

        if (in_array($normalizedStatus, ['occupé', 'occupe', 'occupied', 'indisponible', 'unavailable', 'maintenance', 'en maintenance'])) {
            return back()
                ->withInput()
                ->with('error', 'Cet espace ne peut pas être réservé actuellement.');
        }

        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        $hasOverlap = Reservation::where('space_id', $space->id)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->where(function ($query) use ($startsAt, $endsAt) {
                $query->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt);
            })
            ->exists();

        if ($hasOverlap) {
            return back()
                ->withInput()
                ->with('error', 'Cet espace est déjà réservé sur cette période.');
        }

        $reservation = DB::transaction(function () use ($data, $space, $startsAt, $endsAt) {
            $reservation = Reservation::create([
                'client_id' => $data['client_id'],
                'space_id' => $space->id,
                'campus_id' => $space->campus_id,
                'floor_id' => $space->floor_id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'duration_type' => $data['duration_type'],
                'negotiated_price' => $data['negotiated_price'] ?? 0,
                'status' => 'pending',
                'created_by' => Auth::id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $client = Client::findOrFail($data['client_id']);

            Contract::create([
                'client_id' => $client->id,
                'reservation_id' => $reservation->id,
                'title' => $data['contract_title'] ?: 'Contrat - ' . $client->full_name,
                'start_date' => $startsAt->toDateString(),
                'end_date' => $endsAt->toDateString(),
                'status' => 'draft',
                'uploaded_by' => Auth::id(),
            ]);

            return $reservation;
        });

        return redirect()
            ->route('admin.reservations.show', $reservation)
            ->with('success', 'Réservation créée avec succès. Un contrat brouillon a été créé automatiquement.');
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['client', 'space', 'campus', 'floor', 'contract', 'creator']);

        return view('admin.reservations.show', compact('reservation'));
    }
}