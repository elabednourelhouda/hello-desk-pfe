@extends('layouts.app')

@section('title', 'Contrats - Hello Desk')

@section('content')
@php
    $pageContracts = method_exists($contracts, 'getCollection')
        ? $contracts->getCollection()
        : collect($contracts);

    $totalContracts = method_exists($contracts, 'total')
        ? $contracts->total()
        : $pageContracts->count();

    $visibleCount = $pageContracts->count();

    $activeVisible = $pageContracts->where('status', 'active')->count();
    $draftVisible = $pageContracts->where('status', 'draft')->count();
    $expiredVisible = $pageContracts->where('status', 'expired')->count();
    $cancelledVisible = $pageContracts->where('status', 'cancelled')->count();

    $pdfImportedVisible = $pageContracts->filter(fn ($contract) => !empty($contract->pdf_path))->count();
    $pdfMissingVisible = max($visibleCount - $pdfImportedVisible, 0);

    $pdfImportedRate = $visibleCount > 0
        ? round(($pdfImportedVisible / $visibleCount) * 100)
        : 0;

    $pdfMissingRate = $visibleCount > 0
        ? round(($pdfMissingVisible / $visibleCount) * 100)
        : 0;

    $statusClasses = [
        'draft' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'expired' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
    ];

    $statusDots = [
        'draft' => 'bg-amber-500',
        'active' => 'bg-emerald-500',
        'expired' => 'bg-slate-500',
        'cancelled' => 'bg-rose-500',
    ];

    $statusLabels = [
        'draft' => 'Brouillon',
        'active' => 'Actif',
        'expired' => 'Expiré',
        'cancelled' => 'Annulé',
    ];
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
                        Gestion des contrats
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                        Consultez les contrats liés aux réservations Hello Desk, vérifiez leur statut
                        et suivez les documents PDF importés.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('admin.reservations.index') }}"
                           class="inline-flex h-11 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#284625] shadow-sm transition hover:bg-gray-100">
                            Voir les réservations
                        </a>

                        <a href="#contracts-list"
                           class="inline-flex h-11 items-center justify-center rounded-xl border border-white/25 bg-white/10 px-5 text-sm font-bold text-white transition hover:bg-white/15">
                            Voir les contrats
                        </a>
                    </div>
                </div>

                {{-- Mini summary --}}
                <div class="bg-white p-8">
                    <h2 class="text-lg font-bold text-gray-900">
                        Suivi des documents
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        État des PDF sur les contrats affichés.
                    </p>

                    <div class="mt-6 space-y-5">
                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">PDF importés</span>
                                <span class="font-bold text-emerald-600">{{ $pdfImportedRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-emerald-500" @style(['width: ' . $pdfImportedRate . '%'])></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">PDF manquants</span>
                                <span class="font-bold text-rose-600">{{ $pdfMissingRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-rose-500" @style(['width: ' . $pdfMissingRate . '%'])></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Success --}}
        @if(session('success'))
            <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- KPI cards --}}
        <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Total contrats</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $totalContracts }}</p>
                    </div>

                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">
                        Global
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 w-full rounded-full bg-sky-500"></div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Contrats actifs</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $activeVisible }}</p>
                    </div>

                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                        Actifs
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-emerald-500" @style(['width: ' . ($visibleCount > 0 ? round(($activeVisible / $visibleCount) * 100) : 0) . '%'])></div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">PDF importés</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $pdfImportedVisible }}</p>
                    </div>

                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">
                        Documents
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-violet-500" @style(['width: ' . $pdfImportedRate . '%'])></div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">PDF manquants</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $pdfMissingVisible }}</p>
                    </div>

                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">
                        À traiter
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-rose-500" @style(['width: ' . $pdfMissingRate . '%'])></div>
                </div>
            </div>
        </section>

        {{-- Filters --}}
        <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        Recherche et filtres
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Filtrez les contrats par client, email, titre ou statut.
                    </p>
                </div>

                <a href="{{ route('admin.contracts.index') }}#contracts-list"
                   class="text-sm font-bold text-slate-500 transition hover:text-[#284625] hover:underline">
                    Réinitialiser
                </a>
            </div>

            <form id="contractFilters"
                  method="GET"
                  action="{{ route('admin.contracts.index') }}#contracts-list"
                  class="grid gap-4 lg:grid-cols-[1.5fr_1fr_auto]">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Recherche
                    </label>

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Client, email, titre..."
                           class="filter-search h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Statut
                    </label>

                    <select name="status"
                            class="filter-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="all" @selected(request('status', 'all') === 'all')>Tous les statuts</option>
                        <option value="draft" @selected(request('status') === 'draft')>Brouillon</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expiré</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Annulé</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                        Filtrer
                    </button>
                </div>
            </form>
        </section>

        {{-- List --}}
        <section id="contracts-list" class="mt-8 scroll-mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Liste des contrats
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Chaque contrat est lié à un client et à une réservation.
                        </p>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                        {{ $totalContracts }} résultat(s)
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Contrat</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Client</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Espace</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Période</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Statut</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">PDF</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($contracts as $contract)
                            @php
                                $status = $contract->status;
                                $statusClass = $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';
                                $statusDot = $statusDots[$status] ?? 'bg-slate-500';
                                $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                            @endphp

                            <tr class="transition hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900">
                                        {{ $contract->title }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        Contrat #{{ $contract->id }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sm font-bold text-sky-700 ring-1 ring-sky-100">
                                            {{ strtoupper(substr($contract->client?->full_name ?? 'C', 0, 1)) }}
                                        </div>

                                        <div>
                                            <p class="font-semibold text-gray-900">
                                                {{ $contract->client?->full_name ?? 'Client supprimé' }}
                                            </p>

                                            <p class="mt-0.5 text-xs text-gray-500">
                                                {{ $contract->client?->email ?? 'Email non disponible' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800">
                                        {{ $contract->reservation?->space?->name ?? 'Non précisé' }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ $contract->reservation?->space?->code ?? 'Code non défini' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <p class="font-medium">
                                        {{ $contract->start_date?->format('d/m/Y') ?? '-' }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        au {{ $contract->end_date?->format('d/m/Y') ?? '-' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">
                                        <span class="h-2 w-2 rounded-full {{ $statusDot }}"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    @if($contract->pdf_path)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-600/20">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            Importé
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 ring-1 ring-rose-600/20">
                                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                            Manquant
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-end">
                                        <a href="{{ route('admin.contracts.show', $contract) }}"
                                           class="inline-flex h-9 items-center justify-center rounded-xl bg-sky-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-sky-700">
                                            Voir dossier
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
                                            Aucun contrat trouvé
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            Les contrats seront créés depuis les réservations confirmées.
                                        </p>

                                        <a href="{{ route('admin.reservations.index') }}"
                                           class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                                            Voir les réservations
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($contracts->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $contracts->links() }}
                </div>
            @endif
        </section>
    </div>
</div>

<script>
    const contractFilterForm = document.getElementById('contractFilters');
    const contractAutoFilters = document.querySelectorAll('.filter-auto');
    const contractSearchInput = document.querySelector('.filter-search');

    contractAutoFilters.forEach((filter) => {
        filter.addEventListener('change', () => {
            contractFilterForm.submit();
        });
    });

    let contractSearchTimer;

    if (contractSearchInput) {
        contractSearchInput.addEventListener('input', () => {
            clearTimeout(contractSearchTimer);

            contractSearchTimer = setTimeout(() => {
                contractFilterForm.submit();
            }, 600);
        });
    }
</script>
@endsection