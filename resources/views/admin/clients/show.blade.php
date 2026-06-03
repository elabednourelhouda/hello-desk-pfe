@extends('layouts.app')

@section('title', 'Détail client - Administration')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-6xl px-6 py-8">

        <div class="mb-6">
            <a href="{{ route('admin.clients.index') }}"
               class="text-sm font-semibold text-[#284625] hover:underline">
                ← Retour aux clients
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('temporary_password'))
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                <p class="font-semibold">Identifiants de connexion du client</p>

                <div class="mt-3 space-y-2">
                    <p>
                        Email :
                        <span class="rounded bg-white px-2 py-1 font-mono font-bold">
                            {{ $client->email }}
                        </span>
                    </p>

                    <p>
                        Mot de passe temporaire :
                        <span class="rounded bg-white px-2 py-1 font-mono font-bold">
                            {{ session('temporary_password') }}
                        </span>
                    </p>
                </div>

                <p class="mt-3 text-xs leading-5">
                    Copiez ces informations et envoyez-les au client manuellement. Le mot de passe ne sera affiché qu’une seule fois.
                </p>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[#284625]">Dossier client</p>
                            <h1 class="mt-1 text-3xl font-bold text-slate-900">
                                {{ $client->full_name }}
                            </h1>
                            <p class="mt-2 text-sm text-slate-500">
                                {{ $client->company_name ?? 'Sans entreprise' }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        @if($client->status === 'active')
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                                Actif
                            </span>
                        @else
                            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700 ring-1 ring-red-200">
                                Inactif
                            </span>
                        @endif

                            <a href="{{ route('admin.clients.edit', $client) }}"
                            class="inline-flex h-10 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-bold text-blue-700 transition hover:bg-blue-100">
                                Modifier
                            </a>
                        </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-5 text-lg font-bold text-slate-900">Informations client</h2>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Email</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $client->email }}</p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Téléphone</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $client->phone ?? '-' }}</p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Date d’entrée client</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ $client->registered_at ? $client->registered_at->format('d/m/Y') : $client->created_at->format('d/m/Y') }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Campus principal</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $client->mainCampus->name ?? '-' }}</p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Origine</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ $client->prospect ? 'Converti depuis un prospect' : 'Créé directement' }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold text-slate-900">Notes</h2>
                    <p class="text-sm leading-7 text-slate-700">
                        {{ $client->notes ?? 'Aucune note.' }}
                    </p>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">Compte de connexion</h2>

                    <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm">
                        <p class="text-xs font-bold uppercase text-slate-400">Email de connexion</p>
                        <p class="mt-1 font-medium text-slate-800">{{ $client->user->email }}</p>
                    </div>

                    <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm">
                        <p class="text-xs font-bold uppercase text-slate-400">Rôle</p>
                        <p class="mt-1 font-medium text-slate-800">{{ $client->user->role }}</p>
                    </div>

                    @if($client->user->must_change_password)
                        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            Le client utilise encore un mot de passe temporaire.
                        </div>
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">Actions</h2>

                    <form method="POST"
                        action="{{ route('admin.clients.resetPassword', $client) }}"
                        class="mt-4"
                        onsubmit="return confirm('Réinitialiser le mot de passe de ce client ?');">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                                class="inline-flex h-12 w-full items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 text-sm font-bold text-amber-700 hover:bg-amber-100">
                            Réinitialiser le mot de passe
                        </button>
                    </form>

                    @if($client->status === 'active')
                        <form method="POST"
                              action="{{ route('admin.clients.deactivate', $client) }}"
                              class="mt-4"
                              onsubmit="return confirm('Désactiver ce compte client ?');">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="inline-flex h-12 w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-bold text-red-700 hover:bg-red-100">
                                Désactiver le compte
                            </button>
                        </form>
                    @else
                        <form method="POST"
                              action="{{ route('admin.clients.reactivate', $client) }}"
                              class="mt-4">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="inline-flex h-12 w-full items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-bold text-emerald-700 hover:bg-emerald-100">
                                Réactiver le compte
                            </button>
                        </form>
                    @endif
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection