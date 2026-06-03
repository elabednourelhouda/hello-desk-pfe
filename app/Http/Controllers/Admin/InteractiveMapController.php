<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Floor;
use App\Models\Space;
use Illuminate\Http\Request;

class InteractiveMapController extends Controller
{
    public function index(Request $request)
    {
        $campuses = Campus::orderBy('name')->get();

        $selectedCampusId = $request->integer('campus_id') ?: $campuses->first()?->id;

        $floors = collect();
        $selectedFloorId = null;
        $selectedFloor = null;
        $spaces = collect();

        if ($selectedCampusId) {
            $floors = Floor::where('campus_id', $selectedCampusId)
                ->orderBy('name')
                ->get();

            $selectedFloorId = $request->integer('floor_id') ?: $floors->first()?->id;

            if ($selectedFloorId) {
                $selectedFloor = Floor::find($selectedFloorId);

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

        return view('admin.interactive-map.index', compact(
            'campuses',
            'floors',
            'spaces',
            'selectedCampusId',
            'selectedFloorId',
            'selectedFloor'
        ));
    }
}