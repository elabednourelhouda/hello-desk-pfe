<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservationDurationType;
use App\Models\SpaceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationDurationTypeController extends Controller
{
    /**
     * Display every duration type (active and inactive), ordered the
     * same way they're offered on the reservation form.
     */
    public function index(): View
    {
        $durationTypes = ReservationDurationType::withCount(['reservations', 'prospects'])
            ->orderBy('sort_order')
            ->get();

        return view('admin.settings.reservation-duration-types.index', compact('durationTypes'));
    }

    public function create(): View
    {
        $spaceTypes = SpaceType::orderBy('name')->get();

        return view('admin.settings.reservation-duration-types.create', compact('spaceTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);
        $spaceTypeIds = $this->validateSpaceTypeIds($request);

        $durationType = ReservationDurationType::create($validated);
        $durationType->spaceTypes()->sync($spaceTypeIds);

        return redirect()
            ->route('admin.settings.reservation-duration-types.index')
            ->with('success', 'Type de durée créé avec succès.');
    }

    public function edit(ReservationDurationType $reservationDurationType): View
    {
        $spaceTypes = SpaceType::orderBy('name')->get();
        $selectedSpaceTypeIds = $reservationDurationType->spaceTypes()->pluck('space_types.id')->all();

        return view('admin.settings.reservation-duration-types.edit', [
            'durationType' => $reservationDurationType,
            'spaceTypes' => $spaceTypes,
            'selectedSpaceTypeIds' => $selectedSpaceTypeIds,
        ]);
    }

    public function update(Request $request, ReservationDurationType $reservationDurationType): RedirectResponse
    {
        $validated = $this->validateData($request, $reservationDurationType->id);
        $spaceTypeIds = $this->validateSpaceTypeIds($request);

        $reservationDurationType->update($validated);
        $reservationDurationType->spaceTypes()->sync($spaceTypeIds);

        return redirect()
            ->route('admin.settings.reservation-duration-types.index')
            ->with('success', 'Type de durée mis à jour avec succès.');
    }

    public function toggleActive(ReservationDurationType $reservationDurationType): RedirectResponse
    {
        $reservationDurationType->update(['is_active' => ! $reservationDurationType->is_active]);

        $message = $reservationDurationType->is_active
            ? 'Type de durée activé.'
            : 'Type de durée désactivé.';

        return redirect()
            ->route('admin.settings.reservation-duration-types.index')
            ->with('success', $message);
    }

    /**
     * Swaps sort_order with the previous/next row so the reservation
     * form's <select> options can be reordered without editing code.
     */
    public function moveUp(ReservationDurationType $reservationDurationType): RedirectResponse
    {
        $previous = ReservationDurationType::where('sort_order', '<', $reservationDurationType->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        return $this->swapOrder($reservationDurationType, $previous);
    }

    public function moveDown(ReservationDurationType $reservationDurationType): RedirectResponse
    {
        $next = ReservationDurationType::where('sort_order', '>', $reservationDurationType->sort_order)
            ->orderBy('sort_order')
            ->first();

        return $this->swapOrder($reservationDurationType, $next);
    }

    private function swapOrder(ReservationDurationType $current, ?ReservationDurationType $neighbor): RedirectResponse
    {
        if ($neighbor) {
            $currentOrder = $current->sort_order;
            $current->update(['sort_order' => $neighbor->sort_order]);
            $neighbor->update(['sort_order' => $currentOrder]);
        }

        return redirect()
            ->route('admin.settings.reservation-duration-types.index')
            ->with('success', 'Ordre mis à jour avec succès.');
    }

    /**
     * Delete is only allowed when no reservation or prospect currently
     * uses this duration type's code, to avoid ever leaving a record
     * whose duration_type/desired_rental_period value no longer maps to
     * anything in the list. Otherwise the admin is redirected to use
     * "désactiver" instead — same guard pattern as
     * SpaceStatusController::destroy() and SpaceTypeController::destroy().
     */
    public function destroy(ReservationDurationType $reservationDurationType): RedirectResponse
    {
        if ($reservationDurationType->reservations()->exists() || $reservationDurationType->prospects()->exists()) {
            return redirect()
                ->route('admin.settings.reservation-duration-types.index')
                ->withErrors([
                    'reservation_duration_type' => 'Impossible de supprimer ce type de durée : des réservations ou prospects existants l’utilisent encore. Désactivez-le à la place.',
                ]);
        }

        $reservationDurationType->delete();

        return redirect()
            ->route('admin.settings.reservation-duration-types.index')
            ->with('success', 'Type de durée supprimé avec succès.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'code' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                'unique:reservation_duration_types,code' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
        ], [
            'name.required' => 'Le nom du type de durée est obligatoire.',
            'code.required' => 'Le code est obligatoire (utilisé en interne, ex: quarterly).',
            'code.alpha_dash' => 'Le code ne peut contenir que des lettres, chiffres, tirets et underscores.',
            'code.unique' => 'Ce code est déjà utilisé par un autre type de durée.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($ignoreId === null) {
            $validated['sort_order'] = ((int) ReservationDurationType::max('sort_order')) + 1;
        }

        return $validated;
    }

    /**
     * Which space types this duration type should be bookable on.
     * Empty selection is valid and intentional: it means "bookable on
     * every space type that has no explicit restriction of its own" —
     * see Space::bookableDurationTypes() fallback — NOT "bookable
     * nowhere".
     */
    private function validateSpaceTypeIds(Request $request): array
    {
        return $request->validate([
            'space_type_ids' => ['nullable', 'array'],
            'space_type_ids.*' => ['integer', 'exists:space_types,id'],
        ])['space_type_ids'] ?? [];
    }
}
