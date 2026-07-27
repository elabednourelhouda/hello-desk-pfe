<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpaceStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpaceStatusController extends Controller
{
    /**
     * Display every space status (active and inactive) so the admin
     * can manage the full list from a single screen.
     */
    public function index(): View
    {
        $spaceStatuses = SpaceStatus::withCount('spaces')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.settings.space-statuses.index', compact('spaceStatuses'));
    }

    public function create(): View
    {
        return view('admin.settings.space-statuses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        SpaceStatus::create($validated);

        return redirect()
            ->route('admin.settings.space-statuses.index')
            ->with('success', 'Statut d’espace créé avec succès.');
    }

    public function edit(SpaceStatus $spaceStatus): View
    {
        return view('admin.settings.space-statuses.edit', compact('spaceStatus'));
    }

    public function update(Request $request, SpaceStatus $spaceStatus): RedirectResponse
    {
        $validated = $this->validateData($request, $spaceStatus->id);

        $spaceStatus->update($validated);

        return redirect()
            ->route('admin.settings.space-statuses.index')
            ->with('success', 'Statut d’espace mis à jour avec succès.');
    }

    /**
     * Toggle activation without touching any other field. This is the
     * action used from the list view's on/off switch.
     */
    public function toggleActive(SpaceStatus $spaceStatus): RedirectResponse
    {
        $spaceStatus->update(['is_active' => ! $spaceStatus->is_active]);

        $message = $spaceStatus->is_active
            ? 'Statut d’espace activé.'
            : 'Statut d’espace désactivé.';

        return redirect()
            ->route('admin.settings.space-statuses.index')
            ->with('success', $message);
    }

    /**
     * Delete is only allowed when no space currently uses this status,
     * to avoid ever leaving a space with a "status" value that no
     * longer maps to anything in the list. Otherwise, the admin is
     * redirected to use "deactivate" instead.
     */
    public function destroy(SpaceStatus $spaceStatus): RedirectResponse
    {
        if ($spaceStatus->spaces()->exists()) {
            return redirect()
                ->route('admin.settings.space-statuses.index')
                ->withErrors([
                    'space_status' => 'Impossible de supprimer ce statut : des espaces existants l’utilisent encore. Désactivez-le à la place.',
                ]);
        }

        $spaceStatus->delete();

        return redirect()
            ->route('admin.settings.space-statuses.index')
            ->with('success', 'Statut d’espace supprimé avec succès.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:space_statuses,name' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
            'code' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                'unique:space_statuses,code' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
            'color' => [
                'required',
                'string',
                'regex:/^#[0-9a-fA-F]{6}$/',
            ],
        ], [
            'name.required' => 'Le nom du statut est obligatoire.',
            'name.unique' => 'Ce statut existe déjà.',
            'code.required' => 'Le code est obligatoire (utilisé en interne, ex: cleaning).',
            'code.alpha_dash' => 'Le code ne peut contenir que des lettres, chiffres, tirets et underscores.',
            'code.unique' => 'Ce code est déjà utilisé par un autre statut.',
            'color.required' => 'La couleur est obligatoire (utilisée sur la Vue Espace et le plan interactif).',
            'color.regex' => 'La couleur doit être un code hexadécimal valide, ex: #22c55e.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}