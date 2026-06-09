@extends('layouts.app')

@section('title', 'Réservations - Administration')

@section('content')
@php
    $pageReservations = method_exists($reservations, 'getCollection')
        ? $reservations->getCollection()
        : collect($reservations);

    $totalReservations = method_exists($reservations, 'total')
        ? $reservations->total()
        : $pageReservations->count();

    $visibleCount = $pageReservations->count();
    $contractsCreatedVisible = $pageReservations->filter(fn ($reservation) => $reservation->contract)->count();
    $contractsMissingVisible = max($visibleCount - $contractsCreatedVisible, 0);

    $contractCreatedRate = $visibleCount > 0
        ? round(($contractsCreatedVisible / $visibleCount) * 100)
        : 0;

    $contractMissingRate = $visibleCount > 0
        ? round(($contractsMissingVisible / $visibleCount) * 100)
        : 0;

    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'in_progress' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'completed' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'expired' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
    ];

    $statusDots = [
        'pending' => 'bg-amber-500',
        'confirmed' => 'bg-emerald-500',
        'in_progress' => 'bg-blue-500',
        'completed' => 'bg-slate-500',
        'cancelled' => 'bg-rose-500',
        'expired' => 'bg-orange-500',
    ];

    $statusLabels = [
        'pending' => 'En attente',
        'confirmed' => 'Confirmée',
        'in_progress' => 'En cours',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
        'expired' => 'Expirée',
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
                        Gestion des réservations
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                        Consultez les réservations créées pour les clients Hello Desk, suivez leur statut
                        et vérifiez la présence des contrats liés.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('admin.interactive-map.index') }}"
                           class="inline-flex h-11 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#284625] shadow-sm transition hover:bg-gray-100">
                            Carte interactive
                        </a>

                        <a href="{{ route('admin.reservations.create') }}"
                           class="inline-flex h-11 items-center justify-center rounded-xl border border-white/25 bg-white/10 px-5 text-sm font-bold text-white transition hover:bg-white/15">
                            Créer une réservation
                        </a>
                    </div>
                </div>

                {{-- Mini summary --}}
                <div class="bg-white p-8">
                    <h2 class="text-lg font-bold text-gray-900">
                        Suivi des contrats
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        État des contrats sur les réservations affichées.
                    </p>

                    <div class="mt-6 space-y-5">
                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Contrats créés</span>
                                <span class="font-bold text-emerald-600">{{ $contractCreatedRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-emerald-500" @style(['width: ' . $contractCreatedRate . '%'])></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Contrats manquants</span>
                                <span class="font-bold text-rose-600">{{ $contractMissingRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-rose-500" @style(['width: ' . $contractMissingRate . '%'])></div>
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

        @if(session('error'))
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- KPI cards --}}
        <section class="mt-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Total réservations</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $totalReservations }}</p>
                    </div>

                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">
                        Planning
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 w-full rounded-full bg-sky-500"></div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Contrats créés</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $contractsCreatedVisible }}</p>
                    </div>

                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                        OK
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-emerald-500" @style(['width: ' . $contractCreatedRate . '%'])></div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Contrats manquants</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $contractsMissingVisible }}</p>
                    </div>

                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">
                        À traiter
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-rose-500" @style(['width: ' . $contractMissingRate . '%'])></div>
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
                        Filtrez les réservations par client, espace, statut, contrat ou période.
                    </p>
                </div>

                <a href="{{ route('admin.reservations.index') }}#reservations-list"
                class="text-sm font-bold text-slate-500 transition hover:text-[#284625] hover:underline">
                    Réinitialiser
                </a>
            </div>

            <form id="reservationFilters"
                method="GET"
                action="{{ route('admin.reservations.index') }}#reservations-list"
                class="grid gap-4 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Recherche
                    </label>

                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Client, email, espace..."
                        class="reservation-search h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Statut
                    </label>

                    <select name="status"
                            class="reservation-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="all" @selected(request('status', 'all') === 'all')>Tous les statuts</option>
                        <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                        <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmée</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>En cours</option>
                        <option value="completed" @selected(request('status') === 'completed')>Terminée</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Annulée</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expirée</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Contrat
                    </label>

                    <select name="contract"
                            class="reservation-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="all" @selected(request('contract', 'all') === 'all')>Tous</option>
                        <option value="created" @selected(request('contract') === 'created')>Contrat créé</option>
                        <option value="missing" @selected(request('contract') === 'missing')>Contrat manquant</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                        Filtrer
                    </button>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Date début
                    </label>

                    <input type="date"
                        name="from"
                        value="{{ request('from') }}"
                        class="reservation-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Date fin
                    </label>

                    <input type="date"
                        name="to"
                        value="{{ request('to') }}"
                        class="reservation-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>
            </form>
        </section>

        {{-- List --}}
        <section id="reservations-list" class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Liste des réservations
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Chaque réservation doit être suivie par un contrat lié.
                        </p>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                        {{ $totalReservations }} résultat(s)
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
                                Espace
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                Période
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                Statut
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                Contrat
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wide text-gray-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($reservations as $reservation)
                            @php
                                $status = $reservation->status;
                                $statusClass = $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';
                                $statusDot = $statusDots[$status] ?? 'bg-slate-500';
                                $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                            @endphp

                            <tr class="transition hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sm font-bold text-sky-700 ring-1 ring-sky-100">
                                            {{ strtoupper(substr($reservation->client?->full_name ?? 'C', 0, 1)) }}
                                        </div>

                                        <div>
                                            <p class="font-bold text-gray-900">
                                                {{ $reservation->client?->full_name ?? 'Client supprimé' }}
                                            </p>

                                            <p class="mt-0.5 text-xs text-gray-500">
                                                {{ $reservation->client?->email ?? 'Email non disponible' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800">
                                        {{ $reservation->space?->name ?? 'Espace supprimé' }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ $reservation->space?->code ?? 'Code non défini' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <p class="font-medium">
                                        {{ $reservation->starts_at?->format('d/m/Y H:i') ?? '-' }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        au {{ $reservation->ends_at?->format('d/m/Y H:i') ?? '-' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">
                                        <span class="h-2 w-2 rounded-full {{ $statusDot }}"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    @if($reservation->contract)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-600/20">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            Créé
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
                                        <a href="{{ route('admin.reservations.show', $reservation) }}"
                                           class="inline-flex h-9 items-center justify-center rounded-xl bg-sky-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-sky-700">
                                            Voir dossier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl font-bold text-slate-400">
                                            +
                                        </div>

                                        <p class="mt-4 font-bold text-gray-800">
                                            Aucune réservation pour le moment
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            Créez une réservation depuis la carte interactive ou le formulaire.
                                        </p>

                                        <div class="mt-5 flex flex-wrap justify-center gap-3">
                                            <a href="{{ route('admin.interactive-map.index') }}"
                                               class="inline-flex h-10 items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-4 text-sm font-bold text-sky-700 transition hover:bg-sky-100">
                                                Carte interactive
                                            </a>

                                            <a href="{{ route('admin.reservations.create') }}"
                                               class="inline-flex h-10 items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                                                Créer réservation
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reservations->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $reservations->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
<script>
    const reservationFilterForm = document.getElementById('reservationFilters');
    const reservationAutoFilters = document.querySelectorAll('.reservation-auto');
    const reservationSearchInput = document.querySelector('.reservation-search');

    reservationAutoFilters.forEach((filter) => {
        filter.addEventListener('change', () => {
            reservationFilterForm.submit();
        });
    });

    let reservationSearchTimer;

    if (reservationSearchInput) {
        reservationSearchInput.addEventListener('input', () => {
            clearTimeout(reservationSearchTimer);

            reservationSearchTimer = setTimeout(() => {
                reservationFilterForm.submit();
            }, 600);
        });
    }
</script>
@endsection