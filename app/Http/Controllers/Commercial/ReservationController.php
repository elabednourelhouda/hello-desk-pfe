<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $reservationStatuses = $this->reservationStatuses();

        $contractFilters = [
            'all' => 'Tous les contrats',
            'created' => 'Avec contrat',
            'missing' => 'Sans contrat',
        ];

        $scope = $this->commercialScope();
        $hasCommercialScope = $this->hasScope($scope);

        $query = Reservation::query()
            ->with(['client', 'space', 'contract'])
            ->latest('starts_at');

        $this->applySpaceScopeToReservationQuery($query, $scope);

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

        return view('commercial.reservations.index', compact(
            'reservations',
            'reservationStatuses',
            'contractFilters',
            'hasCommercialScope'
        ));
    }

    public function create(Request $request)
    {
        $scope = $this->commercialScope();

        if (! $this->hasScope($scope)) {
            return redirect()
                ->route('commercial.reservations.index')
                ->with('error', 'Vous n’avez aucune affectation active. Impossible de créer une réservation.');
        }

        $durationTypes = $this->durationTypes();

        $clients = $this->commercialClientsQuery($scope)
            ->get()
            ->sortByDesc(fn($client) => $client->hasCompleteLegalFile())
            ->values();

        $spacesQuery = Space::with(['campus', 'floor', 'spaceType'])
            ->where('is_active', true);

        $this->applySpaceScopeToSpaceQuery($spacesQuery, $scope);

        $spaces = $spacesQuery
            ->orderBy('name')
            ->get();

        $selectedSpace = null;

        if ($request->filled('space_id')) {
            $requestedSpace = Space::with(['campus', 'floor', 'spaceType'])
                ->where('is_active', true)
                ->find($request->space_id);

            if (! $requestedSpace) {
                return redirect()
                    ->route('commercial.interactive-map.index')
                    ->with('error', 'Espace introuvable ou indisponible.');
            }

            if (! $this->canManageSpace($requestedSpace, $scope)) {
                return redirect()
                    ->route('commercial.interactive-map.index', [
                        'campus_id' => $requestedSpace->campus_id,
                        'floor_id' => $requestedSpace->floor_id,
                    ])
                    ->with('error', 'Cet espace est hors de votre périmètre commercial. Consultation en lecture seule uniquement.');
            }

            $selectedSpace = $requestedSpace;

            $selectedSpace->display_price_per_hour = $selectedSpace->price_per_hour ?? 0;
            $selectedSpace->display_price_per_day = $selectedSpace->price_per_day ?? 0;
            $selectedSpace->display_price_per_month = $selectedSpace->price_per_month ?? 0;
        }

        return view('commercial.reservations.create', compact(
            'clients',
            'spaces',
            'selectedSpace',
            'durationTypes'
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
            'negotiated_price' => ['required', 'numeric', 'min:0'],
            'contract_title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $scope = $this->commercialScope();

        if (! $this->hasScope($scope)) {
            return back()
                ->withInput()
                ->with('error', 'Vous n’avez aucune affectation active. Impossible de créer une réservation.');
        }

        $client = Client::findOrFail($data['client_id']);

        if (! $this->canManageClient($client, $scope)) {
            return back()
                ->withInput()
                ->with('error', 'Ce client ne fait pas partie de votre périmètre commercial.');
        }

        if (! $client->hasCompleteLegalFile()) {
            return back()
                ->withInput()
                ->with('error', 'Impossible de créer la réservation : le dossier juridique du client est incomplet.');
        }

        $space = Space::findOrFail($data['space_id']);

        if (! $this->canManageSpace($space, $scope)) {
            return back()
                ->withInput()
                ->with('error', 'Cet espace ne fait pas partie de votre périmètre commercial.');
        }

        $normalizedStatus = mb_strtolower($space->status ?? 'available');

        if (in_array($normalizedStatus, $this->notReservableStatuses(), true)) {
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
                'negotiated_price' => $data['negotiated_price'] ?? 0,
                'status' => 'pending',
                'created_by' => Auth::id(),
                'notes' => $data['notes'] ?? null,
            ]);

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
            ->route('commercial.reservations.show', $reservation)
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

        $scope = $this->commercialScope();

        if (! $this->canManageReservation($reservation, $scope)) {
            abort(403, 'Cette réservation ne fait pas partie de votre périmètre commercial.');
        }

        $reservationStatuses = $this->reservationStatuses();

        return view('commercial.reservations.show', compact(
            'reservation',
            'reservationStatuses'
        ));
    }

    private function commercialScope(): array
    {
        if (! Schema::hasTable('staff_assignments')) {
            return [
                'campus_ids' => [],
                'floor_ids' => [],
                'client_campus_ids' => [],
            ];
        }

        $userColumn = collect(['user_id', 'commercial_id', 'staff_id'])
            ->first(fn($column) => Schema::hasColumn('staff_assignments', $column));

        if (! $userColumn) {
            return [
                'campus_ids' => [],
                'floor_ids' => [],
                'client_campus_ids' => [],
            ];
        }

        $query = DB::table('staff_assignments')
            ->where($userColumn, Auth::id());

        if (Schema::hasColumn('staff_assignments', 'active')) {
            $query->where('active', true);
        }

        if (Schema::hasColumn('staff_assignments', 'is_active')) {
            $query->where('is_active', true);
        }

        if (Schema::hasColumn('staff_assignments', 'status')) {
            $query->whereIn('status', ['active', 'actif']);
        }

        $assignments = $query->get();

        $campusIds = $assignments
            ->filter(fn($assignment) => filled($assignment->campus_id ?? null) && blank($assignment->floor_id ?? null))
            ->pluck('campus_id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $floorIds = $assignments
            ->filter(fn($assignment) => filled($assignment->floor_id ?? null))
            ->pluck('floor_id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $floorCampusIds = [];

        if (! empty($floorIds) && Schema::hasTable('floors')) {
            $floorCampusIds = DB::table('floors')
                ->whereIn('id', $floorIds)
                ->pluck('campus_id')
                ->filter()
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        return [
            'campus_ids' => $campusIds,
            'floor_ids' => $floorIds,
            'client_campus_ids' => collect($campusIds)
                ->merge($floorCampusIds)
                ->unique()
                ->values()
                ->all(),
        ];
    }

    private function commercialClientsQuery(array $scope)
    {
        $query = Client::query()->orderBy('full_name');

        if (! $this->hasScope($scope)) {
            return $query->whereRaw('1 = 0');
        }

        $hasCommercialId = Schema::hasColumn('clients', 'commercial_id');
        $hasCreatedBy = Schema::hasColumn('clients', 'created_by');
        $hasMainCampus = Schema::hasColumn('clients', 'main_campus_id') && ! empty($scope['client_campus_ids']);

        if (! $hasCommercialId && ! $hasCreatedBy && ! $hasMainCampus) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($scope, $hasCommercialId, $hasCreatedBy, $hasMainCampus) {
            if ($hasCommercialId) {
                $q->orWhere('commercial_id', Auth::id());
            }

            if ($hasCreatedBy) {
                $q->orWhere('created_by', Auth::id());
            }

            if ($hasMainCampus) {
                $q->orWhereIn('main_campus_id', $scope['client_campus_ids']);
            }
        });
    }

    private function applySpaceScopeToReservationQuery(Builder $query, array $scope): void
    {
        if (! $this->hasScope($scope)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function ($q) use ($scope) {
            if (! empty($scope['campus_ids'])) {
                $q->orWhereIn('campus_id', $scope['campus_ids']);
            }

            if (! empty($scope['floor_ids'])) {
                $q->orWhereIn('floor_id', $scope['floor_ids']);
            }

            $q->orWhereHas('space', function ($spaceQuery) use ($scope) {
                $this->applySpaceScopeToSpaceQuery($spaceQuery, $scope);
            });
        });
    }

    private function applySpaceScopeToSpaceQuery(Builder $query, array $scope): void
    {
        if (! $this->hasScope($scope)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function ($q) use ($scope) {
            if (! empty($scope['campus_ids'])) {
                $q->orWhereIn('campus_id', $scope['campus_ids']);
            }

            if (! empty($scope['floor_ids'])) {
                $q->orWhereIn('floor_id', $scope['floor_ids']);
            }
        });
    }

    private function canManageReservation(Reservation $reservation, array $scope): bool
    {
        if (! $this->hasScope($scope)) {
            return false;
        }

        $campusId = $reservation->campus_id ?? $reservation->space?->campus_id;
        $floorId = $reservation->floor_id ?? $reservation->space?->floor_id;

        return in_array((int) $campusId, $scope['campus_ids'], true)
            || in_array((int) $floorId, $scope['floor_ids'], true);
    }

    private function canManageSpace(Space $space, array $scope): bool
    {
        if (! $this->hasScope($scope)) {
            return false;
        }

        return in_array((int) $space->campus_id, $scope['campus_ids'], true)
            || in_array((int) $space->floor_id, $scope['floor_ids'], true);
    }

    private function canManageClient(Client $client, array $scope): bool
    {
        if (Schema::hasColumn('clients', 'commercial_id') && (int) $client->getAttribute('commercial_id') === Auth::id()) {
            return true;
        }

        if (Schema::hasColumn('clients', 'created_by') && (int) $client->getAttribute('created_by') === Auth::id()) {
            return true;
        }

        if (
            Schema::hasColumn('clients', 'main_campus_id')
            && in_array((int) $client->getAttribute('main_campus_id'), $scope['client_campus_ids'], true)
        ) {
            return true;
        }

        return false;
    }

    private function hasScope(array $scope): bool
    {
        return ! empty($scope['campus_ids']) || ! empty($scope['floor_ids']);
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

    private function notReservableStatuses(): array
    {
        return [
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
    }
}
