@extends('layouts.app')

@section('title', 'Carte interactive - Administration')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-8">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Carte interactive</h1>
            <p class="mt-1 text-sm text-gray-500">
                Visualisez les espaces disponibles par campus et par étage.
            </p>
        </div>

        <a href="{{ route('admin.reservations.index') }}"
           class="rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
            Voir les réservations
        </a>
    </div>

    <form method="GET"
          action="{{ route('admin.interactive-map.index') }}"
          class="mb-6 grid gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-3">

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Campus
            </label>

            <select name="campus_id"
                    onchange="this.form.submit()"
                    class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
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
                    class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
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
                    class="h-12 w-full rounded-xl bg-[#284625] px-5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                Afficher
            </button>
        </div>
    </form>

    <div class="grid gap-6 lg:grid-cols-4">
    {{-- MAP --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-3">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Plan de l’étage</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Cliquez sur un espace pour afficher ses détails.
                </p>
            </div>

            <div class="flex flex-wrap gap-2 text-xs">
                <span class="rounded-full border border-green-200 bg-green-50 px-3 py-1 font-semibold text-green-700">
                    Disponible
                </span>

                <span class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 font-semibold text-blue-700">
                    Réservé
                </span>

                <span class="rounded-full border border-gray-200 bg-gray-100 px-3 py-1 font-semibold text-gray-700">
                    Occupé
                </span>

                <span class="rounded-full border border-red-200 bg-red-50 px-3 py-1 font-semibold text-red-700">
                    Indisponible
                </span>

                <span class="rounded-full border border-yellow-200 bg-yellow-50 px-3 py-1 font-semibold text-yellow-700">
                    Maintenance
                </span>
            </div>
        </div>

        <div class="rounded-2xl border-2 border-gray-300 bg-gray-50 p-5">
            @php
                $currentFloor = $selectedFloor ?? $floors->firstWhere('id', $selectedFloorId);

                $mapView = $currentFloor?->map_key
                    ? 'admin.interactive-map.maps.' . $currentFloor->map_key
                    : null;
            @endphp

            @if($mapView && view()->exists($mapView))
                @include($mapView, ['spaces' => $spaces])
            @elseif($spaces->count())
                <div class="grid min-h-[420px] grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                    @foreach($spaces as $space)
                        @php
                            $displayStatus = $space->display_status ?? $space->status ?? 'Disponible';
                            $status = mb_strtolower($displayStatus);

                            $statusClass = match($status) {
                                'disponible', 'available' => 'border-green-300 bg-green-50 text-green-800 hover:bg-green-100',
                                'réservé', 'reserve', 'reserved' => 'border-blue-300 bg-blue-50 text-blue-800 hover:bg-blue-100',
                                'occupé', 'occupe', 'occupied' => 'border-gray-300 bg-gray-100 text-gray-800 hover:bg-gray-200',
                                'indisponible', 'unavailable' => 'border-red-300 bg-red-50 text-red-800 hover:bg-red-100',
                                'maintenance', 'en maintenance' => 'border-yellow-300 bg-yellow-50 text-yellow-800 hover:bg-yellow-100',
                                default => 'border-gray-300 bg-white text-gray-800 hover:bg-gray-50',
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
                                class="space-tile flex min-h-[110px] flex-col justify-between rounded-xl border-2 p-4 text-left text-sm shadow-sm transition {{ $statusClass }}">
                            <div>
                                <p class="font-bold">{{ $space->name }}</p>

                                <p class="mt-1 text-xs opacity-80">
                                    {{ $space->code ?? 'Code non défini' }}
                                </p>
                            </div>

                            <div class="mt-4">
                                <span class="rounded-full bg-white/70 px-2 py-1 text-xs font-semibold">
                                    {{ $displayStatus }}
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="flex min-h-[420px] items-center justify-center rounded-xl border border-dashed border-gray-300 bg-white">
                    <div class="text-center">
                        <p class="text-sm font-semibold text-gray-700">
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
    <aside class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-1 lg:sticky lg:top-6 lg:self-start">
        <h2 class="text-lg font-bold text-gray-900">Détails de l’espace</h2>

        <div id="empty-space-message" class="mt-5 rounded-xl bg-gray-50 p-4 text-sm text-gray-500">
            Sélectionnez un espace sur la carte.
        </div>

        <div id="space-details" class="mt-5 hidden space-y-4">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400">Nom</p>
                <p id="detail-name" class="mt-1 text-sm font-bold text-gray-900"></p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase text-gray-400">Code</p>
                <p id="detail-code" class="mt-1 text-sm text-gray-700"></p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase text-gray-400">Statut</p>
                <p id="detail-status" class="mt-1 inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700"></p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase text-gray-400">Capacité</p>
                <p id="detail-capacity" class="mt-1 text-sm text-gray-700"></p>
            </div>

            <div class="rounded-xl bg-gray-50 p-4">
                <p class="text-sm font-semibold text-gray-900">Prix</p>

                <div class="mt-3 space-y-2 text-sm text-gray-600">
                    <p>Heure : <span id="detail-price-hour">—</span></p>
                    <p>Jour : <span id="detail-price-day">—</span></p>
                    <p>Mois : <span id="detail-price-month">—</span></p>
                </div>
            </div>

            <a id="reserve-button"
               href="#"
               class="hidden w-full rounded-xl bg-[#284625] px-5 py-3 text-center text-sm font-semibold text-white shadow-sm hover:opacity-90">
                Réserver cet espace
            </a>

            <div id="cannot-reserve-message"
                 class="hidden rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                Cet espace ne peut pas être réservé actuellement.
            </div>
        </div>
    </aside>
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
        document.getElementById('detail-status').textContent = button.dataset.status || '—';
        document.getElementById('detail-capacity').textContent = button.dataset.capacity || '—';

        document.getElementById('detail-price-hour').textContent = button.dataset.priceHour
            ? button.dataset.priceHour + ' DH'
            : '—';

        document.getElementById('detail-price-day').textContent = button.dataset.priceDay
            ? button.dataset.priceDay + ' DH'
            : '—';

        document.getElementById('detail-price-month').textContent = button.dataset.priceMonth
            ? button.dataset.priceMonth + ' DH'
            : '—';

        const reserveButton = document.getElementById('reserve-button');
        const cannotReserveMessage = document.getElementById('cannot-reserve-message');

        if (button.dataset.reserveUrl) {
            reserveButton.href = button.dataset.reserveUrl;
            reserveButton.classList.remove('hidden');
            cannotReserveMessage.classList.add('hidden');
        } else {
            reserveButton.href = '#';
            reserveButton.classList.add('hidden');
            cannotReserveMessage.classList.remove('hidden');
        }
    }
</script>
@endsection