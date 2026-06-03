@extends('layouts.app')

@section('title', 'Clients - Administration')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-6 py-8">

        <div class="mb-8 rounded-3xl bg-[#284625] p-8 text-white shadow-sm">
            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-white/70">
                        Administration
                    </p>

                    <h1 class="mt-2 text-3xl font-bold">
                        Gestion des clients
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/75">
                        Liste des clients créés après conversion des prospects ou ajoutés directement.
                    </p>
                </div>

                <a href="{{ route('admin.clients.create') }}"
                class="inline-flex h-12 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#284625] shadow-sm hover:bg-slate-100">
                    + Ajouter client
                </a>
            </div>
        </div>

        <div class="mb-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">Clients actifs</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $activeCount }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">Clients inactifs</p>
                <p class="mt-2 text-3xl font-bold text-red-600">{{ $inactiveCount }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">Total clients</p>
                <p class="mt-2 text-3xl font-bold text-[#284625]">{{ $totalCount }}</p>
            </div>
        </div>

        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.clients.index') }}#clients-list" class="grid gap-4 md:grid-cols-4">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Recherche
                    </label>
                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nom, email, téléphone..."
                        class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Statut
                    </label>
                    <select name="status"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white shadow-sm hover:bg-[#1f351d]">
                        Appliquer
                    </button>
                </div>
            </form>

            <div class="mt-4 text-right">
                <a href="{{ route('admin.clients.index') }}#clients-list"
                class="text-sm font-semibold text-slate-500 hover:text-[#284625] hover:underline">
                    Réinitialiser les filtres
                </a>
            </div>
        </div>

        <div id="clients-list" class="scroll-mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-bold text-slate-900">Liste des clients</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase text-slate-500">Client</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase text-slate-500">Contact</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase text-slate-500">Campus</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase text-slate-500">Statut</th>
                            <th class="px-5 py-4 text-right text-xs font-bold uppercase text-slate-500">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($clients as $client)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900">{{ $client->full_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $client->company_name ?? 'Sans entreprise' }}</div>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-700">
                                    <div>{{ $client->email }}</div>
                                    <div class="text-xs text-slate-500">{{ $client->phone ?? '-' }}</div>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-700">
                                    {{ $client->mainCampus->name ?? '-' }}
                                </td>

                                <td class="px-5 py-4">
                                    @if($client->status === 'active')
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                                            Actif
                                        </span>
                                    @else
                                        <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700 ring-1 ring-red-200">
                                            Inactif
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.clients.show', $client) }}"
                                        class="inline-flex rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100">
                                            Voir
                                        </a>

                                        <a href="{{ route('admin.clients.edit', $client) }}"
                                        class="inline-flex rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-bold text-blue-700 hover:bg-blue-100">
                                            Modifier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">
                                    Aucun client trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-4">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
</div>
@endsection