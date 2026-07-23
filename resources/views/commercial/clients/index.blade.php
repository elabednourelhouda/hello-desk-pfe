@extends('layouts.app')

@section('title', 'Mes clients - Hello Desk')

@section('content')
@php
    $totalCount = $counts['all'] ?? 0;
    $activeCount = $counts['active'] ?? 0;
    $inactiveCount = $counts['inactive'] ?? 0;

    $safeTotal = max($totalCount, 1);
    $activeRate = round(($activeCount / $safeTotal) * 100);
    $inactiveRate = round(($inactiveCount / $safeTotal) * 100);
@endphp

<div class="min-h-screen bg-[#f6f8f6]">
    <div class="mx-auto max-w-7xl px-6 py-8">

        {{-- Header --}}
        <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="grid gap-0 lg:grid-cols-[1.35fr_0.65fr]">
                <div class="bg-gradient-to-br from-[#284625] via-[#315f32] to-[#4b7f3f] p-8 text-white">
                    <p class="text-sm font-semibold uppercase tracking-wide text-white/75">
                        Gestion commerciale
                    </p>

                    <h1 class="mt-3 text-3xl font-bold">
                        Mes clients
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                        Consultez les clients liés à votre périmètre commercial, leurs informations
                        de contact, leur site principal et l’état de leur dossier juridique.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('commercial.clients.create') }}"
                        class="inline-flex h-11 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#284625] shadow-sm transition hover:bg-gray-100">
                            Ajouter un client
                        </a>

                        <a href="#clients-list"
                           class="inline-flex h-11 items-center justify-center rounded-xl border border-white/25 bg-white/10 px-5 text-sm font-bold text-white transition hover:bg-white/15">
                            Voir la liste
                        </a>
                    </div>
                </div>

                {{-- Mini summary --}}
                <div class="bg-white p-8">
                    <h2 class="text-lg font-bold text-gray-900">
                        État des clients
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Répartition des clients accessibles à ce commercial.
                    </p>

                    <div class="mt-6 space-y-5">
                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Clients actifs</span>
                                <span class="font-bold text-emerald-600">{{ $activeRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-emerald-500" @style(['width: ' . $activeRate . '%'])></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Clients inactifs</span>
                                <span class="font-bold text-rose-600">{{ $inactiveRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-rose-500" @style(['width: ' . $inactiveRate . '%'])></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Limited access notice --}}
        @if($assignedCampuses->isNotEmpty())
            <div class="mt-6 rounded-xl border border-[#284625]/20 bg-[#284625]/5 px-4 py-3 text-sm text-[#284625]">
                <span class="font-bold">Votre périmètre :</span>
                {{ $assignedCampuses->pluck('name')->join(', ') }}
            </div>
        @else
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Aucune affectation site trouvée. Les clients affichés restent limités à votre compte commercial ou aux données accessibles pour la démonstration.
            </div>
        @endif

        {{-- Messages --}}
        @if(session('success'))
            <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- KPI cards --}}
        <section class="mt-8 grid gap-4 md:grid-cols-3">
            <a href="{{ route('commercial.clients.index', ['status' => 'active']) }}#clients-list"
               class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Clients actifs</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $activeCount }}</p>
                    </div>

                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                        Actifs
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-emerald-500" @style(['width: ' . $activeRate . '%'])></div>
                </div>
            </a>

            <a href="{{ route('commercial.clients.index', ['status' => 'inactive']) }}#clients-list"
               class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Clients inactifs</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $inactiveCount }}</p>
                    </div>

                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">
                        Inactifs
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-rose-500" @style(['width: ' . $inactiveRate . '%'])></div>
                </div>
            </a>

            <a href="{{ route('commercial.clients.index') }}#clients-list"
               class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Total clients</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $totalCount }}</p>
                    </div>

                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">
                        Global
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 w-full rounded-full bg-sky-500"></div>
                </div>
            </a>
        </section>

        {{-- Filters --}}
        <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        Recherche et filtres
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Filtrez vos clients par nom, email, téléphone, type ou état du dossier juridique.
                    </p>
                </div>

                <a href="{{ route('commercial.clients.index') }}#clients-list"
                   class="text-sm font-bold text-slate-500 transition hover:text-[#284625] hover:underline">
                    Réinitialiser
                </a>
            </div>

            <form id="clientFilters"
                  method="GET"
                  action="{{ route('commercial.clients.index') }}#clients-list"
                  class="grid gap-4 lg:grid-cols-[1.5fr_1fr_1fr_1fr_auto]">

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Recherche
                    </label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Nom, email, téléphone..."
                           class="filter-search h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Statut
                    </label>
                    <select name="status"
                            class="filter-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les statuts</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Type de client
                    </label>
                    <select name="client_type"
                            class="filter-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les types</option>
                        <option value="physique" @selected(request('client_type') === 'physique')>
                            Personne physique
                        </option>
                        <option value="morale" @selected(request('client_type') === 'morale')>
                            Personne morale
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Dossier juridique
                    </label>
                    <select name="legal_status"
                            class="filter-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les dossiers</option>
                        <option value="complete" @selected(request('legal_status') === 'complete')>
                            Complet
                        </option>
                        <option value="incomplete" @selected(request('legal_status') === 'incomplete')>
                            À compléter
                        </option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                        Appliquer
                    </button>
                </div>
            </form>
        </section>

        {{-- List --}}
        <section id="clients-list" class="mt-8 scroll-mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Liste des clients
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Cette liste affiche uniquement les clients accessibles à votre compte commercial.
                        </p>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                        {{ $clients->total() }} résultat(s)
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                Client
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                Contact
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                Site principal
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                Statut
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wide text-gray-500">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($clients as $client)
                            <tr class="transition hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sm font-bold text-sky-700 ring-1 ring-sky-100">
                                            {{ strtoupper(substr($client->full_name, 0, 1)) }}
                                        </div>

                                        <div>
                                            <p class="font-bold text-gray-900">
                                                {{ $client->full_name }}
                                            </p>

                                            <div class="mt-1 flex flex-wrap gap-1.5">
                                                @if($client->client_type === 'physique')
                                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600">
                                                        Personne physique
                                                    </span>
                                                @elseif($client->client_type === 'morale')
                                                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-700">
                                                        Personne morale
                                                    </span>
                                                @else
                                                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700">
                                                        Type non renseigné
                                                    </span>
                                                @endif

                                                @if($client->hasCompleteLegalFile())
                                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700">
                                                        Dossier complet
                                                    </span>
                                                @else
                                                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700">
                                                        À compléter
                                                    </span>
                                                @endif
                                            </div>

                                            @if($client->company_name)
                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $client->company_name }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <p class="font-medium">
                                        {{ $client->email }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ $client->phone ?? '-' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $client->mainCampus->name ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    @if($client->status === 'active')
                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-600/20">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            Actif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 ring-1 ring-rose-600/20">
                                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                            Inactif
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('commercial.clients.show', $client) }}"
                                           class="inline-flex h-9 items-center justify-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-bold text-gray-700 transition hover:bg-gray-100">
                                            Dossier
                                        </a>

                                        <a href="{{ route('commercial.clients.edit', $client) }}"
                                           class="inline-flex h-9 items-center justify-center rounded-xl bg-sky-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-sky-700">
                                            Modifier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl font-bold text-slate-400">
                                            +
                                        </div>

                                        <p class="mt-4 font-bold text-gray-800">
                                            Aucun client trouvé
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            Les clients convertis depuis vos prospects apparaîtront ici.
                                        </p>

                                        <a href="{{ route('commercial.prospects.index') }}"
                                           class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                                            Voir les prospects
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($clients->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $clients->links() }}
                </div>
            @endif
        </section>
    </div>
</div>

<script>
    const clientFilterForm = document.getElementById('clientFilters');
    const clientAutoFilters = document.querySelectorAll('.filter-auto');
    const clientSearchInput = document.querySelector('.filter-search');

    clientAutoFilters.forEach((filter) => {
        filter.addEventListener('change', () => {
            clientFilterForm.submit();
        });
    });

    let clientSearchTimer;

    if (clientSearchInput) {
        clientSearchInput.addEventListener('input', () => {
            clearTimeout(clientSearchTimer);

            clientSearchTimer = setTimeout(() => {
                clientFilterForm.submit();
            }, 600);
        });
    }
</script>
@endsection