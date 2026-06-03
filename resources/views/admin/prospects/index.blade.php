@extends('layouts.app')

@section('title', 'Prospects - Administration')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-6 py-8">

        {{-- Header --}}
        <div class="mb-8 overflow-hidden rounded-3xl bg-[#284625] p-8 text-white shadow-sm">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-white/70">
                        CRM Hello Desk
                    </p>

                    <h1 class="mt-2 text-3xl font-bold">
                        Gestion des prospects
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/75">
                        Suivez les personnes intéressées, filtrez les prospects actifs,
                        consultez leurs dossiers et convertissez-les en clients.
                    </p>
                </div>

                <a href="{{ route('admin.prospects.create') }}"
                   class="inline-flex h-12 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#284625] shadow-sm transition hover:bg-slate-100">
                    + Ajouter un prospect
                </a>
            </div>
        </div>

        {{-- Messages --}}
        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-700">
                {{ session('info') }}
            </div>
        @endif

        {{-- CRM Tabs --}}
        <div class="mb-6 grid gap-4 md:grid-cols-4">
            <a href="{{ route('admin.prospects.index', ['view' => 'active']) }}"
               class="rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md
               {{ $view === 'active'
                    ? 'border-[#284625] bg-[#284625] text-white'
                    : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold">Actifs</p>
                    <span class="h-3 w-3 rounded-full {{ $view === 'active' ? 'bg-white' : 'bg-[#284625]' }}"></span>
                </div>
                <p class="mt-3 text-3xl font-bold">{{ $counts['active'] }}</p>
                <p class="mt-1 text-xs {{ $view === 'active' ? 'text-white/70' : 'text-slate-400' }}">
                    En cours de suivi
                </p>
            </a>

            <a href="{{ route('admin.prospects.index', ['view' => 'converted']) }}"
               class="rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md
               {{ $view === 'converted'
                    ? 'border-emerald-600 bg-emerald-600 text-white'
                    : 'border-emerald-100 bg-white text-slate-700 hover:bg-emerald-50' }}">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold">Convertis</p>
                    <span class="h-3 w-3 rounded-full {{ $view === 'converted' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                </div>
                <p class="mt-3 text-3xl font-bold">{{ $counts['converted'] }}</p>
                <p class="mt-1 text-xs {{ $view === 'converted' ? 'text-white/70' : 'text-slate-400' }}">
                    Devenus clients
                </p>
            </a>

            <a href="{{ route('admin.prospects.index', ['view' => 'lost']) }}"
               class="rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md
               {{ $view === 'lost'
                    ? 'border-red-600 bg-red-600 text-white'
                    : 'border-red-100 bg-white text-slate-700 hover:bg-red-50' }}">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold">Perdus</p>
                    <span class="h-3 w-3 rounded-full {{ $view === 'lost' ? 'bg-white' : 'bg-red-500' }}"></span>
                </div>
                <p class="mt-3 text-3xl font-bold">{{ $counts['lost'] }}</p>
                <p class="mt-1 text-xs {{ $view === 'lost' ? 'text-white/70' : 'text-slate-400' }}">
                    Non retenus
                </p>
            </a>

            <a href="{{ route('admin.prospects.index', ['view' => 'all']) }}"
               class="rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md
               {{ $view === 'all'
                    ? 'border-slate-800 bg-slate-800 text-white'
                    : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold">Tous</p>
                    <span class="h-3 w-3 rounded-full {{ $view === 'all' ? 'bg-white' : 'bg-slate-500' }}"></span>
                </div>
                <p class="mt-3 text-3xl font-bold">{{ $counts['all'] }}</p>
                <p class="mt-1 text-xs {{ $view === 'all' ? 'text-white/70' : 'text-slate-400' }}">
                    Historique complet
                </p>
            </a>
        </div>

        {{-- Filters --}}
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form id="prospectFilters"
                method="GET"
                action="{{ route('admin.prospects.index') }}#prospects-list"
                class="grid gap-4 md:grid-cols-5">

                <input type="hidden" name="view" value="{{ $view }}">

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
                        Campus préféré
                    </label>
                    <select name="preferred_campus_id"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les campus</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected(request('preferred_campus_id') == $campus->id)>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Statut CRM
                    </label>
                    <select name="crm_status"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les statuts</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(request('crm_status') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                        Appliquer
                    </button>
                </div>
            </form>

            <div class="mt-4 flex items-center justify-between gap-3">
                <p class="text-xs text-slate-400">
                    Choisissez vos filtres puis cliquez sur “Appliquer”.
                </p>

                <a href="{{ route('admin.prospects.index', ['view' => $view]) }}#prospects-list"
                class="text-sm font-semibold text-slate-500 hover:text-[#284625] hover:underline">
                    Réinitialiser les filtres
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div id="prospects-list" class="scroll-mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-white px-5 py-4">
                <h2 class="text-lg font-bold text-slate-900">
                    Liste des prospects
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Cliquez sur “Dossier” pour voir les informations complètes et convertir le prospect.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Prospect</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Contact</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Besoin</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Campus</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Statut</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Commercial</th>
                            <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($prospects as $prospect)
                            @php
                                $statusClasses = [
                                    'new' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
                                    'contacted' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                    'visit_scheduled' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                    'visited' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
                                    'proposal_sent' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                    'negotiation' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
                                    'converted' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                    'lost' => 'bg-red-50 text-red-700 ring-red-600/20',
                                ];

                                $statusDotClasses = [
                                    'new' => 'bg-slate-400',
                                    'contacted' => 'bg-blue-500',
                                    'visit_scheduled' => 'bg-indigo-500',
                                    'visited' => 'bg-purple-500',
                                    'proposal_sent' => 'bg-amber-500',
                                    'negotiation' => 'bg-orange-500',
                                    'converted' => 'bg-emerald-500',
                                    'lost' => 'bg-red-500',
                                ];
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#284625]/10 text-sm font-bold text-[#284625]">
                                            {{ strtoupper(substr($prospect->full_name, 0, 1)) }}
                                        </div>

                                        <div>
                                            <div class="font-bold text-slate-900">
                                                {{ $prospect->full_name }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ $prospect->company_name ?? 'Sans entreprise' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-700">
                                    <div class="font-medium">{{ $prospect->email ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $prospect->phone ?? '-' }}</div>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-700">
                                    {{ \Illuminate\Support\Str::limit($prospect->need ?? '-', 45) }}
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-700">
                                    {{ $prospect->preferredCampus->name ?? '-' }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClasses[$prospect->crm_status] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20' }}">
                                        <span class="h-2 w-2 rounded-full {{ $statusDotClasses[$prospect->crm_status] ?? 'bg-slate-400' }}"></span>
                                        {{ $statuses[$prospect->crm_status] ?? $prospect->crm_status }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-700">
                                    {{ $prospect->assignedCommercial->name ?? 'Non affecté' }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.prospects.show', ['prospect' => $prospect, 'return_url' => request()->fullUrl()]) }}"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                                            Dossier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            +
                                        </div>
                                        <p class="mt-4 font-bold text-slate-700">
                                            Aucun prospect trouvé
                                        </p>
                                        <p class="mt-1 text-sm text-slate-500">
                                            Ajoutez un prospect ou changez les filtres sélectionnés.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-4">
                {{ $prospects->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    const filterForm = document.getElementById('prospectFilters');
    const autoFilters = document.querySelectorAll('.filter-auto');
    const searchInput = document.querySelector('.filter-search');

    autoFilters.forEach((filter) => {
        filter.addEventListener('change', () => {
            filterForm.submit();
        });
    });

    let searchTimer;

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);

            searchTimer = setTimeout(() => {
                filterForm.submit();
            }, 600);
        });
    }
</script>
@endsection