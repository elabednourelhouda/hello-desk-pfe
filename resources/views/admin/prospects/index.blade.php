@extends('layouts.app')

@section('title', 'Prospects - Administration')

@section('content')
@php
    $total = max($counts['all'] ?? 0, 1);
    $activeRate = round((($counts['active'] ?? 0) / $total) * 100);
    $convertedRate = round((($counts['converted'] ?? 0) / $total) * 100);
    $lostRate = round((($counts['lost'] ?? 0) / $total) * 100);
@endphp

<div class="min-h-screen bg-[#f6f8f6]">
    <div class="mx-auto max-w-7xl px-6 py-8">

        {{-- Header --}}
        <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="grid gap-0 lg:grid-cols-[1.35fr_0.65fr]">
                <div class="bg-gradient-to-br from-[#284625] via-[#315f32] to-[#4b7f3f] p-8 text-white">
                    <p class="text-sm font-semibold uppercase tracking-wide text-white/75">
                        CRM Hello Desk
                    </p>

                    <h1 class="mt-3 text-3xl font-bold">
                        Gestion des prospects
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                        Suivez les prospects, analysez leur avancement commercial et accédez rapidement
                        aux dossiers et au suivi CRM.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('admin.prospects.create') }}"
                           class="inline-flex h-11 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#284625] shadow-sm transition hover:bg-gray-100">
                            Ajouter un prospect
                        </a>

                        <a href="#prospects-list"
                           class="inline-flex h-11 items-center justify-center rounded-xl border border-white/25 bg-white/10 px-5 text-sm font-bold text-white transition hover:bg-white/15">
                            Voir la liste
                        </a>
                    </div>
                </div>

                {{-- Mini pipeline --}}
                <div class="bg-white p-8">
                    <h2 class="text-lg font-bold text-gray-900">
                        Pipeline CRM
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Répartition globale des prospects.
                    </p>

                    <div class="mt-6 space-y-4">
                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Actifs</span>
                                <span class="font-bold text-blue-600">{{ $activeRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-blue-500" @style(['width: ' . $activeRate . '%'])></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Convertis</span>
                                <span class="font-bold text-emerald-600">{{ $convertedRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-emerald-500" @style(['width: ' . $convertedRate . '%'])></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">abandonnés</span>
                                <span class="font-bold text-rose-600">{{ $lostRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-rose-500" @style(['width: ' . $lostRate . '%'])></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Messages --}}
        @if(session('success'))
            <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="mt-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-700">
                {{ session('info') }}
            </div>
        @endif

        {{-- CRM summary cards --}}
        <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('admin.prospects.index', ['view' => 'active']) }}"
               class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md
               {{ $view === 'active' ? 'ring-2 ring-blue-200' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Prospects actifs</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $counts['active'] }}</p>
                    </div>

                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                        Suivi
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-blue-500" @style(['width: ' . $activeRate . '%'])></div>
                </div>
            </a>

            <a href="{{ route('admin.prospects.index', ['view' => 'converted']) }}"
               class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md
               {{ $view === 'converted' ? 'ring-2 ring-emerald-200' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Convertis</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $counts['converted'] }}</p>
                    </div>

                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                        Clients
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-emerald-500" @style(['width: ' . $convertedRate . '%'])></div>
                </div>
            </a>

            <a href="{{ route('admin.prospects.index', ['view' => 'lost']) }}"
               class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md
               {{ $view === 'lost' ? 'ring-2 ring-rose-200' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">abandonnés</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $counts['lost'] }}</p>
                    </div>

                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">
                        Fermés
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-rose-500" @style(['width: ' . $lostRate . '%'])></div>
                </div>
            </a>

            <a href="{{ route('admin.prospects.index', ['view' => 'all']) }}"
               class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md
               {{ $view === 'all' ? 'ring-2 ring-slate-300' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Total prospects</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $counts['all'] }}</p>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                        Global
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-slate-500" style="width: 100%"></div>
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
                        Filtrez les prospects par contact, site ou statut CRM.
                    </p>
                </div>

                <a href="{{ route('admin.prospects.index', ['view' => $view]) }}#prospects-list"
                   class="text-sm font-bold text-slate-500 transition hover:text-[#284625] hover:underline">
                    Réinitialiser
                </a>
            </div>

            <form id="prospectFilters"
                  method="GET"
                  action="{{ route('admin.prospects.index') }}#prospects-list"
                  class="grid gap-4 lg:grid-cols-[1.4fr_1fr_1fr_auto]">

                <input type="hidden" name="view" value="{{ $view }}">

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
                        Site préféré
                    </label>
                    <select name="preferred_campus_id"
                            class="filter-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les sites</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected(request('preferred_campus_id') == $campus->id)>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Statut CRM
                    </label>
                    <select name="crm_status"
                            class="filter-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les statuts</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(request('crm_status') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
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
        <section id="prospects-list" class="mt-8 scroll-mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Liste des prospects
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Dossier = informations générales. Suivi CRM = visites, demandes et relances.
                        </p>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                        {{ $prospects->total() }} résultat(s)
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Prospect</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Besoin</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Site</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Statut</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Commercial</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($prospects as $prospect)
                            @php
                                $statusClasses = [
                                    'new' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
                                    'contacted' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                    'visit_planned' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                    'visit_done' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
                                    'visit_scheduled' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                    'visited' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
                                    'proposal_sent' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                    'negotiation' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
                                    'converted' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                    'lost' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                                ];

                                $statusDotClasses = [
                                    'new' => 'bg-slate-400',
                                    'contacted' => 'bg-blue-500',
                                    'visit_planned' => 'bg-indigo-500',
                                    'visit_done' => 'bg-purple-500',
                                    'visit_scheduled' => 'bg-indigo-500',
                                    'visited' => 'bg-purple-500',
                                    'proposal_sent' => 'bg-amber-500',
                                    'negotiation' => 'bg-orange-500',
                                    'converted' => 'bg-emerald-500',
                                    'lost' => 'bg-rose-500',
                                ];
                            @endphp

                            <tr class="transition hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sm font-bold text-sky-700 ring-1 ring-sky-100">
                                            {{ strtoupper(substr($prospect->full_name, 0, 1)) }}
                                        </div>

                                        <div>
                                            <p class="font-bold text-gray-900">
                                                {{ $prospect->full_name }}
                                            </p>
                                            <p class="mt-0.5 text-xs text-gray-500">
                                                {{ $prospect->company_name ?? 'Sans entreprise' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <p class="font-medium">
                                        {{ $prospect->email ?? '-' }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ $prospect->phone ?? '-' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ \Illuminate\Support\Str::limit($prospect->need ?? '-', 42) }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $prospect->preferredCampus->name ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClasses[$prospect->crm_status] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20' }}">
                                        <span class="h-2 w-2 rounded-full {{ $statusDotClasses[$prospect->crm_status] ?? 'bg-slate-400' }}"></span>
                                        {{ $statuses[$prospect->crm_status] ?? $prospect->crm_status }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $prospect->assignedCommercial->name ?? 'Non affecté' }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.prospects.show', ['prospect' => $prospect, 'return_url' => request()->fullUrl()]) }}"
                                           class="inline-flex h-9 items-center justify-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-bold text-gray-700 transition hover:bg-gray-100">
                                            Dossier
                                        </a>

                                        <a href="{{ route('admin.prospects.crm', ['prospect' => $prospect, 'return_url' => request()->fullUrl()]) }}"
                                           class="inline-flex h-9 items-center justify-center rounded-xl bg-sky-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-sky-700">
                                            Suivi
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-14 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl font-bold text-slate-400">
                                            +
                                        </div>

                                        <p class="mt-4 font-bold text-gray-800">
                                            Aucun prospect trouvé
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            Ajoutez un prospect ou modifiez les filtres sélectionnés.
                                        </p>

                                        <a href="{{ route('admin.prospects.create') }}"
                                           class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                                            Ajouter un prospect
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($prospects->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $prospects->links() }}
                </div>
            @endif
        </section>
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