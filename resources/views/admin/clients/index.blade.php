@extends('layouts.app')

@section('title', 'Clients - Administration')

@section('content')
@php
$safeTotal = max($totalCount ?? 0, 1);

$activeRate = round((($activeCount ?? 0) / $safeTotal) * 100);
$inactiveRate = round((($inactiveCount ?? 0) / $safeTotal) * 100);
$paymentHoldCount = $paymentHoldCount ?? 0;
$bannedCount = $bannedCount ?? 0;
$clearRiskCount = $clearRiskCount ?? 0;
$watchlistRiskCount = $watchlistRiskCount ?? 0;
$blockedRiskCount = $blockedRiskCount ?? 0;
$riskyClientCount = $watchlistRiskCount + $blockedRiskCount;
@endphp

<div class="min-h-screen bg-[#f6f8f6]">
    <div class="mx-auto max-w-7xl px-6 py-8">

        {{-- Header --}}
        <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="grid gap-0 lg:grid-cols-[1.35fr_0.65fr]">
                <div class="bg-gradient-to-br from-[#284625] via-[#315f32] to-[#4b7f3f] p-8 text-white">
                    <p class="text-sm font-semibold uppercase tracking-wide text-white/75">
                        Administration
                    </p>

                    <h1 class="mt-3 text-3xl font-bold">
                        Gestion des clients
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                        Consultez les clients créés après conversion des prospects ou ajoutés directement,
                        avec leur statut, leur campus principal et leurs informations de contact.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('admin.clients.create') }}"
                            class="inline-flex h-11 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#284625] shadow-sm transition hover:bg-gray-100">
                            Ajouter un client
                        </a>

                        <a href="#clients-list"
                            class="inline-flex h-11 items-center justify-center rounded-xl border border-white/25 bg-white/10 px-5 text-sm font-bold text-white transition hover:bg-white/15">
                            Voir la liste
                        </a>

                        <form method="POST" action="{{ route('admin.clients.analyzeRisks') }}">
                            @csrf
                            <button type="submit"
                                class="inline-flex h-11 items-center justify-center rounded-xl border border-white/25 bg-white/10 px-5 text-sm font-bold text-white transition hover:bg-white/15">
                                Analyser les risques
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Mini summary --}}
                <div class="bg-white p-8">
                    <h2 class="text-lg font-bold text-gray-900">
                        État des clients
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Répartition actuelle des comptes clients.
                    </p>

                    <div class="mt-6 space-y-5">
                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Clients actifs</span>
                                <span class="font-bold text-emerald-600">{{ $activeRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="progress-bar h-full rounded-full bg-emerald-500" data-progress="{{ $activeRate }}"></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Clients non actifs</span>
                                <span class="font-bold text-rose-600">{{ $inactiveRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="progress-bar h-full rounded-full bg-rose-500" data-progress="{{ $inactiveRate }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- KPI cards --}}
        <section class="mt-8 grid gap-4 md:grid-cols-4">
            <a href="{{ route('admin.clients.index', ['status' => 'active']) }}#clients-list"
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
                    <div class="progress-bar h-1.5 rounded-full bg-emerald-500" data-progress="{{ $activeRate }}"></div>
                </div>
            </a>

            <a href="{{ route('admin.clients.index', ['status' => 'inactive']) }}#clients-list"
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Clients inactifs</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $inactiveCount }}</p>

                        <p class="mt-1 text-xs text-gray-500">
                            {{ $paymentHoldCount }} bloqué(s) paiement · {{ $bannedCount }} banni(s)
                        </p>
                    </div>

                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">
                        Inactifs
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="progress-bar h-1.5 rounded-full bg-rose-500" data-progress="{{ $inactiveRate }}"></div>
                </div>
            </a>

            <a href="{{ route('admin.clients.index') }}#clients-list"
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

            <a href="{{ route('admin.clients.index', ['risk_status' => 'blocked']) }}#clients-list"
                class="rounded-2xl border border-red-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Clients à risque</p>
                        <p class="mt-3 text-3xl font-bold text-red-700">{{ $riskyClientCount }}</p>

                        <p class="mt-1 text-xs text-gray-500">
                            {{ $blockedRiskCount }} bloqué(s) · {{ $watchlistRiskCount }} à vérifier
                        </p>
                    </div>

                    <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">
                        Risque
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="progress-bar h-1.5 rounded-full bg-red-500" data-progress="{{ min($riskyClientCount * 10, 100) }}"></div>
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
                        Filtrez les clients par nom, email, téléphone, type ou état du dossier juridique.
                    </p>
                </div>

                <a href="{{ route('admin.clients.index') }}#clients-list"
                    class="text-sm font-bold text-slate-500 transition hover:text-[#284625] hover:underline">
                    Réinitialiser
                </a>
            </div>

            <form id="clientFilters"
                method="GET"
                action="{{ route('admin.clients.index') }}#clients-list"
                class="grid gap-4 lg:grid-cols-[1.5fr_1fr_1fr_1fr_1fr_auto]">

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
                        <option value="active" @selected(request('status')==='active' )>Actif</option>
                        <option value="inactive" @selected(request('status')==='inactive' )>
                            Non actif / bloqué / banni
                        </option>
                        <option value="payment_hold" @selected(request('status')==='payment_hold' )>
                            Bloqué paiement uniquement
                        </option>
                        <option value="banned" @selected(request('status')==='banned' )>
                            Banni uniquement
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Type de client
                    </label>
                    <select name="client_type"
                        class="filter-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les types</option>
                        <option value="physique" @selected(request('client_type')==='physique' )>
                            Personne physique
                        </option>
                        <option value="morale" @selected(request('client_type')==='morale' )>
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
                        <option value="complete" @selected(request('legal_status')==='complete' )>
                            Complet
                        </option>
                        <option value="incomplete" @selected(request('legal_status')==='incomplete' )>
                            À compléter
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Risque
                    </label>
                    <select name="risk_status"
                        class="filter-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les risques</option>
                        <option value="clear" @selected(request('risk_status')==='clear' )>
                            Aucun risque
                        </option>
                        <option value="watchlist" @selected(request('risk_status')==='watchlist' )>
                            À vérifier
                        </option>
                        <option value="blocked" @selected(request('risk_status')==='blocked' )>
                            Bloqué
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
                            Consultez les dossiers clients, leurs coordonnées et leur campus principal.
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
                                Campus principal
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

                                            @php
                                            $indexLegalComplete = $client->hasCompleteLegalFile();
                                            @endphp

                                            @if($indexLegalComplete)
                                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700">
                                                Dossier complet
                                            </span>
                                            @else
                                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700">
                                                À compléter
                                            </span>
                                            @endif

                                            @if($client->risk_status === 'blocked')
                                            <span class="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-bold text-red-700 ring-1 ring-red-200">
                                                Risque fraude
                                            </span>
                                            @elseif($client->risk_status === 'watchlist')
                                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700 ring-1 ring-amber-200">
                                                À vérifier
                                            </span>
                                            @else
                                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-200">
                                                Risque OK
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
                                @php
                                $indexStatusLabel = match($client->status) {
                                'active' => 'Actif',
                                'inactive' => 'Inactif',
                                'payment_hold' => 'Bloqué paiement',
                                'banned' => 'Banni',
                                default => ucfirst(str_replace('_', ' ', $client->status ?? '')),
                                };

                                $indexStatusBadgeClass = match($client->status) {
                                'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                'inactive' => 'bg-slate-100 text-slate-700 ring-slate-300',
                                'payment_hold' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                'banned' => 'bg-red-50 text-red-700 ring-red-600/20',
                                default => 'bg-slate-100 text-slate-700 ring-slate-300',
                                };

                                $indexStatusDotClass = match($client->status) {
                                'active' => 'bg-emerald-500',
                                'inactive' => 'bg-slate-500',
                                'payment_hold' => 'bg-amber-500',
                                'banned' => 'bg-red-500',
                                default => 'bg-slate-500',
                                };
                                @endphp

                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $indexStatusBadgeClass }}">
                                    <span class="h-2 w-2 rounded-full {{ $indexStatusDotClass }}"></span>
                                    {{ $indexStatusLabel }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.clients.show', $client) }}"
                                        class="inline-flex h-9 items-center justify-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-bold text-gray-700 transition hover:bg-gray-100">
                                        Dossier
                                    </a>

                                    <a href="{{ route('admin.clients.edit', $client) }}"
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
                                        Ajoutez un client ou modifiez les filtres sélectionnés.
                                    </p>

                                    <a href="{{ route('admin.clients.create') }}"
                                        class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                                        Ajouter un client
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
    document.querySelectorAll('.progress-bar').forEach((bar) => {
        const progress = Number(bar.dataset.progress || 0);
        bar.style.width = `${Math.min(Math.max(progress, 0), 100)}%`;
    });

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