<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Floor;
use App\Models\SpaceStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FloorController extends Controller
{
    public function create(Campus $campus): View
    {
        return view('admin.settings.sites.floors.create', ['site' => $campus]);
    }

    public function store(Request $request, Campus $campus): RedirectResponse
    {
        $validated = $this->validateData($request, $campus->id);

        $floor = $campus->floors()->create($validated);

        return redirect()
            ->route('admin.settings.sites.show', $campus)
            ->with('success', "Étage « {$floor->name} » créé avec succès.");
    }

    /**
     * This IS the "archive" screen: every space on this floor, with its
     * price, capacity and status, all editable from here.
     */
    public function show(Campus $campus, Floor $floor): View
    {
        $this->guardFloorBelongsToCampus($campus, $floor);

        $floor->load(['spaces.spaceType']);

        return view('admin.settings.sites.floors.show', [
            'site' => $campus,
            'floor' => $floor,
            'statuses' => SpaceStatus::orderBy('name')->pluck('name', 'code')->toArray(),
        ]);
    }

    public function edit(Campus $campus, Floor $floor): View
    {
        $this->guardFloorBelongsToCampus($campus, $floor);

        return view('admin.settings.sites.floors.edit', [
            'site' => $campus,
            'floor' => $floor,
        ]);
    }

    public function update(Request $request, Campus $campus, Floor $floor): RedirectResponse
    {
        $this->guardFloorBelongsToCampus($campus, $floor);

        $validated = $this->validateData($request, $campus->id, $floor->id);

        $floor->update($validated);

        return redirect()
            ->route('admin.settings.sites.show', $campus)
            ->with('success', "Étage « {$floor->name} » mis à jour avec succès.");
    }

    public function toggleActive(Campus $campus, Floor $floor): RedirectResponse
    {
        $this->guardFloorBelongsToCampus($campus, $floor);

        $floor->update(['is_active' => ! $floor->is_active]);

        $message = $floor->is_active
            ? "Étage « {$floor->name} » activé."
            : "Étage « {$floor->name} » désactivé.";

        return redirect()
            ->route('admin.settings.sites.show', $campus)
            ->with('success', $message);
    }

    /**
     * Delete is only allowed when no space is attached to this floor.
     * Otherwise the admin is redirected to "deactivate" instead.
     */
    public function destroy(Campus $campus, Floor $floor): RedirectResponse
    {
        $this->guardFloorBelongsToCampus($campus, $floor);

        if ($floor->spaces()->exists()) {
            return redirect()
                ->route('admin.settings.sites.show', $campus)
                ->withErrors([
                    'floor' => 'Impossible de supprimer cet étage : des espaces y sont encore rattachés. Désactivez-le à la place.',
                ]);
        }

        $floor->delete();

        return redirect()
            ->route('admin.settings.sites.show', $campus)
            ->with('success', 'Étage supprimé avec succès.');
    }

    /**
     * Guards against /sites/{campusA}/floors/{floorOfCampusB} — a floor
     * that belongs to a different site than the one in the URL.
     */
    private function guardFloorBelongsToCampus(Campus $campus, Floor $floor): void
    {
        abort_if($floor->campus_id !== $campus->id, 404);
    }

    private function validateData(Request $request, int $campusId, ?int $ignoreFloorId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('floors', 'code')
                    ->where('campus_id', $campusId)
                    ->ignore($ignoreFloorId),
            ],
            'level' => ['nullable', 'integer'],
            'map_key' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Le nom de l’étage est obligatoire.',
            'code.unique' => 'Ce code est déjà utilisé par un autre étage.',
            'level.integer' => 'Le niveau doit être un nombre entier (ex: 0 pour le rez-de-chaussée).',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}