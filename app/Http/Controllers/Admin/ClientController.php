<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ClientRiskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['user', 'prospect', 'mainCampus'])
            ->latest();

        if ($request->filled('status')) {
            if ($request->status === 'inactive') {
                $query->whereIn('status', ['inactive', 'payment_hold', 'banned']);
            } elseif (in_array($request->status, ['active', 'payment_hold', 'banned'], true)) {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('client_type')) {
            $query->where('client_type', $request->client_type);
        }

        if ($request->filled('legal_status')) {
            if ($request->legal_status === 'complete') {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('client_type', 'physique')
                            ->whereNotNull('first_name')
                            ->whereNotNull('last_name')
                            ->whereNotNull('identity_document_type')
                            ->whereNotNull('identity_document_number');
                    })->orWhere(function ($sub) {
                        $sub->where('client_type', 'morale')
                            ->whereNotNull('company_name')
                            ->whereNotNull('legal_form')
                            ->whereNotNull('ice_number')
                            ->whereNotNull('legal_representative_full_name')
                            ->whereNotNull('legal_representative_identity_document_type')
                            ->whereNotNull('legal_representative_identity_document_number');
                    });
                });
            }

            if ($request->legal_status === 'incomplete') {
                $query->where(function ($q) {
                    $q->whereNull('client_type')
                        ->orWhere(function ($sub) {
                            $sub->where('client_type', 'physique')
                                ->where(function ($missing) {
                                    $missing->whereNull('first_name')
                                        ->orWhereNull('last_name')
                                        ->orWhereNull('identity_document_type')
                                        ->orWhereNull('identity_document_number');
                                });
                        })
                        ->orWhere(function ($sub) {
                            $sub->where('client_type', 'morale')
                                ->where(function ($missing) {
                                    $missing->whereNull('company_name')
                                        ->orWhereNull('legal_form')
                                        ->orWhereNull('ice_number')
                                        ->orWhereNull('legal_representative_full_name')
                                        ->orWhereNull('legal_representative_identity_document_type')
                                        ->orWhereNull('legal_representative_identity_document_number');
                                });
                        });
                });
            }
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

        if ($request->filled('risk_status')) {
            if (in_array($request->risk_status, ['clear', 'watchlist', 'blocked'], true)) {
                $query->where('risk_status', $request->risk_status);
            }
        }

        $clients = $query->paginate(10)->withQueryString();

        return view('admin.clients.index', [
            'clients' => $clients,
            'activeCount' => Client::where('status', 'active')->count(),
            'inactiveCount' => Client::whereIn('status', ['inactive', 'payment_hold', 'banned'])->count(),
            'paymentHoldCount' => Client::where('status', 'payment_hold')->count(),
            'bannedCount' => Client::where('status', 'banned')->count(),
            'totalCount' => Client::count(),
            'clearRiskCount' => Client::where('risk_status', 'clear')->count(),
            'watchlistRiskCount' => Client::where('risk_status', 'watchlist')->count(),
            'blockedRiskCount' => Client::where('risk_status', 'blocked')->count(),
        ]);
    }

    public function create()
    {
        $campuses = Campus::orderBy('name')->get();

        return view('admin.clients.create', compact('campuses'));
    }

    public function store(Request $request)
    {
        $validated = $this->normalizeClientData(
            $request->validate(array_merge($this->clientRules(), $this->attachmentRules()), [
                'full_name.required' => 'Le nom complet est obligatoire.',
                'email.required' => 'L’email est obligatoire.',
                'email.email' => 'Veuillez saisir une adresse email valide.',
                'email.unique' => 'Cet email est déjà utilisé.',
                'client_type.required' => 'Veuillez choisir le type de client.',

                'first_name.required_if' => 'Le prénom est obligatoire pour une personne physique.',
                'last_name.required_if' => 'Le nom est obligatoire pour une personne physique.',
                'identity_document_type.required_if' => 'Le type de pièce est obligatoire pour une personne physique.',
                'identity_document_number.required_if' => 'Le numéro de pièce est obligatoire pour une personne physique.',

                'company_name.required_if' => 'La raison sociale est obligatoire pour une personne morale.',
                'legal_form.required_if' => 'La forme juridique est obligatoire pour une personne morale.',
                'ice_number.required_if' => 'L’ICE est obligatoire pour une personne morale.',
                'legal_representative_full_name.required_if' => 'Le représentant légal est obligatoire pour une personne morale.',
                'legal_representative_identity_document_type.required_if' => 'Le type de pièce du représentant légal est obligatoire.',
                'legal_representative_identity_document_number.required_if' => 'Le numéro de pièce du représentant légal est obligatoire.',
            ])
        );

        $temporaryPassword = Str::random(10);

        $client = DB::transaction(function () use ($validated, $temporaryPassword) {
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($temporaryPassword),
                'role' => 'client',
                'must_change_password' => true,
            ]);

            return Client::create([
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'main_campus_id' => $validated['main_campus_id'] ?? null,
                'registered_at' => $validated['registered_at'] ?? now()->toDateString(),
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,

                'client_type' => $validated['client_type'],

                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'identity_document_type' => $validated['identity_document_type'] ?? null,
                'identity_document_number' => $validated['identity_document_number'] ?? null,
                'nationality' => $validated['nationality'] ?? null,

                'billing_email' => $validated['billing_email'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? 'Maroc',

                'legal_form' => $validated['legal_form'] ?? null,
                'ice_number' => $validated['ice_number'] ?? null,
                'if_number' => $validated['if_number'] ?? null,
                'rc_number' => $validated['rc_number'] ?? null,
                'patente_number' => $validated['patente_number'] ?? null,
                'cnss_number' => $validated['cnss_number'] ?? null,
                'headquarters_address' => $validated['headquarters_address'] ?? null,

                'legal_representative_full_name' => $validated['legal_representative_full_name'] ?? null,
                'legal_representative_identity_document_type' => $validated['legal_representative_identity_document_type'] ?? null,
                'legal_representative_identity_document_number' => $validated['legal_representative_identity_document_number'] ?? null,
                'legal_representative_phone' => $validated['legal_representative_phone'] ?? null,
                'legal_representative_email' => $validated['legal_representative_email'] ?? null,
            ]);
        });

        $this->storeInitialAttachments($request, $client);

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Client créé avec succès.')
            ->with('temporary_password', $temporaryPassword);
    }

    private function clientRules(?Client $client = null): array
    {
        $emailRules = ['required', 'email', 'max:255'];

        if ($client) {
            $emailRules[] = Rule::unique('users', 'email')->ignore($client->user_id);
            $emailRules[] = Rule::unique('clients', 'email')->ignore($client->id);
        } else {
            $emailRules[] = 'unique:users,email';
            $emailRules[] = 'unique:clients,email';
        }

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'required_if:client_type,morale', 'string', 'max:255'],
            'main_campus_id' => ['nullable', 'exists:campuses,id'],
            'registered_at' => ['nullable', 'date'],
            'joined_at' => ['nullable', 'date'],
            'billing_info' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],

            'client_type' => ['required', 'in:physique,morale'],

            // Personne physique
            'first_name' => ['nullable', 'required_if:client_type,physique', 'string', 'max:255'],
            'last_name' => ['nullable', 'required_if:client_type,physique', 'string', 'max:255'],
            'identity_document_type' => ['nullable', 'required_if:client_type,physique', 'in:cin,passport,carte_sejour'],
            'identity_document_number' => ['nullable', 'required_if:client_type,physique', 'string', 'max:100'],
            'nationality' => ['nullable', 'string', 'max:100'],

            // Coordonnées / facturation
            'billing_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],

            // Personne morale
            'legal_form' => ['nullable', 'required_if:client_type,morale', 'in:sarl,sa,snc,auto_entrepreneur,association,other'],
            'ice_number' => ['nullable', 'required_if:client_type,morale', 'string', 'max:50'],
            'if_number' => ['nullable', 'string', 'max:50'],
            'rc_number' => ['nullable', 'string', 'max:50'],
            'patente_number' => ['nullable', 'string', 'max:50'],
            'cnss_number' => ['nullable', 'string', 'max:50'],
            'headquarters_address' => ['nullable', 'string', 'max:255'],

            // Représentant légal
            'legal_representative_full_name' => ['nullable', 'required_if:client_type,morale', 'string', 'max:255'],
            'legal_representative_identity_document_type' => ['nullable', 'required_if:client_type,morale', 'in:cin,passport,carte_sejour'],
            'legal_representative_identity_document_number' => ['nullable', 'required_if:client_type,morale', 'string', 'max:100'],
            'legal_representative_phone' => ['nullable', 'string', 'max:50'],
            'legal_representative_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    private function normalizeClientData(array $validated): array
    {
        if (($validated['client_type'] ?? null) === 'physique') {
            $validated['company_name'] = null;
            $validated['legal_form'] = null;
            $validated['ice_number'] = null;
            $validated['if_number'] = null;
            $validated['rc_number'] = null;
            $validated['patente_number'] = null;
            $validated['cnss_number'] = null;
            $validated['headquarters_address'] = null;
            $validated['legal_representative_full_name'] = null;
            $validated['legal_representative_identity_document_type'] = null;
            $validated['legal_representative_identity_document_number'] = null;
            $validated['legal_representative_phone'] = null;
            $validated['legal_representative_email'] = null;
        }

        if (($validated['client_type'] ?? null) === 'morale') {
            $validated['first_name'] = null;
            $validated['last_name'] = null;
            $validated['identity_document_type'] = null;
            $validated['identity_document_number'] = null;
            $validated['nationality'] = null;
        }

        return $validated;
    }

    public function analyzeRisks(ClientRiskService $riskService)
    {
        $scanned = 0;
        $blocked = 0;
        $watchlist = 0;

        Client::query()
            ->orderBy('id')
            ->chunkById(100, function ($clients) use ($riskService, &$scanned, &$blocked, &$watchlist) {
                foreach ($clients as $client) {
                    $updatedClient = $riskService->apply($client);

                    $scanned++;

                    if ($updatedClient->risk_status === 'blocked') {
                        $blocked++;
                    }

                    if ($updatedClient->risk_status === 'watchlist') {
                        $watchlist++;
                    }
                }
            });

        return redirect()
            ->route('admin.clients.index', ['risk_status' => 'blocked'])
            ->with('success', "Analyse terminée : {$scanned} client(s) analysé(s), {$blocked} bloqué(s), {$watchlist} à vérifier.");
    }

    public function show(Client $client)
    {
        $client = app(ClientRiskService::class)->apply($client);

        $client->load([
            'user',
            'prospect.assignedCommercial',
            'prospect.preferredCampus',
            'prospect.preferredSpaceType',
            'mainCampus',
            'attachments.uploader',
        ]);

        return view('admin.clients.show', [
            'client' => $client,
        ]);
    }

    public function edit(Client $client)
    {
        $client->load([
            'user',
            'prospect.assignedCommercial',
            'prospect.preferredCampus',
            'prospect.preferredSpaceType',
            'mainCampus',
            'attachments.uploader',
        ]);

        return view('admin.clients.edit', [
            'client' => $client,
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $this->normalizeClientData(
            $request->validate(array_merge($this->clientRules($client), [
                'status' => ['required', 'in:active,inactive'],
            ]), [
                'full_name.required' => 'Le nom complet est obligatoire.',
                'email.required' => 'L’email est obligatoire.',
                'email.email' => 'Veuillez saisir une adresse email valide.',
                'email.unique' => 'Cet email est déjà utilisé.',
                'client_type.required' => 'Veuillez choisir le type de client.',
                'first_name.required_if' => 'Le prénom est obligatoire pour une personne physique.',
                'last_name.required_if' => 'Le nom est obligatoire pour une personne physique.',
                'identity_document_type.required_if' => 'Le type de pièce est obligatoire pour une personne physique.',
                'identity_document_number.required_if' => 'Le numéro de pièce est obligatoire pour une personne physique.',
                'company_name.required_if' => 'La raison sociale est obligatoire pour une personne morale.',
                'legal_form.required_if' => 'La forme juridique est obligatoire pour une personne morale.',
                'ice_number.required_if' => 'L’ICE est obligatoire pour une personne morale.',
                'legal_representative_full_name.required_if' => 'Le représentant légal est obligatoire pour une personne morale.',
                'legal_representative_identity_document_type.required_if' => 'Le type de pièce du représentant légal est obligatoire.',
                'legal_representative_identity_document_number.required_if' => 'Le numéro de pièce du représentant légal est obligatoire.',
            ])
        );

        $validated['joined_at'] = $validated['joined_at'] ?? now()->toDateString();
        $validated['registered_at'] = $validated['registered_at'] ?? now()->toDateString();

        DB::transaction(function () use ($client, $validated) {
            $client->user->update([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);

            $client->update([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'main_campus_id' => $validated['main_campus_id'] ?? null,
                'joined_at' => $validated['joined_at'],
                'registered_at' => $validated['registered_at'] ?? now()->toDateString(),
                'status' => $validated['status'],
                'billing_info' => $validated['billing_info'] ?? null,
                'notes' => $validated['notes'] ?? null,

                'client_type' => $validated['client_type'],

                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'identity_document_type' => $validated['identity_document_type'] ?? null,
                'identity_document_number' => $validated['identity_document_number'] ?? null,
                'nationality' => $validated['nationality'] ?? null,

                'billing_email' => $validated['billing_email'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? 'Maroc',

                'legal_form' => $validated['legal_form'] ?? null,
                'ice_number' => $validated['ice_number'] ?? null,
                'if_number' => $validated['if_number'] ?? null,
                'rc_number' => $validated['rc_number'] ?? null,
                'patente_number' => $validated['patente_number'] ?? null,
                'cnss_number' => $validated['cnss_number'] ?? null,
                'headquarters_address' => $validated['headquarters_address'] ?? null,

                'legal_representative_full_name' => $validated['legal_representative_full_name'] ?? null,
                'legal_representative_identity_document_type' => $validated['legal_representative_identity_document_type'] ?? null,
                'legal_representative_identity_document_number' => $validated['legal_representative_identity_document_number'] ?? null,
                'legal_representative_phone' => $validated['legal_representative_phone'] ?? null,
                'legal_representative_email' => $validated['legal_representative_email'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Informations client modifiées avec succès.');
    }

    public function deactivate(Client $client)
    {
        $client->update([
            'status' => 'inactive',
        ]);

        $client->user->update([
            'must_change_password' => false,
        ]);

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Compte client désactivé.');
    }

    public function reactivate(Client $client)
    {
        $client->update([
            'status' => 'active',
            'blocked_at' => null,
            'blocked_by' => null,
            'block_reason' => null,
        ]);

        return back()->with('success', 'Client réactivé avec succès.');

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Compte client réactivé.');
    }

    public function resetPassword(Client $client)
    {
        $temporaryPassword = 'HD-' . Str::upper(Str::random(8));

        $client->user->update([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Mot de passe réinitialisé avec succès.')
            ->with('temporary_password', $temporaryPassword);
    }

    public function updateLegalFile(Request $request, Client $client)
    {
        $validated = $request->validate([
            'legal_file_status' => ['required', 'in:complete,incomplete'],
            'legal_file_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $client->update([
            'legal_file_status' => $validated['legal_file_status'],
            'legal_file_completed_at' => $validated['legal_file_status'] === 'complete'
                ? now()
                : null,
            'legal_file_notes' => $validated['legal_file_notes'] ?? $client->legal_file_notes,
        ]);

        return back()->with('success', 'Legal file status updated successfully.');
    }

    private function attachmentRules(): array
    {
        return [
            'attachments' => ['nullable', 'array'],
            'attachments.*.document_type' => [
                'nullable',
                'in:cin_recto,cin_verso,passeport,carte_sejour,ice,rc,patente,cnss,contrat,facture,autre',
            ],
            'attachments.*.title' => ['nullable', 'string', 'max:255'],
            'attachments.*.notes' => ['nullable', 'string', 'max:1000'],
            'attachments.*.file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    private function storeInitialAttachments(Request $request, Client $client): void
    {
        foreach ($request->file('attachments', []) as $index => $attachmentData) {
            if (! isset($attachmentData['file'])) {
                continue;
            }

            $file = $attachmentData['file'];
            $input = $request->input("attachments.$index", []);

            $path = $file->store("client-attachments/{$client->id}", 'local');

            $client->attachments()->create([
                'uploaded_by' => Auth::id(),
                'document_type' => $input['document_type'] ?? 'autre',
                'title' => $input['title'] ?? null,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'notes' => $input['notes'] ?? null,
            ]);
        }
    }

    public function blockForPayment(Request $request, Client $client)
    {
        $reasons = [
            'late_payment' => 'Paiement en retard non régularisé',
            'unpaid_invoice' => 'Facture impayée',
            'repeated_payment_delay' => 'Retards de paiement répétés',
            'payment_promise_not_respected' => 'Promesse de paiement non respectée',
            'pending_regularization' => 'En attente de régularisation',
        ];

        $data = $request->validate([
            'block_reason' => ['required', Rule::in(array_keys($reasons))],
            'block_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = $reasons[$data['block_reason']];

        if (! empty($data['block_note'])) {
            $reason .= ' — ' . $data['block_note'];
        }

        $client->update([
            'status' => 'payment_hold',
            'blocked_at' => now(),
            'blocked_by' => Auth::id(),
            'block_reason' => $reason,
        ]);

        return back()->with('success', 'Client bloqué pour impayé avec succès.');
    }

    public function ban(Request $request, Client $client)
    {
        $reasons = [
            'repeated_unpaid_reservations' => 'Réservations répétées sans paiement',
            'fake_identity_attempt' => 'Tentative d’utilisation d’une fausse identité',
            'abusive_behavior' => 'Comportement abusif',
            'fraud_suspicion' => 'Suspicion de fraude',
            'management_decision' => 'Décision administrative',
        ];

        $data = $request->validate([
            'block_reason' => ['required', Rule::in(array_keys($reasons))],
            'block_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = $reasons[$data['block_reason']];

        if (! empty($data['block_note'])) {
            $reason .= ' — ' . $data['block_note'];
        }

        $client->update([
            'status' => 'banned',
            'blocked_at' => now(),
            'blocked_by' => Auth::id(),
            'block_reason' => $reason,
        ]);

        return back()->with('success', 'Client banni avec succès.');
    }
}
