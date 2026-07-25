@extends('layouts.app')

@section('title', $space->name . ' - Administration')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-5xl px-6 py-8">

        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-medium text-[#284625]">
                    <a href="{{ route('admin.spaces.index') }}" class="hover:underline">Espaces</a>
                    /
                    {{ $space->name }}
                </p>
                <h1 class="mt-1 text-3xl font-bold text-gray-900">{{ $space->name }}</h1>
                <p class="mt-2 text-sm text-gray-500">
                    {{ $space->campus->name ?? '—' }} · {{ $space->floor->name ?? '—' }} · {{ $space->spaceType->name ?? '—' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.spaces.edit', $space) }}"
                   class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                    Modifier
                </a>

                <form method="POST"
                      action="{{ route('admin.spaces.destroy', $space) }}"
                      onsubmit="return confirm('Supprimer définitivement cet espace ?');">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="inline-flex h-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @php
            $statusClasses = [
                'available' => 'bg-green-50 text-green-700 ring-green-600/20',
                'occupied' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                'unavailable' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
                'maintenance' => 'bg-red-50 text-red-700 ring-red-600/20',
            ];
        @endphp

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="space-y-6 lg:col-span-2">

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Informations générales</h2>

                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses[$space->status] ?? 'bg-gray-100 text-gray-700 ring-gray-500/20' }}">
                            {{ $statuses[$space->status] ?? $space->status }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Code</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $space->code ?? '—' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Type</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $space->spaceType->name ?? '—' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Capacité</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $space->capacity ? $space->capacity . ' pers.' : '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Surface</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $space->surface ? number_format($space->surface, 2) . ' m²' : '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Site</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $space->campus->name ?? '—' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Étage</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $space->floor->name ?? '—' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Actif</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $space->is_active ? 'Oui' : 'Non' }}
                            </p>
                        </div>
                    </div>

                    @if($space->description)
                        <div class="mt-5 rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Description</p>
                            <p class="mt-2 text-sm leading-6 text-gray-700">{{ $space->description }}</p>
                        </div>
                    @endif

                    @if($space->notes)
                        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Notes internes</p>
                            <p class="mt-2 text-sm leading-6 text-amber-900">{{ $space->notes }}</p>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Équipements</h2>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse($space->accessories as $accessory)
                            <span class="inline-flex items-center rounded-full bg-[#284625]/10 px-3 py-1.5 text-xs font-bold text-[#284625]">
                                {{ $accessory->name }}
                            </span>
                        @empty
                            <p class="text-sm text-gray-500">Aucun équipement associé à cet espace.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Historique des réservations</h2>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Client</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Début</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Fin</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($space->reservations as $reservation)
                                    <tr>
                                        <td class="px-3 py-3 text-sm font-semibold text-gray-900">
                                            {{ $reservation->client->full_name ?? '—' }}
                                        </td>
                                        <td class="px-3 py-3 text-sm text-gray-600">
                                            {{ $reservation->starts_at?->format('d/m/Y H:i') ?? '—' }}
                                        </td>
                                        <td class="px-3 py-3 text-sm text-gray-600">
                                            {{ $reservation->ends_at?->format('d/m/Y H:i') ?? '—' }}
                                        </td>
                                        <td class="px-3 py-3 text-sm text-gray-600">
                                            {{ ucfirst($reservation->status) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500">
                                            Aucune réservation pour cet espace.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Tarification</h2>

                    <div class="mt-4 space-y-2 text-sm text-gray-700">
                        <p class="flex justify-between">
                            <span>Heure</span>
                            <span class="font-semibold text-gray-900">
                                {{ $space->price_per_hour ? number_format($space->price_per_hour, 2) . ' DH' : '—' }}
                            </span>
                        </p>
                        <p class="flex justify-between">
                            <span>Jour</span>
                            <span class="font-semibold text-gray-900">
                                {{ $space->price_per_day ? number_format($space->price_per_day, 2) . ' DH' : '—' }}
                            </span>
                        </p>
                        <p class="flex justify-between">
                            <span>Mois</span>
                            <span class="font-semibold text-gray-900">
                                {{ $space->price_per_month ? number_format($space->price_per_month, 2) . ' DH' : '—' }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Occupation actuelle</h2>

                    @if($space->currentReservation)
                        <div class="mt-4 rounded-xl bg-blue-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Occupé actuellement par</p>
                            <p class="mt-2 text-sm font-bold text-blue-900">
                                {{ $space->currentReservation->client->full_name ?? 'Client inconnu' }}
                            </p>
                            <p class="mt-1 text-xs text-blue-700">
                                Jusqu’au {{ $space->currentReservation->ends_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @elseif($space->nextReservation)
                        <div class="mt-4 rounded-xl bg-amber-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-amber-700">Libre — prochaine réservation</p>
                            <p class="mt-2 text-sm font-bold text-amber-900">
                                {{ $space->nextReservation->client->full_name ?? 'Client inconnu' }}
                            </p>
                            <p class="mt-1 text-xs text-amber-700">
                                À partir du {{ $space->nextReservation->starts_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @else
                        <div class="mt-4 rounded-xl bg-green-50 p-4 text-sm font-semibold text-green-700">
                            Libre — aucune réservation à venir.
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection