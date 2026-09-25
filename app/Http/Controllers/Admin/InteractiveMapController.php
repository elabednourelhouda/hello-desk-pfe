<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Floor;
use App\Models\Space;
use App\Models\SpaceStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

            $defaultFloor = $floors->first();

            $selectedFloorId = $request->integer('floor_id') ?: $defaultFloor?->id;

            if ($selectedFloorId) {
                $selectedFloor = Floor::find($selectedFloorId);

                $spaces = Space::with([
                        'campus',
                        'floor',
                        'spaceType',
                        'reservations' => function ($query) use ($rangeStart, $rangeEnd) {
                            $query->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                                ->where('starts_at', '<', $rangeEnd)
                                ->where('ends_at', '>', $rangeStart)
                                ->orderBy('starts_at');
                        },
                    ])
                    ->where('campus_id', $selectedCampusId)
                    ->where('floor_id', $selectedFloorId)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
                    ->map(function ($space) use ($statusPalette, $reservedStatus, $rangeStart, $rangeEnd) {
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
                        $hasOverlappingReservation = $space->reservations->contains(
                            fn ($reservation) => $reservation->starts_at->lt($rangeEnd)
                                && $reservation->ends_at->gt($rangeStart)
                        );

                        if (in_array($savedCode, ['available', 'disponible'], true) && $hasOverlappingReservation) {
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

                $spaces = Space::withTemporaryGridLayout($spaces);
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

    public function saveLayout(Request $request, Space $space): JsonResponse
    {
        $validated = $request->validate([
            'grid_column' => ['required', 'integer', 'min:1', 'max:9'],
            'grid_row' => ['required', 'integer', 'min:1', 'max:5'],
            'grid_width' => ['required', 'integer', 'min:1', 'max:9'],
            'grid_height' => ['required', 'integer', 'min:1', 'max:5'],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(SpaceStatus::query()->pluck('code')->all())],
        ]);

        if (
            $validated['grid_column'] + $validated['grid_width'] - 1 > 9
            || $validated['grid_row'] + $validated['grid_height'] - 1 > 5
        ) {
            return response()->json(['message' => 'La disposition doit rester dans la grille 9×5.'], 422);
        }

        DB::transaction(function () use ($space, $validated) {
            $overlaps = Space::query()
                ->where('campus_id', $space->campus_id)
                ->where('floor_id', $space->floor_id)
                ->where('spaces.id', '<>', $space->getKey())
                ->whereNotNull('grid_column')
                ->whereNotNull('grid_row')
                ->lockForUpdate()
                ->get()
                ->contains(function (Space $other) use ($validated): bool {
                    return ! (
                        $validated['grid_column'] + $validated['grid_width'] <= $other->grid_column
                        || $other->grid_column + ($other->grid_width ?: 1) <= $validated['grid_column']
                        || $validated['grid_row'] + $validated['grid_height'] <= $other->grid_row
                        || $other->grid_row + ($other->grid_height ?: 1) <= $validated['grid_row']
                    );
                });

            if ($overlaps) {
                abort(422, 'La disposition chevauche un autre espace.');
            }

            $space->update($validated);
        });

        return response()->json(['message' => 'Disposition enregistrée.']);
    }

    public function saveLayoutBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'spaces' => ['required', 'array', 'min:1'],
            'spaces.*.id' => ['required', 'integer', 'distinct', 'exists:spaces,id'],
            'spaces.*.grid_column' => ['required', 'integer', 'min:1', 'max:9'],
            'spaces.*.grid_row' => ['required', 'integer', 'min:1', 'max:5'],
            'spaces.*.grid_width' => ['required', 'integer', 'min:1', 'max:9'],
            'spaces.*.grid_height' => ['required', 'integer', 'min:1', 'max:5'],
            'spaces.*.status' => ['sometimes', 'nullable', 'string', Rule::in(SpaceStatus::query()->pluck('code')->all())],
        ]);

        $requestedSpaces = collect($validated['spaces']);
        $spaceModels = Space::query()
            ->whereIn('id', $requestedSpaces->pluck('id'))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($spaceModels->count() !== $requestedSpaces->count()) {
            return response()->json(['message' => 'Un espace de la disposition est introuvable.'], 422);
        }

        $firstSpace = $spaceModels->first();
        if ($spaceModels->contains(fn (Space $space): bool =>
            $space->campus_id !== $firstSpace->campus_id
            || $space->floor_id !== $firstSpace->floor_id
        )) {
            return response()->json(['message' => 'Les espaces doivent appartenir au même étage.'], 422);
        }

        $positions = $requestedSpaces->mapWithKeys(fn (array $layout): array => [
            $layout['id'] => $layout,
        ]);

        foreach ($positions as $layout) {
            if (
                $layout['grid_column'] + $layout['grid_width'] - 1 > 9
                || $layout['grid_row'] + $layout['grid_height'] - 1 > 5
            ) {
                return response()->json(['message' => 'La disposition doit rester dans la grille 9×5.'], 422);
            }
        }

        $layoutValues = $positions->values()->all();
        for ($index = 0; $index < count($layoutValues); $index++) {
            for ($otherIndex = $index + 1; $otherIndex < count($layoutValues); $otherIndex++) {
                if ($this->layoutsOverlap($layoutValues[$index], $layoutValues[$otherIndex])) {
                    return response()->json(['message' => 'La disposition chevauche un autre espace.'], 422);
                }
            }
        }

        DB::transaction(function () use ($firstSpace, $spaceModels, $positions): void {
            $otherSpaces = Space::query()
                ->where('campus_id', $firstSpace->campus_id)
                ->where('floor_id', $firstSpace->floor_id)
                ->whereNotIn('id', $spaceModels->keys())
                ->whereNotNull('grid_column')
                ->whereNotNull('grid_row')
                ->lockForUpdate()
                ->get();

            foreach ($otherSpaces as $otherSpace) {
                $otherLayout = [
                    'grid_column' => (int) $otherSpace->grid_column,
                    'grid_row' => (int) $otherSpace->grid_row,
                    'grid_width' => max(1, (int) $otherSpace->grid_width),
                    'grid_height' => max(1, (int) $otherSpace->grid_height),
                ];

                foreach ($positions as $layout) {
                    if ($this->layoutsOverlap($layout, $otherLayout)) {
                        abort(422, 'La disposition chevauche un autre espace.');
                    }
                }
            }

            foreach ($positions as $layout) {
                $spaceModels[$layout['id']]->update([
                    'grid_column' => $layout['grid_column'],
                    'grid_row' => $layout['grid_row'],
                    'grid_width' => $layout['grid_width'],
                    'grid_height' => $layout['grid_height'],
                    ...(array_key_exists('status', $layout) ? ['status' => $layout['status']] : []),
                ]);
            }
        });

        return response()->json(['message' => 'Disposition enregistrée.']);
    }

    private function layoutsOverlap(array $first, array $second): bool
    {
        return ! (
            $first['grid_column'] + $first['grid_width'] <= $second['grid_column']
            || $second['grid_column'] + $second['grid_width'] <= $first['grid_column']
            || $first['grid_row'] + $first['grid_height'] <= $second['grid_row']
            || $second['grid_row'] + $second['grid_height'] <= $first['grid_row']
        );
    }

    public function configure(Request $request)
    {
        $campuses = Campus::orderBy('name')->get();
        $selectedCampusId = $request->integer('campus_id') ?: $campuses->first()?->id;
        $floors = $selectedCampusId
            ? Floor::where('campus_id', $selectedCampusId)->orderBy('name')->get()
            : collect();
        $selectedFloorId = $request->integer('floor_id') ?: $floors->first()?->id;
        $selectedFloor = $floors->firstWhere('id', $selectedFloorId);
        $spaces = $selectedFloor
            ? Space::where('campus_id', $selectedCampusId)
                ->where('floor_id', $selectedFloor->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect();
        $spaces = Space::withTemporaryGridLayout($spaces);

        return view('admin.interactive-map.configure', [
            'campuses' => $campuses,
            'floors' => $floors,
            'selectedCampusId' => $selectedCampusId,
            'selectedFloorId' => $selectedFloorId,
            'selectedFloor' => $selectedFloor,
            'spaces' => $spaces,
            'statuses' => SpaceStatus::orderBy('name')->get(),
        ]);
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

        if ($from) {
            return [
                $from->copy()->startOfDay(),
                $from->copy()->endOfDay(),
                $from->toDateString(),
                null,
            ];
        }

        if ($to) {
            return [
                $to->copy()->startOfDay(),
                $to->copy()->endOfDay(),
                null,
                $to->toDateString(),
            ];
        }

        $now = Carbon::now();

        return [$now, $now->copy()->addSecond(), null, null];
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