<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivitySector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivitySectorController extends Controller
{
    public function index(): View
    {
        $activitySectors = ActivitySector::withCount('prospects')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.settings.activity-sectors.index', compact('activitySectors'));
    }

    public function create(): View
    {
        return view('admin.settings.activity-sectors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        ActivitySector::create($validated);

        return redirect()
            ->route('admin.settings.activity-sectors.index')
            ->with('success', 'Secteur d’activité créé avec succès.');
    }

    public function edit(ActivitySector $activitySector): View
    {
        return view('admin.settings.activity-sectors.edit', compact('activitySector'));
    }

    public function update(Request $request, ActivitySector $activitySector): RedirectResponse
    {
        $validated = $this->validateData($request, $activitySector->id);

        $activitySector->update($validated);

        return redirect()
            ->route('admin.settings.activity-sectors.index')
            ->with('success', 'Secteur d’activité mis à jour avec succès.');
    }

    public function toggleActive(ActivitySector $activitySector): RedirectResponse
    {
        $activitySector->update(['is_active' => ! $activitySector->is_active]);

        $message = $activitySector->is_active
            ? 'Secteur d’activité activé.'
            : 'Secteur d’activité désactivé.';

        return redirect()
            ->route('admin.settings.activity-sectors.index')
            ->with('success', $message);
    }

    /**
     * Delete is only allowed when no prospect currently uses this sector,
     * same safety rule as ProspectSourceController::destroy().
     */
    public function destroy(ActivitySector $activitySector): RedirectResponse
    {
        if ($activitySector->prospects()->exists()) {
            return redirect()
                ->route('admin.settings.activity-sectors.index')
                ->withErrors([
                    'activity_sector' => 'Impossible de supprimer ce secteur : des prospects existants l’utilisent encore. Désactivez-le à la place.',
                ]);
        }

        $activitySector->delete();

        return redirect()
            ->route('admin.settings.activity-sectors.index')
            ->with('success', 'Secteur d’activité supprimé avec succès.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:activity_sectors,name' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
        ], [
            'name.required' => 'Le nom du secteur est obligatoire.',
            'name.unique' => 'Ce secteur existe déjà.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}