<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\ProspectVisit;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = (int) Auth::id();

        $prospectsCount = $this->countOwnedRecords('prospects', $userId);
        $convertedProspectsCount = $this->countProspectsByStatus($userId, 'converted');
        $lostProspectsCount = $this->countProspectsByStatus($userId, 'lost');
        $activeProspectsCount = max($prospectsCount - $convertedProspectsCount - $lostProspectsCount, 0);

        $stats = [
            'prospects' => $prospectsCount,
            'clients' => $this->countCommercialClients($userId),
            'reservations' => $this->countOwnedRecords('reservations', $userId),
            'available_spaces' => $this->countAvailableSpaces(),
        ];

        $assignments = $this->loadAssignments($userId);
        $recentProspects = $this->loadRecentProspects($userId);
        $upcomingReservations = $this->loadUpcomingReservations($userId);

        $todayVisits = ProspectVisit::with(['prospect', 'campus', 'spaceType'])
            ->whereDate('visit_date', Carbon::today())
            ->where('status', 'planned')
            ->where(function ($query) use ($userId) {
                $query->where('commercial_id', $userId)
                    ->orWhereHas('prospect', function ($prospectQuery) use ($userId) {
                        $prospectQuery->where('assigned_to', $userId);
                    });
            })
            ->orderBy('visit_time')
            ->get();

        return view('commercial.dashboard', compact(
            'stats',
            'assignments',
            'recentProspects',
            'upcomingReservations',
            'todayVisits',
            'prospectsCount',
            'convertedProspectsCount',
            'lostProspectsCount',
            'activeProspectsCount'
        ));
    }

    private function countOwnedRecords(string $table, int $userId): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        $query = DB::table($table);

        $this->applyUserScope($query, $table, $userId);

        return (int) $query->count();
    }

    private function countProspectsByStatus(int $userId, string $status): int
    {
        if (! Schema::hasTable('prospects')) {
            return 0;
        }

        if (! Schema::hasColumn('prospects', 'assigned_to') || ! Schema::hasColumn('prospects', 'crm_status')) {
            return 0;
        }

        return (int) DB::table('prospects')
            ->where('assigned_to', $userId)
            ->where('crm_status', $status)
            ->count();
    }

    private function countCommercialClients(int $userId): int
    {
        if (! Schema::hasTable('clients')) {
            return 0;
        }

        $query = DB::table('clients');

        $query->where(function ($clientQuery) use ($userId) {
            if (
                Schema::hasTable('prospects')
                && Schema::hasColumn('clients', 'prospect_id')
                && Schema::hasColumn('prospects', 'assigned_to')
            ) {
                $clientQuery->whereIn('prospect_id', function ($subQuery) use ($userId) {
                    $subQuery->select('id')
                        ->from('prospects')
                        ->where('assigned_to', $userId);
                });
            }

            foreach (['assigned_to', 'commercial_id', 'responsible_commercial_id', 'created_by'] as $column) {
                if (Schema::hasColumn('clients', $column)) {
                    $clientQuery->orWhere($column, $userId);
                    break;
                }
            }
        });

        return (int) $query->count();
    }

    private function countAvailableSpaces(): int
    {
        if (! Schema::hasTable('spaces')) {
            return 0;
        }

        $query = DB::table('spaces');

        $statusColumn = $this->firstExistingColumn('spaces', [
            'availability_status',
            'status',
            'space_status',
        ]);

        if ($statusColumn) {
            $query->whereIn($statusColumn, [
                'available',
                'disponible',
                'Disponible',
                'AVAILABLE',
            ]);
        }

        return (int) $query->count();
    }

    private function loadAssignments(int $userId): Collection
    {
        if (! Schema::hasTable('staff_assignments')) {
            return collect();
        }

        $query = DB::table('staff_assignments');

        $this->applyUserScope($query, 'staff_assignments', $userId);

        $rows = $query->latest('id')->limit(5)->get();

        $campusNames = $this->nameMap('campuses', ['name', 'title', 'label']);
        $floorNames = $this->nameMap('floors', ['name', 'title', 'label', 'floor_number', 'number']);

        return $rows->map(function ($row) use ($campusNames, $floorNames) {
            $campusId = $row->campus_id ?? null;
            $floorId = $row->floor_id ?? null;

            return [
                'campus' => $campusId ? ($campusNames[$campusId] ?? 'Site #' . $campusId) : 'Tous les sites',
                'floor' => $floorId ? ($floorNames[$floorId] ?? 'Étage #' . $floorId) : 'Tous les étages',
            ];
        });
    }

    private function loadRecentProspects(int $userId): Collection
    {
        if (! Schema::hasTable('prospects')) {
            return collect();
        }

        $query = DB::table('prospects');

        $this->applyUserScope($query, 'prospects', $userId);

        if (Schema::hasColumn('prospects', 'created_at')) {
            $query->orderByDesc('created_at');
        } else {
            $query->latest('id');
        }

        return $query->limit(5)->get()->map(function ($prospect) {
            return [
                'name' => $prospect->full_name
                    ?? $prospect->name
                    ?? $prospect->nom_complet
                    ?? 'Prospect sans nom',

                'company' => $prospect->company_name
                    ?? $prospect->company
                    ?? $prospect->entreprise
                    ?? null,

                'status' => $prospect->crm_status
                    ?? $prospect->status
                    ?? $prospect->statut
                    ?? 'Nouveau',

                'created_at' => $prospect->created_at ?? null,
            ];
        });
    }

    private function loadUpcomingReservations(int $userId): Collection
    {
        if (! Schema::hasTable('reservations')) {
            return collect();
        }

        $dateColumn = $this->firstExistingColumn('reservations', [
            'start_date',
            'starts_at',
            'date_debut',
            'started_at',
        ]);

        $query = DB::table('reservations');

        $this->applyUserScope($query, 'reservations', $userId);

        if ($dateColumn) {
            $query->whereDate($dateColumn, '>=', now()->toDateString())
                ->orderBy($dateColumn);
        } else {
            $query->latest('id');
        }

        $spaceNames = $this->nameMap('spaces', ['name', 'title', 'label']);
        $clientNames = $this->nameMap('clients', ['full_name', 'name', 'nom_complet']);

        return $query->limit(5)->get()->map(function ($reservation) use ($dateColumn, $spaceNames, $clientNames) {
            $spaceId = $reservation->space_id ?? null;
            $clientId = $reservation->client_id ?? null;

            return [
                'client' => $clientId ? ($clientNames[$clientId] ?? 'Client #' . $clientId) : 'Client non défini',
                'space' => $spaceId ? ($spaceNames[$spaceId] ?? 'Espace #' . $spaceId) : 'Espace non défini',
                'date' => $dateColumn ? ($reservation->{$dateColumn} ?? null) : null,
                'status' => $reservation->status
                    ?? $reservation->reservation_status
                    ?? $reservation->statut
                    ?? 'En attente',
            ];
        });
    }

    private function applyUserScope(Builder $query, string $table, int $userId): void
    {
        $possibleColumns = [
            'assigned_to',
            'commercial_id',
            'assigned_commercial_id',
            'responsible_commercial_id',
            'created_by',
            'created_by_id',
            'user_id',
        ];

        foreach ($possibleColumns as $column) {
            if (Schema::hasColumn($table, $column)) {
                $query->where($column, $userId);
                return;
            }
        }
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function nameMap(string $table, array $possibleNameColumns): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $nameColumn = $this->firstExistingColumn($table, $possibleNameColumns);

        if (! $nameColumn || ! Schema::hasColumn($table, 'id')) {
            return [];
        }

        return DB::table($table)
            ->pluck($nameColumn, 'id')
            ->toArray();
    }
}