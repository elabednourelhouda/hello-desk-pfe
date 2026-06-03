<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            $query->where('status', $request->status);
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

        $clients = $query->paginate(10)->withQueryString();

        return view('admin.clients.index', [
            'clients' => $clients,
            'activeCount' => Client::where('status', 'active')->count(),
            'inactiveCount' => Client::where('status', 'inactive')->count(),
            'totalCount' => Client::count(),
        ]);
    }

    public function create()
    {
        $campuses = Campus::orderBy('name')->get();

        return view('admin.clients.create', compact('campuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:clients,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'main_campus_id' => ['nullable', 'exists:campuses,id'],
            'registered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

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
            ]);
        });

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Client créé avec succès.')
            ->with('temporary_password', $temporaryPassword);
    }

    public function show(Client $client)
    {
        $client->load(['user', 'prospect', 'mainCampus']);

        return view('admin.clients.show', [
            'client' => $client,
        ]);
    }

    public function edit(Client $client)
    {
        $client->load(['user', 'prospect', 'mainCampus']);

        return view('admin.clients.edit', [
            'client' => $client,
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($client->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'main_campus_id' => ['nullable', 'exists:campuses,id'],
            'joined_at' => ['nullable', 'date'],
            'registered_at' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'billing_info' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ], [
            'full_name.required' => 'Le nom complet est obligatoire.',
            'email.required' => 'L’email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
        ]);

        $validated['joined_at'] = $validated['joined_at'] ?? now()->toDateString();
        $validated['registered_at'] = $validated['registered_at'] ?? now()->toDateString();

        DB::transaction(function () use ($client, $validated) {
            $client->user->update([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
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
        ]);

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
}