@extends('layouts.app')

@section('title', 'Vue Espace - Administration')

@section('content')
@php
    $currentFloor = $selectedFloor ?? $floors->firstWhere('id', $selectedFloorId);
    $currentCampus = $campuses->firstWhere('id', $selectedCampusId);

    $mapView = $currentFloor?->map_key
        ? 'admin.interactive-map.maps.' . $currentFloor->map_key
        : null;

    $statusCounts = [
        'available' => 0,
        'reserved' => 0,
        'occupied' => 0,
        'unavailable' => 0,
        'maintenance' => 0,
    ];

    foreach ($spaces as $space) {
        $displayStatus = $space->display_status ?? $space->status ?? 'Disponible';
        $status = mb_strtolower($displayStatus);

        if (in_array($status, ['disponible', 'available'])) {
            $statusCounts['available']++;
        } elseif (in_array($status, ['réservé', 'reserve', 'reserved'])) {
            $statusCounts['reserved']++;
        } elseif (in_array($status, ['occupé', 'occupe', 'occupied'])) {
            $statusCounts['occupied']++;
        } elseif (in_array($status, ['indisponible', 'unavailable'])) {
            $statusCounts['unavailable']++;
        } elseif (in_array($status, ['maintenance', 'en maintenance'])) {
            $statusCounts['maintenance']++;
        }
    }
@endphp

<div class="min-h-screen bg-[#f6f8f6]">
    <div class="mx-auto max-w-7xl px-6 py-8">

        {{-- Compact Header --}}
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-[#284625]">
                        Administration
                    </p>

                    <h1 class="mt-2 text-3xl font-bold text-gray-900">
                        Vue Espace
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-500">
                        Visualisez les espaces par campus et par étage, consultez leur disponibilité
                        et accédez rapidement à la réservation d’un espace disponible.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.reservations.index') }}"
                    class="inline-flex h-11 items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-5 text-sm font-bold text-sky-700 transition hover:bg-sky-100">
                        Réservations
                    </a>

                    <a href="#map-zone"
                    class="inline-flex h-11 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                        Voir le plan
                    </a>
                </div>
            </div>
        </section>

        {{-- Compact selector --}}
        <section class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <form method="GET"
                action="{{ route('admin.interactive-map.index') }}#map-zone"
                class="grid gap-4 lg:grid-cols-[1fr_1fr_auto]">

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Campus
                    </label>

                    <select name="campus_id"
                            onchange="this.form.submit()"
                            class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected($selectedCampusId == $campus->id)>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Étage
                    </label>

                    <select name="floor_id"
                            onchange="this.form.submit()"
                            class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        @forelse($floors as $floor)
                            <option value="{{ $floor->id }}" @selected($selectedFloorId == $floor->id)>
                                {{ $floor->name }}
                            </option>
                        @empty
                            <option value="">Aucun étage</option>
                        @endforelse
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                        Afficher
                    </button>
                </div>
            </form>
        </section>

        {{-- Map + details --}}
        <div id="map-zone" class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px]">

            {{-- MAP --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Plan de l’étage
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Cliquez sur un espace pour consulter ses informations et créer une réservation.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 font-bold text-emerald-700">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Disponible
                        </span>

                        <span class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 font-bold text-sky-700">
                            <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                            Réservé
                        </span>

                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-bold text-slate-700">
                            <span class="h-2 w-2 rounded-full bg-slate-500"></span>
                            Occupé
                        </span>

                        <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 font-bold text-rose-700">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            Indisponible
                        </span>

                        <span class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-orange-50 px-3 py-1 font-bold text-orange-700">
                            <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                            Maintenance
                        </span>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 shadow-inner">
                    @if($mapView && view()->exists($mapView))
                        @include($mapView, ['spaces' => $spaces])
                    @elseif($spaces->count())
                        <div class="grid min-h-[460px] grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                            @foreach($spaces as $space)
                                @php
                                    $displayStatus = $space->display_status ?? $space->status ?? 'Disponible';
                                    $status = mb_strtolower($displayStatus);

                                    $statusClass = match($status) {
                                        'disponible', 'available' => 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100',
                                        'réservé', 'reserve', 'reserved' => 'border-sky-200 bg-sky-50 text-sky-800 hover:bg-sky-100',
                                        'occupé', 'occupe', 'occupied' => 'border-slate-300 bg-slate-100 text-slate-800 hover:bg-slate-200',
                                        'indisponible', 'unavailable' => 'border-rose-200 bg-rose-50 text-rose-800 hover:bg-rose-100',
                                        'maintenance', 'en maintenance' => 'border-orange-200 bg-orange-50 text-orange-800 hover:bg-orange-100',
                                        default => 'border-gray-200 bg-white text-gray-800 hover:bg-gray-50',
                                    };

                                    $canReserve = in_array($status, ['disponible', 'available']);
                                @endphp

                                <button type="button"
                                        onclick="selectSpace(this)"
                                        data-id="{{ $space->id }}"
                                        data-name="{{ $space->name }}"
                                        data-code="{{ $space->code ?? '' }}"
                                        data-status="{{ $displayStatus }}"
                                        data-capacity="{{ $space->capacity ?? 'Non précisée' }}"
                                        data-price-hour="{{ $space->price_per_hour ?? '' }}"
                                        data-price-day="{{ $space->price_per_day ?? '' }}"
                                        data-price-month="{{ $space->price_per_month ?? '' }}"
                                        data-reserve-url="{{ $canReserve ? route('admin.reservations.create', ['space_id' => $space->id]) : '' }}"
                                        class="space-tile flex min-h-[120px] flex-col justify-between rounded-2xl border p-4 text-left text-sm shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $statusClass }}">
                                    <div>
                                        <p class="font-bold">{{ $space->name }}</p>

                                        <p class="mt-1 text-xs opacity-80">
                                            {{ $space->code ?? 'Code non défini' }}
                                        </p>
                                    </div>

                                    <div class="mt-4">
                                        <span class="rounded-full bg-white/80 px-2.5 py-1 text-xs font-bold">
                                            {{ $displayStatus }}
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="flex min-h-[460px] items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-white">
                            <div class="text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl font-bold text-slate-400">
                                    +
                                </div>

                                <p class="mt-4 text-sm font-bold text-gray-700">
                                    Aucun espace trouvé
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    Ajoutez des espaces pour ce campus et cet étage.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- DETAILS --}}
            <aside class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:sticky lg:top-6 lg:self-start">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Détails de l’espace
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Informations opérationnelles.
                        </p>
                    </div>

                    <span id="detail-status-pill"
                          class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                    </span>
                </div>

                <div id="empty-space-message" class="mt-5 rounded-2xl border border-dashed border-gray-300 bg-slate-50 p-5 text-sm text-gray-500">
                    Sélectionnez un espace sur la carte pour afficher ses détails.
                </div>

                <div id="space-details" class="mt-5 hidden space-y-4">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Nom</p>
                        <p id="detail-name" class="mt-1 text-base font-bold text-gray-900"></p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                        <div class="rounded-2xl border border-gray-100 bg-white p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Code</p>
                            <p id="detail-code" class="mt-1 text-sm font-semibold text-gray-700"></p>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-white p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Capacité</p>
                            <p id="detail-capacity" class="mt-1 text-sm font-semibold text-gray-700"></p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-4">
                        <p class="text-sm font-bold text-gray-900">Prix</p>

                        <div class="mt-3 space-y-2 text-sm text-gray-600">
                            <p class="flex justify-between">
                                <span>Heure</span>
                                <span id="detail-price-hour" class="font-semibold text-gray-900">—</span>
                            </p>

                            <p class="flex justify-between">
                                <span>Jour</span>
                                <span id="detail-price-day" class="font-semibold text-gray-900">—</span>
                            </p>

                            <p class="flex justify-between">
                                <span>Mois</span>
                                <span id="detail-price-month" class="font-semibold text-gray-900">—</span>
                            </p>
                        </div>
                    </div>

                    <a id="reserve-button"
                       href="#"
                       class="hidden w-full rounded-xl bg-[#284625] px-5 py-3 text-center text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                        Réserver cet espace
                    </a>

                    <div id="cannot-reserve-message"
                         class="hidden rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-700">
                        Cet espace ne peut pas être réservé actuellement.
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
    function selectSpace(button) {
        document.querySelectorAll('.space-tile').forEach(tile => {
            tile.classList.remove('ring-4', 'ring-[#284625]', 'ring-offset-2');
        });

        button.classList.add('ring-4', 'ring-[#284625]', 'ring-offset-2');

        document.getElementById('empty-space-message').classList.add('hidden');
        document.getElementById('space-details').classList.remove('hidden');

        document.getElementById('detail-name').textContent = button.dataset.name || '—';
        document.getElementById('detail-code').textContent = button.dataset.code || '—';
        document.getElementById('detail-capacity').textContent = button.dataset.capacity || '—';

        const status = button.dataset.status || '—';
        const statusPill = document.getElementById('detail-status-pill');

        statusPill.textContent = status;
        statusPill.classList.remove('hidden');

        document.getElementById('detail-price-hour').textContent = button.dataset.priceHour
            ? `${button.dataset.priceHour} DH`
            : '—';

        document.getElementById('detail-price-day').textContent = button.dataset.priceDay
            ? `${button.dataset.priceDay} DH`
            : '—';

        document.getElementById('detail-price-month').textContent = button.dataset.priceMonth
            ? `${button.dataset.priceMonth} DH`
            : '—';

        const reserveButton = document.getElementById('reserve-button');
        const cannotReserveMessage = document.getElementById('cannot-reserve-message');

        if (button.dataset.reserveUrl) {
            reserveButton.href = button.dataset.reserveUrl;
            reserveButton.classList.remove('hidden');
            cannotReserveMessage.classList.add('hidden');
        } else {
            reserveButton.classList.add('hidden');
            cannotReserveMessage.classList.remove('hidden');
        }
    }
</script>
@endsection