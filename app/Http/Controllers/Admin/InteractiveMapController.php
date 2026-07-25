<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Floor;
use App\Models\Space;
use Carbon\Carbon;
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

        [$rangeStart, $rangeEnd, $filterFrom, $filterTo] = $this->resolveDateRange($request);

        if ($selectedCampusId) {
            $floors = Floor::where('campus_id', $selectedCampusId)
                ->orderBy('name')
                ->get();

            $defaultFloor = $floors->firstWhere('map_key', 'centre_ville_2') ?? $floors->first();

            $defaultFloor = $floors->firstWhere('map_key', 'centre_ville_2') ?? $floors->first();

            $selectedFloorId = $request->integer('floor_id') ?: $defaultFloor?->id;

            if ($selectedFloorId) {
                $selectedFloor = Floor::find($selectedFloorId);

                $spaces = Space::with([
                        'campus',
                        'floor',
                        'spaceType',
                        'reservations' => function ($query) use ($rangeStart, $rangeEnd) {
                            $query->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                                ->where('starts_at', '<=', $rangeEnd)
                                ->where('ends_at', '>=', $rangeStart)
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

        return view('admin.interactive-map.index', compact(
            'campuses',
            'floors',
            'spaces',
            'selectedCampusId',
            'selectedFloorId',
            'selectedFloor',
            'filterFrom',
            'filterTo'
        ));
    }

    /**
     * Resolves the period the map should check reservations against.
     *
     * Without ?from=&to=, we check "right now" — i.e. is there a
     * reservation covering this exact instant? (starts_at <= now <=
     * ends_at). This intentionally replaces the old behavior of
     * checking only `ends_at >= now()` with no upper bound, which made
     * a space with ANY future reservation show "Réservé" indefinitely,
     * even weeks before that booking actually starts.
     *
     * With ?from=&to=, we check for any reservation that *overlaps*
     * that period at all — the space shows "Réservé" if it's booked
     * for even part of the requested window — so an admin previewing
     * "next month" sees which spaces already have something booked in
     * it, not just spaces booked for the whole month.
     *
     * @return array{0: Carbon, 1: Carbon, 2: ?string, 3: ?string}
     */
    private function resolveDateRange(Request $request): array
    {
        $from = $this->parseDate($request->query('from'));
        $to = $this->parseDate($request->query('to'));

        if ($from && $to) {
            $rangeStart = $from->copy()->startOfDay();
            $rangeEnd = $to->copy()->endOfDay();

            // Defensive: if the admin swapped the two dates, just swap
            // them back rather than returning an always-empty range.
            if ($rangeEnd->lt($rangeStart)) {
                [$rangeStart, $rangeEnd] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$rangeStart, $rangeEnd, $from->toDateString(), $to->toDateString()];
        }

        $now = Carbon::now();

        return [$now, $now, null, null];
    }

    /**
     * Parses a "Y-m-d" query string into a Carbon date, returning null
     * for anything missing or unparseable — a malformed ?from=/?to=
     * should silently fall back to "right now" rather than crash the
     * page with a 500.
     */
    private function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)?->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}