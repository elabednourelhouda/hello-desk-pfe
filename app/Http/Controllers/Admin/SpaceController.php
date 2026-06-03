<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Floor;
use App\Models\Space;
use App\Models\SpaceType;
use Illuminate\Http\Request;

class SpaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Space::with(['campus', 'floor', 'spaceType'])
            ->latest();

        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('floor_id')) {
            $query->where('floor_id', $request->floor_id);
        }

        if ($request->filled('space_type_id')) {
            $query->where('space_type_id', $request->space_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $spaces = $query->paginate(10)->withQueryString();

        return view('admin.spaces.index', [
            'spaces' => $spaces,
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
            'floors' => Floor::where('is_active', true)->with('campus')->orderBy('level')->get(),
            'spaceTypes' => SpaceType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function map(Request $request)
    {
        $campuses = Campus::where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedCampus = null;
        $selectedFloor = null;

        if ($request->filled('campus_id')) {
            $selectedCampus = Campus::where('is_active', true)
                ->where('id', $request->campus_id)
                ->first();
        }

        if (!$selectedCampus) {
            $selectedCampus = $campuses->first();
        }

        $floors = collect();

        if ($selectedCampus) {
            $floors = Floor::where('is_active', true)
                ->where('campus_id', $selectedCampus->id)
                ->orderBy('level')
                ->get();

            if ($request->filled('floor_id')) {
                $selectedFloor = $floors->where('id', (int) $request->floor_id)->first();
            }

            if (!$selectedFloor) {
                $selectedFloor = $floors->first();
            }
        }

        $spaces = collect();

        if ($selectedCampus && $selectedFloor) {
            $spaces = Space::with(['campus', 'floor', 'spaceType', 'accessories'])
                ->where('campus_id', $selectedCampus->id)
                ->where('floor_id', $selectedFloor->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return view('admin.map.index', [
            'campuses' => $campuses,
            'floors' => $floors,
            'spaces' => $spaces,
            'selectedCampus' => $selectedCampus,
            'selectedFloor' => $selectedFloor,
        ]);
    }
}