<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accessory;
use App\Models\Campus;
use App\Models\Floor;
use App\Models\Space;
use App\Models\SpaceStatus;
use App\Models\SpaceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
            'statuses' => $this->allSpaceStatusLabels(),
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

    /**
     * Accepts optional ?campus_id=&floor_id= query params so it can be
     * reached both from the flat /admin/spaces list and from a floor's
     * page inside Configuration → Sites, pre-filled either way.
     */
    public function create(Request $request): View
    {
        return view('admin.spaces.create', [
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
            'floors' => Floor::where('is_active', true)->with('campus')->orderBy('level')->get(),
            'spaceTypes' => SpaceType::where('is_active', true)->orderBy('name')->get(),
            'accessories' => Accessory::where('is_active', true)->orderBy('name')->get(),
            'statuses' => $this->spaceStatusOptions(),
            'prefillCampusId' => $request->integer('campus_id') ?: null,
            'prefillFloorId' => $request->integer('floor_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);
        $accessoryIds = $validated['accessory_ids'] ?? [];
        unset($validated['accessory_ids']);

        $space = Space::create($validated);
        $space->accessories()->sync($accessoryIds);

        return redirect()
            ->route('admin.settings.sites.floors.show', [$space->campus_id, $space->floor_id])
            ->with('success', "Espace « {$space->name} » créé avec succès.");
    }

    /**
     * Shows every stored detail about the space: full pricing, capacity,
     * description, equipment (accessories), and who is currently booked
     * in it (or the next upcoming reservation), plus the full booking
     * history for this space.
     */
    public function show(Space $space): View
    {
        $space->load([
            'campus',
            'floor',
            'spaceType',
            'accessories',
            'currentReservation.client',
            'nextReservation.client',
            'reservations' => function ($query) {
                $query->with('client')->latest('starts_at');
            },
        ]);

        return view('admin.spaces.show', [
            'space' => $space,
            'statuses' => $this->allSpaceStatusLabels(),
        ]);
    }

    public function edit(Space $space): View
    {
        $space->load('accessories');

        return view('admin.spaces.edit', [
            'space' => $space,
            'campuses' => Campus::where('is_active', true)->orWhere('id', $space->campus_id)->orderBy('name')->get(),
            'floors' => Floor::where('is_active', true)->orWhere('id', $space->floor_id)->with('campus')->orderBy('level')->get(),
            'spaceTypes' => SpaceType::where('is_active', true)->orWhere('id', $space->space_type_id)->orderBy('name')->get(),
            'accessories' => Accessory::where('is_active', true)->orderBy('name')->get(),
            'selectedAccessoryIds' => $space->accessories->pluck('id')->all(),
            'statuses' => $this->spaceStatusOptions($space->status),
        ]);
    }

    public function update(Request $request, Space $space): RedirectResponse
    {
        $validated = $this->validateData($request, $space->id, $space->status);
        $accessoryIds = $validated['accessory_ids'] ?? [];
        unset($validated['accessory_ids']);

        $space->update($validated);
        $space->accessories()->sync($accessoryIds);

        return redirect()
            ->route('admin.settings.sites.floors.show', [$space->campus_id, $space->floor_id])
            ->with('success', "Espace « {$space->name} » mis à jour avec succès.");
    }

    /**
     * Delete is only allowed when this space has no reservation history.
     * Otherwise the admin is redirected to "deactivate" instead, so the
     * historical reservation/contract/payment records keep a valid space.
     */
    public function destroy(Space $space): RedirectResponse
    {
        if ($space->reservations()->exists()) {
            return redirect()
                ->route('admin.settings.sites.floors.show', [$space->campus_id, $space->floor_id])
                ->withErrors([
                    'space' => 'Impossible de supprimer cet espace : des réservations y sont rattachées. Désactivez-le à la place.',
                ]);
        }

        $campusId = $space->campus_id;
        $floorId = $space->floor_id;

        $space->delete();

        return redirect()
            ->route('admin.settings.sites.floors.show', [$campusId, $floorId])
            ->with('success', 'Espace supprimé avec succès.');
    }

    private function validateData(Request $request, ?int $ignoreId = null, ?string $currentStatusCode = null): array
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'floor_id' => ['required', 'exists:floors,id'],
            'space_type_id' => ['required', 'exists:space_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:100',
                'unique:spaces,code' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'surface' => ['nullable', 'numeric', 'min:0'],
            'price_per_hour' => ['nullable', 'numeric', 'min:0'],
            'price_per_day' => ['nullable', 'numeric', 'min:0'],
            'price_per_month' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in($this->assignableStatusCodes($currentStatusCode))],
            'description' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'accessory_ids' => ['nullable', 'array'],
            'accessory_ids.*' => ['integer', 'exists:accessories,id'],
        ], [
            'campus_id.required' => 'Le site est obligatoire.',
            'floor_id.required' => 'L’étage est obligatoire.',
            'space_type_id.required' => 'Le type d’espace est obligatoire.',
            'name.required' => 'Le nom de l’espace est obligatoire.',
            'code.unique' => 'Ce code est déjà utilisé par un autre espace.',
            'status.required' => 'Le statut est obligatoire.',
        ]);

        // Guard: the chosen floor must actually belong to the chosen site.
        $floorBelongsToCampus = Floor::where('id', $validated['floor_id'])
            ->where('campus_id', $validated['campus_id'])
            ->exists();

        if (! $floorBelongsToCampus) {
            abort(422, 'L’étage sélectionné n’appartient pas au site sélectionné.');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }

    /**
     * Active space statuses for the create/edit "Statut" dropdown. If
     * $currentCode is given (editing), that code stays selectable even
     * if an admin has since deactivated it in Configuration > Statuts
     * d'espace — same pattern as Prospect's source/activity-sector
     * dropdowns.
     */
    private function spaceStatusOptions(?string $currentCode = null): array
    {
        return SpaceStatus::query()
            ->where(function ($q) use ($currentCode) {
                $q->where('is_active', true);

                if ($currentCode) {
                    $q->orWhere('code', $currentCode);
                }
            })
            ->orderBy('name')
            ->pluck('name', 'code')
            ->toArray();
    }

    /**
     * Every status label (active or not), for read-only displays like
     * the spaces list and the "Fiche espace" show page, and for the
     * index filter — an admin should still be able to filter by a
     * status even after deactivating it, to find the spaces that were
     * using it.
     */
    private function allSpaceStatusLabels(): array
    {
        return SpaceStatus::orderBy('name')->pluck('name', 'code')->toArray();
    }

    /**
     * Codes accepted by the 'status' validation rule: every active
     * status, plus $currentCode if editing (so re-submitting a form
     * without touching "Statut" doesn't fail validation just because
     * that status was deactivated in the meantime).
     */
    private function assignableStatusCodes(?string $currentCode = null): array
    {
        return array_keys($this->spaceStatusOptions($currentCode));
    }
}