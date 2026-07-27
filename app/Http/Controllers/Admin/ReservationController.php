<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ClientRiskService;
use App\Models\Contract;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $reservationStatuses = $this->reservationStatuses();

        $contractFilters = [
            'all' => 'All contracts',
            'created' => 'With contract',
            'missing' => 'Without contract',
        ];

        $query = Reservation::query()
            ->with(['client', 'space', 'contract'])
            ->latest('starts_at');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('client', function ($clientQuery) use ($search) {
                    $clientQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
                    ->orWhereHas('space', function ($spaceQuery) use ($search) {
                        $spaceQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('contract', function ($contractQuery) use ($search) {
                        $contractQuery->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('contract') && $request->contract !== 'all') {
            if ($request->contract === 'created') {
                $query->whereHas('contract');
            }

            if ($request->contract === 'missing') {
                $query->whereDoesntHave('contract');
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('starts_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('starts_at', '<=', $request->to);
        }

        $reservations = $query->paginate(10)->withQueryString();

        return view('admin.reservations.index', compact(
            'reservations',
            'reservationStatuses',
            'contractFilters'
        ));
    }

    public function create(Request $request)
    {
        $durationTypes = $this->durationTypes();

        $clients = Client::where(function ($query) {
            $query->whereNull('risk_status')
                ->orWhere('risk_status', 'clear');
        })
            ->orderBy('full_name')
            ->get()
            ->sortByDesc(fn($client) => $client->hasCompleteLegalFile())
            ->values();

        $spaces = Space::with(['campus', 'floor', 'spaceType'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedSpace = null;

        if ($request->filled('space_id')) {
            $selectedSpace = Space::with(['campus', 'floor', 'spaceType'])
                ->where('is_active', true)
                ->findOrFail($request->space_id);

            $selectedSpace->display_price_per_hour = $selectedSpace->price_per_hour ?? 0;
            $selectedSpace->display_price_per_half_day = $selectedSpace->priceForUnit('half_day') ?? 0;
            $selectedSpace->display_price_per_day = $selectedSpace->price_per_day ?? 0;
            $selectedSpace->display_price_per_month = $selectedSpace->price_per_month ?? 0;
        }

        // Per-space booking rules, keyed by space id, so the form's JS
        // can filter the "Type de durée" / "Unité" selects and prefill
        // the negotiated price the moment a space is chosen — without a
        // round trip to the server.
        $spaceBookingRules = $spaces->mapWithKeys(fn (Space $space) => [
            $space->id => [
                'durationTypes' => $space->bookableDurationTypes(),
                'engagementUnits' => $space->bookableEngagementUnits(),
                'prices' => [
                    'hour' => $space->price_per_hour,
                    'half_day' => $space->priceForUnit('half_day'),
                    'day' => $space->price_per_day,
                    'month' => $space->price_per_month,
                ],
            ],
        ]);

        return view('admin.reservations.create', compact(
            'clients',
            'spaces',
            'selectedSpace',
            'durationTypes',
            'spaceBookingRules'
        ));
    }

    public function store(Request $request)
    {
        // The space determines which duration_type / engagement_duration_unit
        // values are actually bookable (e.g. a Bureau can't be booked
        // hourly, a Salle de formation can't be booked monthly). We load
        // it before validating so those two fields can be restricted
        // with the right Rule::in() for this specific space.
        $requestedSpace = Space::with('spaceType')->find($request->input('space_id'));

        $allowedDurationTypes = $requestedSpace?->bookableDurationTypes() ?? ['hourly', 'daily', 'monthly', 'custom'];
        $allowedEngagementUnits = $requestedSpace?->bookableEngagementUnits() ?? ['hour', 'half_day', 'day', 'month'];

        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'space_id' => ['required', 'exists:spaces,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'duration_type' => ['required', Rule::in($allowedDurationTypes)],
            'engagement_duration_value' => ['required', 'integer', 'min:1', 'max:999'],
            'engagement_duration_unit' => ['required', Rule::in($allowedEngagementUnits)],
            'negotiated_price' => ['required', 'numeric', 'min:0'],
            'contract_title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ], [
            'duration_type.in' => 'Ce type de durée n’est pas disponible pour ce type d’espace.',
            'engagement_duration_unit.in' => 'Cette unité de durée n’est pas disponible pour ce type d’espace.',
        ]);

        $client = Client::findOrFail($data['client_id']);

        $client = app(ClientRiskService::class)->apply($client);

        if ($client->risk_status === 'blocked') {
            return back()
                ->withInput()
                ->withErrors([
                    'client_id' => $client->risk_reason ?? 'Ce client est bloqué pour risque de fraude.',
                ]);
        }

        if ($client->risk_status === 'watchlist') {
            return back()
                ->withInput()
                ->withErrors([
                    'client_id' => $client->risk_reason ?? 'Ce client nécessite une vérification manuelle avant réservation.',
                ]);
        }

        if (! $client->hasCompleteLegalFile()) {
            return back()
                ->withInput()
                ->with('error', 'Impossible de créer la réservation : le dossier juridique du client est incomplet.');
        }

        if ($client->isBlockedForReservation()) {
            return back()
                ->withInput()
                ->withErrors([
                    'client_id' => 'Ce client ne peut pas créer une réservation car son compte est bloqué.',
                ]);
        }

        if ($client->hasLateUnpaidPayments()) {
            return back()
                ->withInput()
                ->withErrors([
                    'client_id' => 'Ce client a des paiements en retard. La réservation est bloquée jusqu’à régularisation.',
                ]);
        }

        $space = Space::findOrFail($data['space_id']);

        $normalizedStatus = mb_strtolower($space->status ?? 'available');

        $notReservableStatuses = [
            'occupied',
            'reserved',
            'unavailable',
            'maintenance',
            'in maintenance',

            'occupé',
            'occupe',
            'réservé',
            'reserve',
            'réservée',
            'indisponible',
            'en maintenance',
        ];

        if (in_array($normalizedStatus, $notReservableStatuses, true)) {
            return back()
                ->withInput()
                ->with('error', 'Cet espace ne peut pas être réservé pour le moment.');
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
                ->with('error', 'Cet espace est déjà réservé pendant cette période.');
        }

        $reservation = DB::transaction(function () use ($data, $client, $space, $startsAt, $endsAt) {
            $reservation = Reservation::create([
                'client_id' => $client->id,
                'space_id' => $space->id,
                'campus_id' => $space->campus_id,
                'floor_id' => $space->floor_id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'duration_type' => $data['duration_type'],
                'engagement_duration_value' => $data['engagement_duration_value'],
                'engagement_duration_unit' => $data['engagement_duration_unit'],
                'negotiated_price' => $data['negotiated_price'] ?? 0,
                'status' => 'pending',
                'created_by' => Auth::id(),
                'notes' => $data['notes'] ?? null,
            ]);

            Contract::create([
                'client_id' => $client->id,
                'reservation_id' => $reservation->id,
                'title' => $data['contract_title'] ?: 'Contract - ' . $client->full_name,
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
        $reservation->load([
            'client',
            'space',
            'campus',
            'floor',
            'contract',
            'creator',
        ]);

        $reservationStatuses = $this->reservationStatuses();

        return view('admin.reservations.show', compact(
            'reservation',
            'reservationStatuses'
        ));
    }

    private function reservationStatuses(): array
    {
        return [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'in_progress' => 'En cours',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            'expired' => 'Expirée',
        ];
    }

    private function durationTypes(): array
    {
        return [
            'hourly' => 'À l’heure',
            'daily' => 'À la journée',
            'monthly' => 'Au mois',
            'custom' => 'Personnalisée',
        ];
    }
}
