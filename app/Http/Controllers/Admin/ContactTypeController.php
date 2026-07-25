<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactTypeController extends Controller
{
    /**
     * Display every contact type (active and inactive) so the admin
     * can manage the full list from a single screen.
     */
    public function index(): View
    {
        $contactTypes = ContactType::withCount('prospectVisits')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.settings.contact-types.index', compact('contactTypes'));
    }

    public function create(): View
    {
        return view('admin.settings.contact-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        ContactType::create($validated);

        return redirect()
            ->route('admin.settings.contact-types.index')
            ->with('success', 'Type de contact créé avec succès.');
    }

    public function edit(ContactType $contactType): View
    {
        return view('admin.settings.contact-types.edit', compact('contactType'));
    }

    public function update(Request $request, ContactType $contactType): RedirectResponse
    {
        $validated = $this->validateData($request, $contactType->id);

        $contactType->update($validated);

        return redirect()
            ->route('admin.settings.contact-types.index')
            ->with('success', 'Type de contact mis à jour avec succès.');
    }

    /**
     * Toggle activation without touching any other field. This is the
     * action used from the list view's on/off switch.
     */
    public function toggleActive(ContactType $contactType): RedirectResponse
    {
        $contactType->update(['is_active' => ! $contactType->is_active]);

        $message = $contactType->is_active
            ? 'Type de contact activé.'
            : 'Type de contact désactivé.';

        return redirect()
            ->route('admin.settings.contact-types.index')
            ->with('success', $message);
    }

    /**
     * Delete is only allowed when no suivi entry currently uses this
     * contact type, to avoid ever leaving a record whose "contact_type"
     * value no longer maps to anything in the list. Otherwise, the
     * admin is redirected to use "deactivate" instead.
     */
    public function destroy(ContactType $contactType): RedirectResponse
    {
        if ($contactType->prospectVisits()->exists()) {
            return redirect()
                ->route('admin.settings.contact-types.index')
                ->withErrors([
                    'contact_type' => 'Impossible de supprimer ce type : des suivis existants l’utilisent encore. Désactivez-le à la place.',
                ]);
        }

        $contactType->delete();

        return redirect()
            ->route('admin.settings.contact-types.index')
            ->with('success', 'Type de contact supprimé avec succès.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:contact_types,name' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
            'code' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                'unique:contact_types,code' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
        ], [
            'name.required' => 'Le nom du type de contact est obligatoire.',
            'name.unique' => 'Ce type de contact existe déjà.',
            'code.required' => 'Le code est obligatoire (utilisé en interne, ex: sms).',
            'code.alpha_dash' => 'Le code ne peut contenir que des lettres, chiffres, tirets et underscores.',
            'code.unique' => 'Ce code est déjà utilisé par un autre type de contact.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}