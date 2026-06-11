<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Reservation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $scope = $this->commercialScope();
        $hasCommercialScope = $this->hasScope($scope);

        $query = Complaint::with([
            'client',
            'user',
            'reservation.space',
            'contract.reservation.space',
        ]);

        $this->applyScopeToComplaintQuery($query, $scope);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('client', function (Builder $clientQuery) use ($search) {
                        $clientQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('reservation.space', function (Builder $spaceQuery) use ($search) {
                        $spaceQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('internal_code', 'like', "%{$search}%");
                    });
            });
        }

        $complaints = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $baseStatsQuery = Complaint::query();
        $this->applyScopeToComplaintQuery($baseStatsQuery, $scope);

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'new' => (clone $baseStatsQuery)->where('status', 'new')->count(),
            'in_progress' => (clone $baseStatsQuery)->where('status', 'in_progress')->count(),
            'resolved' => (clone $baseStatsQuery)->where('status', 'resolved')->count(),
            'urgent' => (clone $baseStatsQuery)->where('priority', 'urgent')->count(),
        ];

        return view('commercial.complaints.index', compact(
            'complaints',
            'stats',
            'hasCommercialScope'
        ));
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load([
            'client',
            'user',
            'reservation.space',
            'reservation.campus',
            'reservation.floor',
            'contract.reservation.space',
            'contract',
        ]);

        if (! $this->canManageComplaint($complaint)) {
            abort(403, 'Cette réclamation ne fait pas partie de votre périmètre commercial.');
        }

        return view('commercial.complaints.show', compact('complaint'));
    }

    public function update(Request $request, Complaint $complaint): RedirectResponse
    {
        $complaint->load([
            'reservation.space',
            'contract.reservation.space',
            'client',
        ]);

        if (! $this->canManageComplaint($complaint)) {
            abort(403, 'Cette réclamation ne fait pas partie de votre périmètre commercial.');
        }

        $data = $request->validate([
            'status' => ['required', 'string', 'in:new,in_progress,waiting,resolved,closed,rejected'],
            'priority' => ['required', 'string', 'in:low,normal,high,urgent'],
            'admin_response' => ['nullable', 'string', 'max:3000'],
        ]);

        $data['resolved_at'] = in_array($data['status'], ['resolved', 'closed'])
            ? now()
            : null;

        $complaint->update($data);

        return redirect()
            ->route('commercial.complaints.show', $complaint)
            ->with('success', 'Réclamation mise à jour avec succès.');
    }

    private function applyScopeToComplaintQuery(Builder $query, array $scope): void
    {
        if (! $this->hasScope($scope)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function (Builder $q) use ($scope) {
            $q->whereHas('reservation', function (Builder $reservationQuery) use ($scope) {
                $this->applyScopeToReservationQuery($reservationQuery, $scope);
            })
            ->orWhereHas('contract.reservation', function (Builder $reservationQuery) use ($scope) {
                $this->applyScopeToReservationQuery($reservationQuery, $scope);
            });

            if (! empty($scope['client_campus_ids'])) {
                $q->orWhereHas('client', function (Builder $clientQuery) use ($scope) {
                    $clientQuery->whereIn('main_campus_id', $scope['client_campus_ids']);
                });
            }
        });
    }

    private function applyScopeToReservationQuery(Builder $query, array $scope): void
    {
        if (! $this->hasScope($scope)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function (Builder $q) use ($scope) {
            if (! empty($scope['campus_ids'])) {
                $q->orWhereIn('campus_id', $scope['campus_ids']);
            }

            if (! empty($scope['floor_ids'])) {
                $q->orWhereIn('floor_id', $scope['floor_ids']);
            }

            $q->orWhereHas('space', function (Builder $spaceQuery) use ($scope) {
                if (! empty($scope['campus_ids'])) {
                    $spaceQuery->orWhereIn('campus_id', $scope['campus_ids']);
                }

                if (! empty($scope['floor_ids'])) {
                    $spaceQuery->orWhereIn('floor_id', $scope['floor_ids']);
                }
            });
        });
    }

    private function canManageComplaint(Complaint $complaint): bool
    {
        $scope = $this->commercialScope();

        if (! $this->hasScope($scope)) {
            return false;
        }

        if ($complaint->reservation && $this->canManageReservation($complaint->reservation, $scope)) {
            return true;
        }

        if ($complaint->contract?->reservation && $this->canManageReservation($complaint->contract->reservation, $scope)) {
            return true;
        }

        if (
            $complaint->client
            && $complaint->client->main_campus_id
            && in_array((int) $complaint->client->main_campus_id, $scope['client_campus_ids'], true)
        ) {
            return true;
        }

        return false;
    }

    private function canManageReservation(Reservation $reservation, array $scope): bool
    {
        $campusId = $reservation->campus_id ?? $reservation->space?->campus_id;
        $floorId = $reservation->floor_id ?? $reservation->space?->floor_id;

        return in_array((int) $campusId, $scope['campus_ids'], true)
            || in_array((int) $floorId, $scope['floor_ids'], true);
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
            ->first(fn ($column) => Schema::hasColumn('staff_assignments', $column));

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
            ->filter(fn ($assignment) => filled($assignment->campus_id ?? null) && blank($assignment->floor_id ?? null))
            ->pluck('campus_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $floorIds = $assignments
            ->filter(fn ($assignment) => filled($assignment->floor_id ?? null))
            ->pluck('floor_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $floorCampusIds = [];

        if (! empty($floorIds) && Schema::hasTable('floors')) {
            $floorCampusIds = DB::table('floors')
                ->whereIn('id', $floorIds)
                ->pluck('campus_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
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

    private function hasScope(array $scope): bool
    {
        return ! empty($scope['campus_ids']) || ! empty($scope['floor_ids']);
    }
}