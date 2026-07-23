@extends('layouts.app')

@section('title', 'Mes réservations - Hello Desk')

@section('content')
@php
    $statusLabels = [
        'pending' => 'En attente',
        'confirmed' => 'Confirmée',
        'active' => 'En cours',
        'in_progress' => 'En cours',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
        'expired' => 'Expirée',
    ];

    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'active' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'in_progress' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'completed' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
        'expired' => 'bg-pink-50 text-pink-700 ring-pink-200',
    ];

    $durationLabels = [
        'hour' => 'À l’heure',
        'day' => 'À la journée',
        'month' => 'Au mois',
        'custom' => 'Personnalisée',
    ];

    $formatMoney = function ($value) {
        if ($value === null || $value === '') {
            return '—';
        }

        return number_format((float) $value, 2, ',', ' ') . ' MAD';
    };
@endphp

<div class="mx-auto max-w-7xl px-6 py-8">

    {{-- Header --}}
    <div class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-[1.4fr_0.7fr]">
            <div class="bg-gradient-to-br from-[#284625] via-[#2f6130] to-[#3f7a3b] p-8 text-white">
                <p class="text-sm font-bold uppercase tracking-wide text-white/70">
                    Espace client
                </p>

                <h1 class="mt-3 text-3xl font-black">
                    Mes réservations
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                    Consultez les espaces réservés, les périodes, les contrats liés et l’état de chaque réservation.
                </p>
            </div>

            <div class="bg-white p-8">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                    Client connecté
                </p>

                <p class="mt-2 text-lg font-black text-slate-900">
                    {{ $client?->full_name ?? auth()->user()->name }}
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $client?->email ?? auth()->user()->email }}
                </p>
            </div>
        </div>
    </div>

    @if(!$client)
        <div class="rounded-2xl border border-red-200 bg-red-50 p-6 text-sm text-red-700">
            Aucun profil client n’est lié à ce compte. Veuillez contacter l’administration Hello Desk.
        </div>
    @else

        {{-- Stats --}}
        <div class="mb-6 grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">Total réservations</p>
                <p class="mt-3 text-3xl font-black text-slate-900">
                    {{ $stats['total'] ?? 0 }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">En attente</p>
                <p class="mt-3 text-3xl font-black text-amber-600">
                    {{ $stats['pending'] ?? 0 }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">Confirmées</p>
                <p class="mt-3 text-3xl font-black text-emerald-600">
                    {{ $stats['confirmed'] ?? 0 }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">En cours</p>
                <p class="mt-3 text-3xl font-black text-blue-600">
                    {{ $stats['active'] ?? 0 }}
                </p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET"
                  action="{{ route('client.reservations.index') }}"
                  class="grid gap-4 lg:grid-cols-[1fr_220px_140px_130px] lg:items-end">

                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">
                        Recherche
                    </label>

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Rechercher par espace, campus, étage ou contrat..."
                           class="h-11 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">
                        Statut
                    </label>

                    <select name="status"
                            class="h-11 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="all" @selected(request('status', 'all') === 'all')>Tous</option>
                        <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                        <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmée</option>
                        <option value="active" @selected(request('status') === 'active')>En cours</option>
                        <option value="completed" @selected(request('status') === 'completed')>Terminée</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Annulée</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expirée</option>
                    </select>
                </div>

                <button type="submit"
                        class="h-11 rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#20391f]">
                    Filtrer
                </button>

                <a href="{{ route('client.reservations.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                    Réinitialiser
                </a>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-black text-slate-900">
                    Liste des réservations
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Réservations associées à votre compte client.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Espace</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Localisation</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Période</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Type</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Prix</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Statut</th>
                            <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($reservations as $reservation)
                            @php
                                $status = $reservation->status ?? 'pending';
                                $statusClass = $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                                $space = $reservation->space;
                                $contract = $reservation->contract;
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <p class="font-black text-slate-900">
                                        {{ $space?->name ?? 'Espace supprimé' }}
                                    </p>

                                    <p class="mt-1 text-xs font-medium text-slate-500">
                                        {{ $space?->spaceType?->name ?? 'Type non précisé' }}

                                        @if($space?->code)
                                            <span class="text-slate-300">•</span>
                                            {{ $space->code }}
                                        @endif
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-sm font-bold text-slate-800">
                                        {{ $reservation->campus?->name ?? $space?->campus?->name ?? 'Site non précisé' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $reservation->floor?->name ?? $space?->floor?->name ?? 'Étage non précisé' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <div class="font-semibold text-slate-800">
                                        {{ $reservation->starts_at?->format('d/m/Y H:i') ?? '—' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        jusqu’au {{ $reservation->ends_at?->format('d/m/Y H:i') ?? '—' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $durationLabels[$reservation->duration_type] ?? ucfirst($reservation->duration_type ?? '—') }}
                                </td>

                                <td class="px-5 py-4 text-sm font-bold text-slate-900">
                                    {{ $formatMoney($reservation->negotiated_price) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">
                                        {{ $statusLabels[$status] ?? ucfirst($status) }}
                                    </span>

                                    <div class="mt-2">
                                        @if($contract)
                                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                                                Contrat lié
                                            </span>
                                        @else
                                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">
                                                Contrat en attente
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('client.reservations.show', $reservation) }}"
                                       class="inline-flex items-center justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#20391f]">
                                        Voir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <p class="font-bold text-slate-800">
                                        Aucune réservation trouvée
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Vos réservations apparaîtront ici après leur création par l’équipe Hello Desk.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($reservations, 'links'))
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection