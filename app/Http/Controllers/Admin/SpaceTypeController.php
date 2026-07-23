<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpaceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpaceTypeController extends Controller
{
    /**
     * Display every space type (active and inactive) so the admin can
     * manage the full list from a single screen.
     */
    public function index(): View
    {
        $spaceTypes = SpaceType::withCount('spaces')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.settings.space-types.index', compact('spaceTypes'));
    }

    public function create(): View
    {
        return view('admin.settings.space-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        SpaceType::create($validated);

        return redirect()
            ->route('admin.settings.space-types.index')
            ->with('success', 'Type d’espace créé avec succès.');
    }

    public function edit(SpaceType $spaceType): View
    {
        return view('admin.settings.space-types.edit', compact('spaceType'));
    }

    public function update(Request $request, SpaceType $spaceType): RedirectResponse
    {
        $validated = $this->validateData($request, $spaceType->id);

        $spaceType->update($validated);

        return redirect()
            ->route('admin.settings.space-types.index')
            ->with('success', 'Type d’espace mis à jour avec succès.');
    }

    /**
     * Toggle activation without touching any other field. This is the
     * action used from the list view's on/off switch.
     */
    public function toggleActive(SpaceType $spaceType): RedirectResponse
    {
        $spaceType->update(['is_active' => ! $spaceType->is_active]);

        $message = $spaceType->is_active
            ? 'Type d’espace activé.'
            : 'Type d’espace désactivé.';

        return redirect()
            ->route('admin.settings.space-types.index')
            ->with('success', $message);
    }

    /**
     * Delete is only allowed when no space currently uses this type, to
     * avoid ever leaving orphaned or unexpectedly nulled foreign keys.
     * Otherwise, the admin is redirected to use "deactivate" instead.
     */
    public function destroy(SpaceType $spaceType): RedirectResponse
    {
        if ($spaceType->spaces()->exists()) {
            return redirect()
                ->route('admin.settings.space-types.index')
                ->withErrors([
                    'space_type' => 'Impossible de supprimer ce type d’espace : des espaces existants l’utilisent encore. Désactivez-le à la place.',
                ]);
        }

        $spaceType->delete();

        return redirect()
            ->route('admin.settings.space-types.index')
            ->with('success', 'Type d’espace supprimé avec succès.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:space_types,name' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
            'code' => [
                'nullable',
                'string',
                'max:100',
                'unique:space_types,code' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Le nom du type d’espace est obligatoire.',
            'name.unique' => 'Ce type d’espace existe déjà.',
            'code.unique' => 'Ce code est déjà utilisé par un autre type d’espace.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
