<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        $baseQuery = $this->scopedClientsQuery($userId, $assignedCampusIds);

        $counts = [
            'all' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', 'active')->count(),
            'inactive' => (clone $baseQuery)->where('status', 'inactive')->count(),
            'complete_legal_file' => (clone $baseQuery)->get()->filter->hasCompleteLegalFile()->count(),
        ];

        $query = $this->scopedClientsQuery($userId, $assignedCampusIds)
            ->with(['user', 'prospect', 'mainCampus'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_type')) {
            $query->where('client_type', $request->client_type);
        }

        if ($request->filled('legal_status')) {
            if ($request->legal_status === 'complete') {
                $query->where(function (Builder $q) {
                    $q->where(function (Builder $physical) {
                        $physical->where('client_type', 'physique')
                            ->whereNotNull('first_name')
                            ->where('first_name', '!=', '')
                            ->whereNotNull('last_name')
                            ->where('last_name', '!=', '')
                            ->whereNotNull('identity_document_type')
                            ->where('identity_document_type', '!=', '')
                            ->whereNotNull('identity_document_number')
                            ->where('identity_document_number', '!=', '');
                    })
                        ->orWhere(function (Builder $company) {
                            $company->where('client_type', 'morale')
                                ->whereNotNull('company_name')
                                ->where('company_name', '!=', '')
                                ->whereNotNull('legal_form')
                                ->where('legal_form', '!=', '')
                                ->whereNotNull('ice_number')
                                ->where('ice_number', '!=', '')
                                ->whereNotNull('legal_representative_full_name')
                                ->where('legal_representative_full_name', '!=', '')
                                ->whereNotNull('legal_representative_identity_document_type')
                                ->where('legal_representative_identity_document_type', '!=', '')
                                ->whereNotNull('legal_representative_identity_document_number')
                                ->where('legal_representative_identity_document_number', '!=', '');
                        });
                });
            }

            if ($request->legal_status === 'incomplete') {
                $query->where(function (Builder $q) {
                    $q->whereNull('client_type')
                        ->orWhere(function (Builder $physical) {
                            $physical->where('client_type', 'physique')
                                ->where(function (Builder $missing) {
                                    $missing->whereNull('first_name')
                                        ->orWhere('first_name', '')
                                        ->orWhereNull('last_name')
                                        ->orWhere('last_name', '')
                                        ->orWhereNull('identity_document_type')
                                        ->orWhere('identity_document_type', '')
                                        ->orWhereNull('identity_document_number')
                                        ->orWhere('identity_document_number', '');
                                });
                        })
                        ->orWhere(function (Builder $company) {
                            $company->where('client_type', 'morale')
                                ->where(function (Builder $missing) {
                                    $missing->whereNull('company_name')
                                        ->orWhere('company_name', '')
                                        ->orWhereNull('legal_form')
                                        ->orWhere('legal_form', '')
                                        ->orWhereNull('ice_number')
                                        ->orWhere('ice_number', '')
                                        ->orWhereNull('legal_representative_full_name')
                                        ->orWhere('legal_representative_full_name', '')
                                        ->orWhereNull('legal_representative_identity_document_type')
                                        ->orWhere('legal_representative_identity_document_type', '')
                                        ->orWhereNull('legal_representative_identity_document_number')
                                        ->orWhere('legal_representative_identity_document_number', '');
                                });
                        });
                });
            }
        }

        if ($request->filled('main_campus_id')) {
            $query->where('main_campus_id', $request->main_campus_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function (Builder $q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('ice_number', 'like', "%{$search}%");
            });
        }

        $clients = $query->paginate(10)->withQueryString();

        return view('commercial.clients.index', [
            'clients' => $clients,
            'counts' => $counts,
            'campuses' => $this->availableCampuses($assignedCampusIds),
            'assignedCampuses' => $this->assignedCampuses($assignedCampusIds),
        ]);
    }

    public function create()
    {
        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        return view('commercial.clients.create', [
            'campuses' => $this->availableCampuses($assignedCampusIds),
            'assignedCampuses' => $this->assignedCampuses($assignedCampusIds),
        ]);
    }

    public function store(Request $request)
    {
        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        $campusRule = ['nullable', 'exists:campuses,id'];

        if (! empty($assignedCampusIds)) {
            $campusRule = ['nullable', Rule::in($assignedCampusIds)];
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'registered_at' => ['nullable', 'date'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:clients,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'main_campus_id' => $campusRule,
            'notes' => ['nullable', 'string'],

            'client_type' => ['nullable', 'in:physique,morale'],

            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'identity_document_type' => ['nullable', 'string', 'max:100'],
            'identity_document_number' => ['nullable', 'string', 'max:100'],
            'nationality' => ['nullable', 'string', 'max:100'],

            'billing_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],

            'company_name' => ['nullable', 'string', 'max:255'],
            'legal_form' => ['nullable', 'string', 'max:100'],
            'ice_number' => ['nullable', 'string', 'max:100'],
            'if_number' => ['nullable', 'string', 'max:100'],
            'rc_number' => ['nullable', 'string', 'max:100'],
            'patente_number' => ['nullable', 'string', 'max:100'],
            'cnss_number' => ['nullable', 'string', 'max:100'],
            'headquarters_address' => ['nullable', 'string', 'max:255'],

            'legal_representative_full_name' => ['nullable', 'string', 'max:255'],
            'legal_representative_identity_document_type' => ['nullable', 'string', 'max:100'],
            'legal_representative_identity_document_number' => ['nullable', 'string', 'max:100'],
            'legal_representative_phone' => ['nullable', 'string', 'max:50'],
            'legal_representative_email' => ['nullable', 'email', 'max:255'],

            'legal_file_notes' => ['nullable', 'string'],
        ], [
            'full_name.required' => 'Le nom complet est obligatoire.',
            'email.required' => 'L’email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'main_campus_id.in' => 'Ce campus ne fait pas partie de votre périmètre affecté.',
        ]);

        $temporaryPassword = 'HD-' . Str::upper(Str::random(8));

        $client = null;

        DB::transaction(function () use ($validated, $temporaryPassword, &$client, $userId) {
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'role' => 'client',
                'must_change_password' => true,
            ]);

            $client = new Client();

            $client->fill($validated);

            $client->user_id = $user->id;
            $client->status = 'active';
            $client->registered_at = $validated['registered_at'] ?? now()->toDateString();
            $client->joined_at = now()->toDateString();

            if ($client->hasCompleteLegalFile()) {
                $client->legal_file_status = 'complete';
                $client->legal_file_completed_at = now();
            } else {
                $client->legal_file_status = 'incomplete';
                $client->legal_file_completed_at = null;
            }

            $client->save();

            $updates = [];

            foreach (['assigned_to', 'commercial_id', 'responsible_commercial_id', 'created_by'] as $column) {
                if (Schema::hasColumn('clients', $column)) {
                    $updates[$column] = $userId;
                    break;
                }
            }

            if (! empty($updates)) {
                DB::table('clients')->where('id', $client->id)->update($updates);
            }
        });

        return redirect()
            ->route('commercial.clients.show', $client)
            ->with('success', 'Client ajouté avec succès.')
            ->with('temporary_password', $temporaryPassword);
    }

    public function show(int $client)
    {
        $client = $this->findScopedClient($client);

        $client->load([
            'user',
            'prospect.assignedCommercial',
            'prospect.preferredCampus',
            'prospect.preferredSpaceType',
            'mainCampus',
            'reservations.space',
            'reservations.campus',
            'reservations.floor',
            'contracts.reservation.space',
            'payments.contract',
            'complaints',
        ]);

        return view('commercial.clients.show', [
            'client' => $client,
        ]);
    }

    public function edit(int $client)
    {
        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        $client = $this->findScopedClient($client);

        return view('commercial.clients.edit', [
            'client' => $client,
            'campuses' => $this->availableCampuses($assignedCampusIds),
        ]);
    }

    public function update(Request $request, int $client)
    {
        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        $client = $this->findScopedClient($client);

        $campusRule = ['nullable', 'exists:campuses,id'];

        if (! empty($assignedCampusIds)) {
            $campusRule = ['nullable', Rule::in($assignedCampusIds)];
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->ignore($client->id),
                Rule::unique('users', 'email')->ignore($client->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'main_campus_id' => $campusRule,
            'status' => ['required', 'in:active,inactive'],
            'billing_info' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],

            'client_type' => ['nullable', 'in:physique,morale'],

            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'identity_document_type' => ['nullable', 'string', 'max:100'],
            'identity_document_number' => ['nullable', 'string', 'max:100'],
            'nationality' => ['nullable', 'string', 'max:100'],

            'billing_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],

            'legal_form' => ['nullable', 'string', 'max:100'],
            'ice_number' => ['nullable', 'string', 'max:100'],
            'if_number' => ['nullable', 'string', 'max:100'],
            'rc_number' => ['nullable', 'string', 'max:100'],
            'patente_number' => ['nullable', 'string', 'max:100'],
            'cnss_number' => ['nullable', 'string', 'max:100'],
            'headquarters_address' => ['nullable', 'string', 'max:255'],

            'legal_representative_full_name' => ['nullable', 'string', 'max:255'],
            'legal_representative_identity_document_type' => ['nullable', 'string', 'max:100'],
            'legal_representative_identity_document_number' => ['nullable', 'string', 'max:100'],
            'legal_representative_phone' => ['nullable', 'string', 'max:50'],
            'legal_representative_email' => ['nullable', 'email', 'max:255'],

            'legal_file_notes' => ['nullable', 'string'],
        ], [
            'full_name.required' => 'Le nom complet est obligatoire.',
            'email.required' => 'L’email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'main_campus_id.in' => 'Ce campus ne fait pas partie de votre périmètre affecté.',
        ]);

        $client->fill($validated);

        if ($client->hasCompleteLegalFile()) {
            $client->legal_file_status = 'complete';
            $client->legal_file_completed_at = $client->legal_file_completed_at ?? now();
        } else {
            $client->legal_file_status = 'incomplete';
            $client->legal_file_completed_at = null;
        }

        $client->save();

        if ($client->user) {
            $client->user->update([
                'name' => $client->full_name,
                'email' => $client->email,
            ]);
        }

        return redirect()
            ->route('commercial.clients.show', $client)
            ->with('success', 'Client modifié avec succès.');
    }

    private function findScopedClient(int $clientId): Client
    {
        $userId = (int) Auth::id();
        $assignedCampusIds = $this->assignedCampusIds($userId);

        return $this->scopedClientsQuery($userId, $assignedCampusIds)
            ->where('id', $clientId)
            ->firstOrFail();
    }

    private function scopedClientsQuery(int $userId, array $assignedCampusIds): Builder
    {
        $query = Client::query();

        if (empty($assignedCampusIds)) {
            return $query->where(function (Builder $q) use ($userId) {
                $q->whereHas('prospect', function (Builder $prospectQuery) use ($userId) {
                    $prospectQuery->where('assigned_to', $userId);
                });

                foreach (['assigned_to', 'commercial_id', 'responsible_commercial_id', 'created_by'] as $column) {
                    if (Schema::hasColumn('clients', $column)) {
                        $q->orWhere($column, $userId);
                        break;
                    }
                }

                // Demo fallback: show active clients if no assignment columns exist
                if (
                    ! Schema::hasColumn('clients', 'assigned_to')
                    && ! Schema::hasColumn('clients', 'commercial_id')
                    && ! Schema::hasColumn('clients', 'responsible_commercial_id')
                    && ! Schema::hasColumn('clients', 'created_by')
                ) {
                    $q->orWhere('status', 'active');
                }
            });
        }

        return $query->where(function (Builder $q) use ($userId, $assignedCampusIds) {
            $q->whereHas('prospect', function (Builder $prospectQuery) use ($userId) {
                $prospectQuery->where('assigned_to', $userId);
            });

            $q->orWhereIn('main_campus_id', $assignedCampusIds);

            foreach (['assigned_to', 'commercial_id', 'responsible_commercial_id', 'created_by'] as $column) {
                if (Schema::hasColumn('clients', $column)) {
                    $q->orWhere($column, $userId);
                    break;
                }
            }
        });
    }

    private function assignedCampusIds(int $userId): array
    {
        if (! Schema::hasTable('staff_assignments')) {
            return [];
        }

        if (! Schema::hasColumn('staff_assignments', 'commercial_id')) {
            return [];
        }

        return DB::table('staff_assignments')
            ->where('commercial_id', $userId)
            ->whereNotNull('campus_id')
            ->pluck('campus_id')
            ->unique()
            ->values()
            ->map(fn($id) => (int) $id)
            ->toArray();
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
        $query = Campus::where('is_active', true)->orderBy('name');

        if (! empty($assignedCampusIds)) {
            $query->whereIn('id', $assignedCampusIds);
        }

        return $query->get();
    }
}
