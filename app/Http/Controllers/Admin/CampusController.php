<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * User-facing name: "Site". Internally this remains the Campus model /
 * campuses table, since that structure already matches what a "site" is
 * (name, code, city, address, is_active) and renaming the model/table
 * would require touching every relation across the app for no functional
 * gain. See admin/settings/sites/*.blade.php for the "Site" wording.
 */
class CampusController extends Controller
{
    public function index(): View
    {
        $sites = Campus::withCount(['spaces', 'floors'])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.settings.sites.index', ['sites' => $sites]);
    }

    public function create(): View
    {
        return view('admin.settings.sites.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        Campus::create($validated);

        return redirect()
            ->route('admin.settings.sites.index')
            ->with('success', 'Site créé avec succès.');
    }

    /**
     * The site "archive" landing page: every floor on this site, each
     * with a count of its spaces, and quick actions to add/edit/toggle/
     * delete a floor. Clicking a floor goes to FloorController::show(),
     * which is the actual space-by-space archive.
     */
    public function show(Campus $campus): View
    {
        $campus->loadCount('spaces');
        $campus->load(['floors' => function ($query) {
            $query->withCount('spaces')->orderBy('level')->orderBy('name');
        }]);

        return view('admin.settings.sites.show', ['site' => $campus]);
    }

    public function edit(Campus $campus): View
    {
        return view('admin.settings.sites.edit', ['site' => $campus]);
    }

    public function update(Request $request, Campus $campus): RedirectResponse
    {
        $validated = $this->validateData($request, $campus->id);

        $campus->update($validated);

        return redirect()
            ->route('admin.settings.sites.index')
            ->with('success', 'Site mis à jour avec succès.');
    }

    public function toggleActive(Campus $campus): RedirectResponse
    {
        $campus->update(['is_active' => ! $campus->is_active]);

        $message = $campus->is_active
            ? 'Site activé.'
            : 'Site désactivé.';

        return redirect()
            ->route('admin.settings.sites.index')
            ->with('success', $message);
    }

    /**
     * Delete is only allowed when nothing depends on this site (no spaces,
     * no floors). Otherwise the admin is redirected to "deactivate" instead,
     * to avoid ever leaving orphaned foreign keys on spaces/floors.
     */
    public function destroy(Campus $campus): RedirectResponse
    {
        if ($campus->spaces()->exists() || $campus->floors()->exists()) {
            return redirect()
                ->route('admin.settings.sites.index')
                ->withErrors([
                    'site' => 'Impossible de supprimer ce site : des étages ou des espaces y sont encore rattachés. Désactivez-le à la place.',
                ]);
        }

        $campus->delete();

        return redirect()
            ->route('admin.settings.sites.index')
            ->with('success', 'Site supprimé avec succès.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:campuses,name' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                'unique:campuses,code' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Le nom du site est obligatoire.',
            'name.unique' => 'Ce site existe déjà.',
            'code.unique' => 'Ce code est déjà utilisé par un autre site.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
