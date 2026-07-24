<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProspectSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProspectSourceController extends Controller
{
    /**
     * Display every prospect source (active and inactive) so the admin
     * can manage the full list from a single screen.
     */
    public function index(): View
    {
        $prospectSources = ProspectSource::withCount('prospects')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.settings.prospect-sources.index', compact('prospectSources'));
    }

    public function create(): View
    {
        return view('admin.settings.prospect-sources.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        ProspectSource::create($validated);

        return redirect()
            ->route('admin.settings.prospect-sources.index')
            ->with('success', 'Source de prospect créée avec succès.');
    }

    public function edit(ProspectSource $prospectSource): View
    {
        return view('admin.settings.prospect-sources.edit', compact('prospectSource'));
    }

    public function update(Request $request, ProspectSource $prospectSource): RedirectResponse
    {
        $validated = $this->validateData($request, $prospectSource->id);

        $prospectSource->update($validated);

        return redirect()
            ->route('admin.settings.prospect-sources.index')
            ->with('success', 'Source de prospect mise à jour avec succès.');
    }

    /**
     * Toggle activation without touching any other field. This is the
     * action used from the list view's on/off switch.
     */
    public function toggleActive(ProspectSource $prospectSource): RedirectResponse
    {
        $prospectSource->update(['is_active' => ! $prospectSource->is_active]);

        $message = $prospectSource->is_active
            ? 'Source de prospect activée.'
            : 'Source de prospect désactivée.';

        return redirect()
            ->route('admin.settings.prospect-sources.index')
            ->with('success', $message);
    }

    /**
     * Delete is only allowed when no prospect currently uses this source,
     * to avoid ever leaving a prospect with a "source" value that no
     * longer maps to anything in the list. Otherwise, the admin is
     * redirected to use "deactivate" instead.
     */
    public function destroy(ProspectSource $prospectSource): RedirectResponse
    {
        if ($prospectSource->prospects()->exists()) {
            return redirect()
                ->route('admin.settings.prospect-sources.index')
                ->withErrors([
                    'prospect_source' => 'Impossible de supprimer cette source : des prospects existants l’utilisent encore. Désactivez-la à la place.',
                ]);
        }

        $prospectSource->delete();

        return redirect()
            ->route('admin.settings.prospect-sources.index')
            ->with('success', 'Source de prospect supprimée avec succès.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:prospect_sources,name' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
            'code' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                'unique:prospect_sources,code' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
        ], [
            'name.required' => 'Le nom de la source est obligatoire.',
            'name.unique' => 'Cette source existe déjà.',
            'code.required' => 'Le code est obligatoire (utilisé en interne, ex: salon_pro).',
            'code.alpha_dash' => 'Le code ne peut contenir que des lettres, chiffres, tirets et underscores.',
            'code.unique' => 'Ce code est déjà utilisé par une autre source.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}