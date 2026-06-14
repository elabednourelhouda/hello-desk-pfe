<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\SpaceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProspectController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        $view = $request->get('view', 'active');

        $baseQuery = $this->baseProspectQuery($userId, $assignedCampusIds);

        $counts = [
            'active' => (clone $baseQuery)->whereNotIn('crm_status', ['converted', 'lost'])->count(),
            'converted' => (clone $baseQuery)->where('crm_status', 'converted')->count(),
            'lost' => (clone $baseQuery)->where('crm_status', 'lost')->count(),
            'all' => (clone $baseQuery)->count(),
        ];

        $query = $this->baseProspectQuery($userId, $assignedCampusIds)
            ->with([
                'preferredCampus',
                'preferredSpaceType',
                'assignedCommercial',
                'convertedClient',
            ])
            ->latest();

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

            $query->where(function (EloquentBuilder $q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $prospects = $query->paginate(10)->withQueryString();

        return view('commercial.prospects.index', [
            'prospects' => $prospects,
            'campuses' => $this->availableCampuses($assignedCampusIds),
            'assignedCampuses' => $this->assignedCampuses($assignedCampusIds),
            'statuses' => $this->crmStatuses(),
            'view' => $view,
            'counts' => $counts,
        ]);
    }

    public function create()
    {
        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        return view('commercial.prospects.create', [
            'campuses' => $this->availableCampuses($assignedCampusIds),
            'assignedCampuses' => $this->assignedCampuses($assignedCampusIds),
            'spaceTypes' => SpaceType::where('is_active', true)->orderBy('name')->get(),
            'statuses' => $this->editableCrmStatuses(),
            'sources' => $this->prospectSources(),
        ]);
    }

    public function store(Request $request)
    {
        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        $campusRule = ['nullable', 'exists:campuses,id'];

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'registered_at' => ['nullable', 'date'],
            'need' => ['nullable', 'string'],
            'preferred_campus_id' => $campusRule,
            'preferred_space_type_id' => ['nullable', 'exists:space_types,id'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'source' => ['nullable', Rule::in(array_keys($this->prospectSources()))],
            'notes' => ['nullable', 'string'],
        ], [
            'full_name.required' => 'Le nom complet est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'budget.numeric' => 'Le budget doit être un nombre.',
        ]);

        $validated['registered_at'] = $validated['registered_at'] ?? now()->toDateString();
        $validated['assigned_to'] = $userId;
        $validated['crm_status'] = 'new';

        $prospect = Prospect::create($validated);

        return redirect()
            ->route('commercial.prospects.show', $prospect)
            ->with('success', 'Prospect ajouté avec succès.');
    }

    public function show(int $prospect)
    {
        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        $prospect = $this->findScopedProspect($prospect);

        $prospect->load([
            'preferredCampus',
            'preferredSpaceType',
            'assignedCommercial',
            'convertedClient',
            'visits.campus',
            'visits.spaceType',
            'prospectRequests.campus',
            'prospectRequests.spaceType',
            'prospectRequests.creator',
        ]);

        return view('commercial.prospects.show', [
            'prospect' => $prospect,
            'campuses' => $this->availableCampuses($assignedCampusIds),
            'assignedCampuses' => $this->assignedCampuses($assignedCampusIds),
            'spaceTypes' => SpaceType::where('is_active', true)->orderBy('name')->get(),
            'statuses' => $this->crmStatuses(),
            'sources' => $this->prospectSources(),
            'lostReasons' => $this->lostReasons(),
        ]);
    }

    public function convert(Request $request, int $prospect)
    {
        $prospect = $this->findScopedProspect($prospect);

        if ($prospect->crm_status === 'converted' && $prospect->converted_client_id) {
            return redirect()
                ->route('commercial.prospects.show', $prospect)
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

            $client = Client::updateOrCreate(
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

            $this->attachCommercialToClient($client->id, (int) Auth::id());

            $prospect->update([
                'email' => $validated['client_email'],
                'crm_status' => 'converted',
                'converted_client_id' => $client->id,
                'converted_at' => now(),
            ]);
        });

        $redirect = redirect()
            ->route('commercial.prospects.show', $prospect)
            ->with('success', 'Prospect converti en client avec succès.');

        if ($temporaryPassword) {
            $redirect->with('temporary_password', $temporaryPassword);
        }

        if ($usedExistingClient) {
            $redirect->with('info', 'Ce client avait déjà un compte. Le profil client a été lié au prospect.');
        }

        return $redirect;
    }

    public function markLost(Request $request, int $prospect)
    {
        $prospect = $this->findScopedProspect($prospect);

        if ($prospect->crm_status === 'converted') {
            return back()->withErrors([
                'prospect' => 'Un prospect déjà converti ne peut pas être marqué comme perdu.',
            ]);
        }

        $validated = $request->validate([
            'lost_reason_key' => ['required', Rule::in(array_keys($this->lostReasons()))],
            'lost_reason_details' => ['nullable', 'string', 'max:1000'],
        ], [
            'lost_reason_key.required' => 'Veuillez choisir une raison de perte.',
            'lost_reason_key.in' => 'La raison de perte sélectionnée est invalide.',
            'lost_reason_details.max' => 'Le détail ne doit pas dépasser 1000 caractères.',
        ]);

        if ($validated['lost_reason_key'] === 'autre' && blank($validated['lost_reason_details'] ?? null)) {
            return back()
                ->withErrors(['lost_reason_details' => 'Veuillez préciser la raison de perte.'])
                ->withInput();
        }

        $reasonLabel = $this->lostReasons()[$validated['lost_reason_key']];

        $lostReason = $reasonLabel;

        if (filled($validated['lost_reason_details'] ?? null)) {
            $lostReason .= ' — ' . $validated['lost_reason_details'];
        }

        $prospect->update([
            'crm_status' => 'lost',
            'lost_reason' => $lostReason,
        ]);

        return redirect()
            ->route('commercial.prospects.index', ['view' => 'lost'])
            ->with('success', 'Prospect marqué comme perdu.');
    }

    public function reactivate(int $prospect)
    {
        $prospect = $this->findScopedProspect($prospect);

        if ($prospect->crm_status === 'converted') {
            return back()->withErrors([
                'prospect' => 'Un client déjà converti ne peut pas être réactivé comme prospect.',
            ]);
        }

        $prospect->update([
            'crm_status' => 'contacted',
        ]);

        return redirect()
            ->route('commercial.prospects.show', $prospect)
            ->with('success', 'Prospect réactivé avec succès.');
    }

    private function findScopedProspect(int $prospectId): Prospect
    {
        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        return $this->baseProspectQuery($userId, $assignedCampusIds)
            ->where('id', $prospectId)
            ->firstOrFail();
    }

    private function baseProspectQuery(int $userId, array $assignedCampusIds): EloquentBuilder
    {
        return Prospect::query()
            ->where('assigned_to', $userId);
    }

    private function assignedCampusIds(int $userId): array
    {
        if (! Schema::hasTable('staff_assignments')) {
            return [];
        }

        if (! Schema::hasColumn('staff_assignments', 'campus_id')) {
            return [];
        }

        $query = DB::table('staff_assignments');

        $userColumn = $this->assignmentUserColumn();

        if (! $userColumn) {
            return [];
        }

        $query->where($userColumn, $userId);

        if (Schema::hasColumn('staff_assignments', 'is_active')) {
            $query->where('is_active', true);
        }

        return $query
            ->whereNotNull('campus_id')
            ->pluck('campus_id')
            ->unique()
            ->values()
            ->map(fn($id) => (int) $id)
            ->toArray();
    }

    private function assignmentUserColumn(): ?string
    {
        $possibleColumns = [
            'user_id',
            'commercial_id',
            'staff_id',
            'assigned_user_id',
        ];

        foreach ($possibleColumns as $column) {
            if (Schema::hasColumn('staff_assignments', $column)) {
                return $column;
            }
        }

        return null;
    }

    private function assignedCampuses(array $assignedCampusIds)
    {
        if (empty($assignedCampusIds)) {
            return collect();
        }

        return Campus::whereIn('id', $assignedCampusIds)
            ->orderBy('name')
            ->get();
    }

    private function availableCampuses(array $assignedCampusIds)
    {
        return Campus::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function attachCommercialToClient(int $clientId, int $commercialId): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        $updates = [];

        foreach (['assigned_to', 'commercial_id', 'responsible_commercial_id', 'created_by'] as $column) {
            if (Schema::hasColumn('clients', $column)) {
                $updates[$column] = $commercialId;
                break;
            }
        }

        if (! empty($updates)) {
            DB::table('clients')->where('id', $clientId)->update($updates);
        }
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

    private function editableCrmStatuses(): array
    {
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

    public function edit(int $prospect)
    {
        $prospect = $this->findScopedProspect($prospect);

        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        return view('commercial.prospects.edit', [
            'prospect' => $prospect,
            'campuses' => $this->availableCampuses($assignedCampusIds),
            'assignedCampuses' => $this->assignedCampuses($assignedCampusIds),
            'spaceTypes' => SpaceType::where('is_active', true)->orderBy('name')->get(),
            'statuses' => $this->editableCrmStatuses(),
            'sources' => $this->prospectSources(),
        ]);
    }

    public function update(Request $request, int $prospect)
    {
        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        $prospect = $this->findScopedProspect($prospect);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'registered_at' => ['nullable', 'date'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],

            'preferred_campus_id' => ['nullable', 'exists:campuses,id'],
            'preferred_space_type_id' => ['nullable', 'exists:space_types,id'],

            'people_count' => ['nullable', 'integer', 'min:1'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'desired_start_date' => ['nullable', 'date'],
            'desired_rental_period' => ['nullable', 'in:hourly,daily,monthly,custom'],

            'source' => ['nullable', Rule::in(array_keys($this->prospectSources()))],
            'need' => ['nullable', 'string'],
            'crm_status' => ['required', 'in:new,contacted,visit_scheduled,visited,proposal_sent,negotiation,converted,lost'],
            'notes' => ['nullable', 'string'],
        ]);

        // Important: commercial must never update assigned_to.
        unset($validated['assigned_to']);

        $prospect->update($validated);

        return redirect()
            ->route('commercial.prospects.show', $prospect)
            ->with('success', 'Le prospect a été modifié avec succès.');
    }

    private function prospectSources(): array
    {
        return [
            'passage_direct' => 'Passage direct',
            'appel_telephonique' => 'Appel téléphonique',
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'site_web' => 'Site web',
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'google_maps' => 'Google / Maps',
            'recommandation' => 'Recommandation',
            'ancien_client' => 'Ancien client',
            'evenement' => 'Événement / networking',
            'autre' => 'Autre',
        ];
    }

    private function lostReasons(): array
    {
        return [
            'budget_trop_eleve' => 'Budget insuffisant / prix trop élevé',
            'besoin_non_adapte' => 'Besoin non adapté aux offres',
            'espace_indisponible' => 'Espace souhaité indisponible',
            'localisation_non_adaptee' => 'Localisation non adaptée',
            'delai_non_adapte' => 'Délai non adapté',
            'choix_concurrent' => 'A choisi un concurrent',
            'pas_de_reponse' => 'Pas de réponse après relance',
            'projet_reporte' => 'Projet reporté',
            'besoin_annule' => 'Besoin annulé',
            'autre' => 'Autre',
        ];
    }
}
