<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Floor;
use App\Models\Space;
use App\Models\SpaceStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InteractiveMapController extends Controller
{
    public function index(Request $request)
    {
        $campuses = Campus::orderBy('name')->get();

        // Every space status (active AND inactive — a space may still
        // carry a status an admin has since deactivated, and it must
        // keep rendering correctly), keyed by lowercase code for O(1)
        // lookup below, plus a synthetic "reserved" entry that is
        // never a real row in space_statuses (see SpaceStatus::spaces()
        // docblock) but still needs a color/label to render with.
        $statusPalette = SpaceStatus::all()->keyBy(fn ($status) => mb_strtolower($status->code));

        $reservedStatus = new SpaceStatus([
            'name' => 'Réservé',
            'code' => 'reserved',
            'color' => '#0284c7', // sky-600
            'is_active' => true,
        ]);

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
                    ->map(function ($space) use ($statusPalette, $reservedStatus) {
                        $space->code = $space->internal_code;
                        $space->surface = $space->area_m2;

                        $space->display_price_per_hour = $space->price_per_hour ?? 0;
                        $space->display_price_per_day = $space->price_per_day ?? 0;
                        $space->display_price_per_month = $space->price_per_month ?? 0;

                        $savedCode = mb_strtolower($space->status ?? 'available');

                        // "available" is the only stored status that can be
                        // superseded by the reservation-computed "Réservé" —
                        // any other manually-assigned status (maintenance,
                        // unavailable, or a future admin-added one) always
                        // takes priority, exactly like before this fix,
                        // except now driven entirely by the space_statuses
                        // table instead of a fixed list of known codes.
                        if (in_array($savedCode, ['available', 'disponible'], true) && $space->reservations->count() > 0) {
                            $resolvedStatus = $reservedStatus;
                        } else {
                            $resolvedStatus = $statusPalette->get($savedCode)
                                ?? $statusPalette->get('available')
                                ?? $reservedStatus; // last-resort fallback, should never actually hit
                        }

                        $space->display_status = $resolvedStatus->name;
                        $space->display_status_color = $resolvedStatus->color;
                        $space->display_status_style = $resolvedStatus->badgeStyle();

                        $space->next_reservation = $space->reservations->first();

                        return $space;
                    });
            }
        }

        // Legend order: Disponible first, Réservé second (both are the
        // "system" statuses every install has), then any admin-added
        // statuses alphabetically. Inactive statuses are omitted from
        // the legend (an admin can still have spaces stuck with an
        // inactive status — those still render correctly via the
        // per-space lookup above — but the legend itself only advertises
        // choices that are currently selectable going forward).
        $statusLegend = $statusPalette
            ->filter(fn ($status) => $status->is_active)
            ->push($reservedStatus)
            ->sortBy(fn ($status) => match (true) {
                in_array($status->code, ['available', 'disponible'], true) => '0',
                $status->code === 'reserved' => '1',
                default => '2' . $status->name,
            })
            ->values();

        return view('admin.interactive-map.index', compact(
            'campuses',
            'floors',
            'spaces',
            'selectedCampusId',
            'selectedFloorId',
            'selectedFloor',
            'filterFrom',
            'filterTo',
            'statusLegend'
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