<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Floor;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class InteractiveMapController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $hasCommercialScope = $user->hasAnyStaffAssignment();

        $campuses = Campus::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $floors = collect();
        $spaces = collect();

        $selectedCampusId = null;
        $selectedFloorId = null;
        $selectedFloor = null;

        $requestedCampusId = $request->integer('campus_id');

        $selectedCampusId = $requestedCampusId && $campuses->pluck('id')->contains($requestedCampusId)
            ? $requestedCampusId
            : $campuses->first()?->id;

        if ($selectedCampusId) {
            $floorsQuery = Floor::query()
                ->where('campus_id', $selectedCampusId);

            if (Schema::hasColumn('floors', 'is_active')) {
                $floorsQuery->where('is_active', true);
            }

            if (Schema::hasColumn('floors', 'level')) {
                $floorsQuery->orderBy('level');
            }

            $floors = $floorsQuery
                ->orderBy('name')
                ->get();

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
                    ->map(function ($space) use ($user) {
                        $space->code = $space->internal_code ?? $space->code ?? null;
                        $space->surface = $space->area_m2 ?? $space->surface ?? null;

                        $space->display_price_per_hour = $space->price_per_hour ?? 0;
                        $space->display_price_per_day = $space->price_per_day ?? 0;
                        $space->display_price_per_month = $space->price_per_month ?? 0;

                        $savedStatus = mb_strtolower($space->status ?? 'disponible');

                        if (in_array($savedStatus, ['occupé', 'occupe', 'occupied'], true)) {
                            $space->display_status = 'Occupé';
                        } elseif (in_array($savedStatus, ['indisponible', 'unavailable'], true)) {
                            $space->display_status = 'Indisponible';
                        } elseif (in_array($savedStatus, ['maintenance', 'en maintenance'], true)) {
                            $space->display_status = 'En maintenance';
                        } elseif ($space->reservations->count() > 0) {
                            $space->display_status = 'Réservé';
                        } else {
                            $space->display_status = 'Disponible';
                        }

                        $displayStatus = mb_strtolower($space->display_status);

                        $space->next_reservation = $space->reservations->first();

                        $space->can_manage = $user->canManageSpace($space);

                        $space->can_reserve = $space->can_manage
                            && in_array($displayStatus, ['disponible', 'available'], true);

                        $space->reserve_url = $space->can_reserve
                            ? route('commercial.reservations.create', ['space_id' => $space->id])
                            : null;

                        return $space;
                    });
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
}
