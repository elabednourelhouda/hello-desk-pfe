<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Floor;
use App\Models\Space;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InteractiveMapController extends Controller
{
    public function index(Request $request)
    {
        $scope = $this->commercialScope();
        $hasCommercialScope = $this->hasScope($scope);

        $campuses = collect();
        $floors = collect();
        $spaces = collect();

        $selectedCampusId = null;
        $selectedFloorId = null;
        $selectedFloor = null;

        if ($hasCommercialScope) {
            $campuses = Campus::query()
                ->whereIn('id', $scope['display_campus_ids'])
                ->orderBy('name')
                ->get();

            $requestedCampusId = $request->integer('campus_id');

            $selectedCampusId = $requestedCampusId && $campuses->pluck('id')->contains($requestedCampusId)
                ? $requestedCampusId
                : $campuses->first()?->id;

            if ($selectedCampusId) {
                $floorsQuery = Floor::query()
                    ->where('campus_id', $selectedCampusId)
                    ->orderBy('name');

                $this->applyScopeToFloorQuery($floorsQuery, $scope, $selectedCampusId);

                $floors = $floorsQuery->get();

                $defaultFloor = $floors->firstWhere('map_key', 'centre_ville_2') ?? $floors->first();

                $requestedFloorId = $request->integer('floor_id');

                $selectedFloorId = $requestedFloorId && $floors->pluck('id')->contains($requestedFloorId)
                    ? $requestedFloorId
                    : $defaultFloor?->id;

                if ($selectedFloorId) {
                    $selectedFloor = $floors->firstWhere('id', $selectedFloorId);

                    $spaces = Space::with([
                            'campus',
                            'floor',
                            'spaceType',
                            'reservations' => function ($query) {
                                $query->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                                    ->where('ends_at', '>=', now())
                                    ->orderBy('starts_at');
                            },
                        ])
                        ->where('campus_id', $selectedCampusId)
                        ->where('floor_id', $selectedFloorId)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get()
                        ->map(function ($space) {
                            $space->code = $space->internal_code;
                            $space->surface = $space->area_m2;

                            $space->display_price_per_hour = $space->price_per_hour ?? 0;
                            $space->display_price_per_day = $space->price_per_day ?? 0;
                            $space->display_price_per_month = $space->price_per_month ?? 0;

                            $savedStatus = mb_strtolower($space->status ?? 'disponible');

                            if (in_array($savedStatus, ['occupé', 'occupe', 'occupied'])) {
                                $space->display_status = 'Occupé';
                            } elseif (in_array($savedStatus, ['indisponible', 'unavailable'])) {
                                $space->display_status = 'Indisponible';
                            } elseif (in_array($savedStatus, ['maintenance', 'en maintenance'])) {
                                $space->display_status = 'En maintenance';
                            } elseif ($space->reservations->count() > 0) {
                                $space->display_status = 'Réservé';
                            } else {
                                $space->display_status = 'Disponible';
                            }

                            $space->next_reservation = $space->reservations->first();

                            return $space;
                        });
                }
            }
        }

        return view('commercial.interactive-map.index', compact(
            'campuses',
            'floors',
            'spaces',
            'selectedCampusId',
            'selectedFloorId',
            'selectedFloor',
            'hasCommercialScope'
        ));
    }

    private function applyScopeToFloorQuery(Builder $query, array $scope, int $selectedCampusId): void
    {
        $query->where(function (Builder $q) use ($scope, $selectedCampusId) {
            if (in_array($selectedCampusId, $scope['campus_ids'], true)) {
                $q->orWhere('campus_id', $selectedCampusId);
            }

            if (! empty($scope['floor_ids'])) {
                $q->orWhereIn('id', $scope['floor_ids']);
            }
        });
    }

    private function commercialScope(): array
    {
        if (! Schema::hasTable('staff_assignments')) {
            return [
                'campus_ids' => [],
                'floor_ids' => [],
                'display_campus_ids' => [],
            ];
        }

        $userColumn = collect(['user_id', 'commercial_id', 'staff_id'])
            ->first(fn ($column) => Schema::hasColumn('staff_assignments', $column));

        if (! $userColumn) {
            return [
                'campus_ids' => [],
                'floor_ids' => [],
                'display_campus_ids' => [],
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
            'display_campus_ids' => collect($campusIds)
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