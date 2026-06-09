@extends('layouts.app')

@section('title', 'Mes contrats - Hello Desk')

@section('content')
@php
    $statusLabels = [
        'draft' => 'En préparation',
        'active' => 'Actif',
        'expired' => 'Expiré',
        'cancelled' => 'Annulé',
    ];

    $statusClasses = [
        'draft' => 'bg-purple-50 text-purple-700 ring-purple-200',
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'expired' => 'bg-pink-50 text-pink-700 ring-pink-200',
        'cancelled' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];
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
                    Mes contrats
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                    Consultez vos contrats, leur statut et les documents disponibles.
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
            Aucun profil client n’est lié à ce compte.
        </div>
    @else

        {{-- Minimal stats --}}
        <div class="mb-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-slate-500">
                        Total contrats
                    </p>

                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                        Global
                    </span>
                </div>

                <p class="mt-3 text-3xl font-black text-slate-900">
                    {{ $totalCount }}
                </p>

                <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full w-2/3 rounded-full bg-blue-500"></div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-slate-500">
                        Contrats actifs
                    </p>

                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                        Actifs
                    </span>
                </div>

                <p class="mt-3 text-3xl font-black text-slate-900">
                    {{ $activeCount }}
                </p>

                <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full w-1/3 rounded-full bg-emerald-500"></div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-slate-500">
                        En préparation
                    </p>

                    <span class="rounded-full bg-purple-50 px-3 py-1 text-xs font-bold text-purple-700">
                        Brouillons
                    </span>
                </div>

                <p class="mt-3 text-3xl font-black text-slate-900">
                    {{ $draftCount }}
                </p>

                <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full w-1/2 rounded-full bg-purple-500"></div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('client.contracts.index') }}" class="grid gap-4 lg:grid-cols-[1fr_220px_140px_130px] lg:items-end">
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">
                        Recherche
                    </label>

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Rechercher par contrat, espace ou code..."
                           class="h-11 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">
                        Statut
                    </label>

                    <select name="status"
                            class="h-11 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="all" @selected(request('status', 'all') === 'all')>Tous</option>
                        <option value="draft" @selected(request('status') === 'draft')>En préparation</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expiré</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Annulé</option>
                    </select>
                </div>

                <button type="submit"
                        class="h-11 rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#20391f]">
                    Filtrer
                </button>

                <a href="{{ route('client.contracts.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                    Réinitialiser
                </a>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-black text-slate-900">
                    Liste des contrats
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Documents contractuels associés à vos réservations.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Contrat</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Espace</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Période</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Statut</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Document</th>
                            <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($contracts as $contract)
                            @php
                                $statusClass = $statusClasses[$contract->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <p class="font-black text-slate-900">
                                        {{ $contract->title }}
                                    </p>

                                    <p class="mt-1 text-xs font-medium text-slate-500">
                                        Réf. HD-CONTRAT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-sm font-bold text-slate-800">
                                        {{ $contract->reservation?->space?->name ?? 'Non précisé' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $contract->start_date?->format('d/m/Y') }}
                                    <span class="mx-1 text-slate-400">→</span>
                                    {{ $contract->end_date?->format('d/m/Y') }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">
                                        {{ $statusLabels[$contract->status] ?? ucfirst($contract->status) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    @if($contract->pdf_path)
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                                            PDF signé
                                        </span>
                                    @elseif($contract->status === 'active')
                                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 ring-1 ring-blue-200">
                                            Export disponible
                                        </span>
                                    @else
                                        <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700 ring-1 ring-orange-200">
                                            En préparation
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('client.contracts.show', $contract) }}"
                                       class="inline-flex items-center justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#20391f]">
                                        Voir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <p class="font-bold text-slate-800">
                                        Aucun contrat trouvé
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Vos contrats apparaîtront ici après validation d’une réservation.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($contracts, 'links'))
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection