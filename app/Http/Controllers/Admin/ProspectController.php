<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\SpaceType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProspectController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->get('view', 'active');

        $baseQuery = Prospect::query();

        $counts = [
            'active' => (clone $baseQuery)->whereNotIn('crm_status', ['converted', 'lost'])->count(),
            'converted' => (clone $baseQuery)->where('crm_status', 'converted')->count(),
            'lost' => (clone $baseQuery)->where('crm_status', 'lost')->count(),
            'all' => (clone $baseQuery)->count(),
        ];

        $query = Prospect::with([
            'preferredCampus',
            'preferredSpaceType',
            'assignedCommercial',
            'convertedClient',
        ])->latest();

        if ($view === 'active') {
            $query->whereNotIn('crm_status', ['converted', 'lost']);
        }

        if ($view === 'converted') {
            $query->where('crm_status', 'converted');
        }

        if ($view === 'lost') {
            $query->where('crm_status', 'lost');
        }

        if ($request->filled('crm_status')) {
            $query->where('crm_status', $request->crm_status);
        }

        if ($request->filled('preferred_campus_id')) {
            $query->where('preferred_campus_id', $request->preferred_campus_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $prospects = $query->paginate(10)->withQueryString();

        return view('admin.prospects.index', [
            'prospects' => $prospects,
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
            'statuses' => $this->crmStatuses(),
            'view' => $view,
            'counts' => $counts,
        ]);
    }

    public function create()
    {
        return view('admin.prospects.create', [
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
            'spaceTypes' => SpaceType::where('is_active', true)->orderBy('name')->get(),
            'commercials' => User::where('role', 'commercial')->orderBy('name')->get(),
            'statuses' => $this->crmStatuses(),
            'registered_at' => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],

            // Contact rule: at least one of them is required
            'phone' => ['nullable', 'required_without:email', 'string', 'max:50'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:255'],

            'company_name' => ['nullable', 'string', 'max:255'],
            'registered_at' => ['nullable', 'date'],

            'need' => ['nullable', 'string'],
            'preferred_campus_id' => ['nullable', 'exists:campuses,id'],
            'preferred_space_type_id' => ['nullable', 'exists:space_types,id'],

            'people_count' => ['nullable', 'integer', 'min:1', 'max:100'],
            'desired_start_date' => ['nullable', 'date'],
            'desired_rental_period' => ['nullable', 'in:hourly,daily,monthly,custom'],

            'source' => ['nullable', 'string', 'max:100'],
            'budget' => ['nullable', 'numeric', 'min:0'],

            'crm_status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ], [
            'full_name.required' => 'Le nom complet est obligatoire.',

            'phone.required_without' => 'Veuillez saisir un téléphone ou un email.',
            'email.required_without' => 'Veuillez saisir un email ou un téléphone.',
            'email.email' => 'Veuillez saisir une adresse email valide.',

            'budget.numeric' => 'Le budget doit être un nombre.',
            'people_count.integer' => 'Le nombre de personnes doit être un nombre entier.',
            'people_count.min' => 'Le nombre de personnes doit être au minimum 1.',
            'people_count.max' => 'Le nombre de personnes ne peut pas dépasser 100.',
        ]);

        $validated['registered_at'] = $validated['registered_at'] ?? now()->toDateString();

        $prospect = Prospect::create($validated);

        return redirect()
            ->route('admin.prospects.show', $prospect)
            ->with('success', 'Prospect ajouté avec succès.');
    }

    public function show(Prospect $prospect) 
    {
        $prospect->load([
            'preferredCampus',
            'preferredSpaceType',
            'assignedCommercial',
            'convertedClient',
            'visits.campus',
            'visits.spaceType',
            'visits.commercial',
        ]);

        return view('admin.prospects.show', [
            'prospect' => $prospect,
            'statuses' => $this->crmStatuses(),
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
            'spaceTypes' => SpaceType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function edit(Prospect $prospect)
    {
        $prospect->load([
            'preferredCampus',
            'preferredSpaceType',
            'assignedCommercial',
            'convertedClient',
        ]);

        return view('admin.prospects.edit', [
            'prospect' => $prospect,
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
            'spaceTypes' => SpaceType::where('is_active', true)->orderBy('name')->get(),
            'commercials' => User::where('role', 'commercial')->orderBy('name')->get(),
            'statuses' => $this->editableCrmStatuses($prospect),
        ]);
    }

    public function update(Request $request, Prospect $prospect)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],

            'registered_at' => ['nullable', 'date'],

            'need' => ['nullable', 'string'],
            'preferred_campus_id' => ['nullable', 'exists:campuses,id'],
            'preferred_space_type_id' => ['nullable', 'exists:space_types,id'],
            'people_count' => ['nullable', 'integer', 'min:1', 'max:100'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'desired_start_date' => ['nullable', 'date'],
            'desired_rental_period' => ['nullable', 'in:hourly,daily,monthly,custom'],
            'source' => ['nullable', 'string', 'max:100'],

            'crm_status' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ], [
            'full_name.required' => 'Le nom complet est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'people_count.integer' => 'Le nombre de personnes doit être un nombre entier.',
            'people_count.min' => 'Le nombre de personnes doit être au moins 1.',
            'people_count.max' => 'Le nombre de personnes ne doit pas dépasser 100.',
            'budget.numeric' => 'Le budget doit être un nombre.',
        ]);

        $validated['registered_at'] = $validated['registered_at'] ?? now()->toDateString();

        if ($prospect->crm_status === 'converted' && $prospect->converted_client_id) {
            $validated['crm_status'] = 'converted';
        }

        if ($validated['crm_status'] === 'converted' && !$prospect->converted_client_id) {
            $validated['crm_status'] = $prospect->crm_status;
        }

        $prospect->update($validated);

        return redirect()
            ->route('admin.prospects.show', $prospect)
            ->with('success', 'Prospect modifié avec succès.');
    }

    public function convert(Request $request, Prospect $prospect)
    {
        if ($prospect->crm_status === 'converted' && $prospect->converted_client_id) {
            return redirect()
                ->route('admin.prospects.show', $prospect)
                ->with('info', 'Ce prospect est déjà converti en client.');
        }

        $validated = $request->validate([
            'client_email' => ['required', 'email', 'max:255'],
        ], [
            'client_email.required' => 'L’email du client est obligatoire pour créer le compte.',
            'client_email.email' => 'Veuillez saisir une adresse email valide.',
        ]);

        $temporaryPassword = null;
        $usedExistingClient = false;

        DB::transaction(function () use ($prospect, $validated, &$temporaryPassword, &$usedExistingClient) {
            $existingUser = User::where('email', $validated['client_email'])->first();

            if ($existingUser && $existingUser->role !== 'client') {
                abort(422, 'Cet email est déjà utilisé par un autre type d’utilisateur.');
            }

            if ($existingUser) {
                $clientUser = $existingUser;
                $usedExistingClient = true;
            } else {
                $temporaryPassword = 'HD-' . Str::upper(Str::random(8));

                $clientUser = User::create([
                    'name' => $prospect->full_name,
                    'email' => $validated['client_email'],
                    'password' => Hash::make($temporaryPassword),
                    'role' => 'client',
                    'must_change_password' => true,
                ]);
            }

            Client::updateOrCreate(
                ['user_id' => $clientUser->id],
                [
                    'prospect_id' => $prospect->id,
                    'full_name' => $prospect->full_name,
                    'email' => $validated['client_email'],
                    'phone' => $prospect->phone,
                    'company_name' => $prospect->company_name,
                    'main_campus_id' => $prospect->preferred_campus_id,
                    'joined_at' => now()->toDateString(),
                    'status' => 'active',
                    'notes' => $prospect->notes,
                ]
            );

            $prospect->update([
                'email' => $validated['client_email'],
                'crm_status' => 'converted',
                'converted_client_id' => $clientUser->id,
                'converted_at' => now(),
            ]);
        });

        $redirect = redirect()
            ->route('admin.prospects.show', $prospect)
            ->with('success', 'Prospect converti en client avec succès.');

        if ($temporaryPassword) {
            $redirect->with('temporary_password', $temporaryPassword);
        }

        if ($usedExistingClient) {
            $redirect->with('info', 'Ce client avait déjà un compte. Le profil client a été lié au prospect.');
        }

        return $redirect;
    }

    public function markLost(Prospect $prospect)
    {
        if ($prospect->crm_status === 'converted') {
            return back()->withErrors([
                'prospect' => 'Un prospect déjà converti ne peut pas être marqué comme perdu.',
            ]);
        }

        $prospect->update([
            'crm_status' => 'lost',
        ]);

        return redirect()
            ->route('admin.prospects.index', ['view' => 'lost'])
            ->with('success', 'Prospect marqué comme perdu.');
    }

    public function reactivate(Prospect $prospect)
    {
        if ($prospect->crm_status === 'converted') {
            return back()->withErrors([
                'prospect' => 'Un client déjà converti ne peut pas être réactivé comme prospect.',
            ]);
        }

        $prospect->update([
            'crm_status' => 'contacted',
        ]);

        return redirect()
            ->route('admin.prospects.show', $prospect)
            ->with('success', 'Prospect réactivé avec succès.');
    }

    public function crm(Prospect $prospect)
    {
        $prospect->load([
            'visits.campus',
            'visits.spaceType',
            'requests',
            'assignedCommercial',
        ]);

        $statuses = [
            'new' => 'Nouveau',
            'contacted' => 'Contacté',
            'visit_planned' => 'Visite planifiée',
            'visit_done' => 'Visite effectuée',
            'proposal_sent' => 'Proposition envoyée',
            'negotiation' => 'En négociation',
            'converted' => 'Converti en client',
            'lost' => 'Perdu',
        ];

        $campuses = \App\Models\Campus::orderBy('name')->get();
        $spaceTypes = \App\Models\SpaceType::orderBy('name')->get();

        return view('admin.prospects.crm', compact(
            'prospect',
            'statuses',
            'campuses',
            'spaceTypes'
        ));
    }

    private function crmStatuses(): array
    {
        return [
            'new' => 'Nouveau',
            'contacted' => 'Contacté',
            'visit_scheduled' => 'Visite planifiée',
            'visited' => 'Visite effectuée',
            'proposal_sent' => 'Proposition envoyée',
            'negotiation' => 'En négociation',
            'converted' => 'Converti en client',
            'lost' => 'Perdu',
        ];
    }

    private function editableCrmStatuses(Prospect $prospect): array
    {
        if ($prospect->crm_status === 'converted' && $prospect->converted_client_id) {
            return [
                'converted' => 'Converti en client',
            ];
        }

        return [
            'new' => 'Nouveau',
            'contacted' => 'Contacté',
            'visit_scheduled' => 'Visite planifiée',
            'visited' => 'Visite effectuée',
            'proposal_sent' => 'Proposition envoyée',
            'negotiation' => 'En négociation',
            'lost' => 'Perdu',
        ];
    }
}