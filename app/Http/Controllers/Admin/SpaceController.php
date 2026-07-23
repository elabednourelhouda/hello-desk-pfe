<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Floor;
use App\Models\Space;
use App\Models\SpaceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpaceController extends Controller
{
    /**
     * Every possible space status. Kept as the single source of truth so
     * create/edit/index all render the same labels from one place.
     *
     * @var array<string, string>
     */
    private array $statuses = [
        'available' => 'Disponible',
        'occupied' => 'Occupé',
        'reserved' => 'Réservé',
        'unavailable' => 'Indisponible',
        'maintenance' => 'Maintenance',
    ];

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
            'statuses' => $this->statuses,
            'prefillCampusId' => $request->integer('campus_id') ?: null,
            'prefillFloorId' => $request->integer('floor_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        $space = Space::create($validated);

        return redirect()
            ->route('admin.settings.sites.floors.show', [$space->campus_id, $space->floor_id])
            ->with('success', "Espace « {$space->name} » créé avec succès.");
    }

    public function show(Space $space): View
    {
        $space->load(['campus', 'floor', 'spaceType']);

        return view('admin.spaces.show', [
            'space' => $space,
            'statuses' => $this->statuses,
        ]);
    }

    public function edit(Space $space): View
    {
        return view('admin.spaces.edit', [
            'space' => $space,
            'campuses' => Campus::where('is_active', true)->orWhere('id', $space->campus_id)->orderBy('name')->get(),
            'floors' => Floor::where('is_active', true)->orWhere('id', $space->floor_id)->with('campus')->orderBy('level')->get(),
            'spaceTypes' => SpaceType::where('is_active', true)->orWhere('id', $space->space_type_id)->orderBy('name')->get(),
            'statuses' => $this->statuses,
        ]);
    }

    public function update(Request $request, Space $space): RedirectResponse
    {
        $validated = $this->validateData($request, $space->id);

        $space->update($validated);

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

    private function validateData(Request $request, ?int $ignoreId = null): array
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
            'status' => ['required', 'in:' . implode(',', array_keys($this->statuses))],
            'description' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
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
}
