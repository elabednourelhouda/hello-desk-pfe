@extends('layouts.app')

@section('title', 'Carte interactive - Administration')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-6 py-8">

        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-[#284625]">Administration</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">Carte interactive des espaces</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">
                    Visualisez les bureaux, salles de réunion et positions par campus et par étage.
                </p>
            </div>

            <a href="{{ route('admin.spaces.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-100">
                Voir la liste
            </a>
        </div>

        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.map.index') }}" class="grid gap-4 md:grid-cols-3">

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Campus</label>
                    <select name="campus_id"
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected($selectedCampus && $selectedCampus->id === $campus->id)>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Étage</label>
                    <select name="floor_id"
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        @foreach($floors as $floor)
                            <option value="{{ $floor->id }}" @selected($selectedFloor && $selectedFloor->id === $floor->id)>
                                {{ $floor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <div class="w-full rounded-xl bg-[#284625]/5 px-4 py-3 text-sm text-[#284625]">
                        <span class="font-semibold">Vue actuelle :</span>
                        {{ $selectedCampus->name ?? 'Aucun campus' }}
                        @if($selectedFloor)
                            — {{ $selectedFloor->name }}
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Plan d’étage</h2>
                        <p class="text-sm text-slate-500">
                            Cliquez sur un espace pour afficher ses détails.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700 ring-1 ring-emerald-600/20">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Disponible
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 font-medium text-blue-700 ring-1 ring-blue-600/20">
                            <span class="h-2 w-2 rounded-full bg-blue-500"></span> Occupé
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-700 ring-1 ring-amber-600/20">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span> Réservé
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 font-medium text-red-700 ring-1 ring-red-600/20">
                            <span class="h-2 w-2 rounded-full bg-red-500"></span> Maintenance
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 ring-1 ring-slate-500/20">
                            <span class="h-2 w-2 rounded-full bg-slate-500"></span> Indisponible
                        </span>
                    </div>
                </div>

                <div class="overflow-auto rounded-2xl border border-slate-200 bg-slate-100 p-4">
                    <div class="relative mx-auto h-[560px] w-[900px] rounded-xl border-4 border-slate-700 bg-white shadow-inner">

                        <div
                            class="absolute left-[20px] top-[20px] right-[20px] bottom-[20px] rounded-lg border border-slate-300"
                            style="background-image: linear-gradient(to right, #e2e8f0 1px, transparent 1px), linear-gradient(to bottom, #e2e8f0 1px, transparent 1px); background-size: 40px 40px;"
                        ></div>

                        <div class="absolute left-[380px] top-[460px] h-[70px] w-[140px] rounded-t-xl border border-slate-400 bg-slate-50 text-center text-xs font-semibold text-slate-500 flex items-center justify-center">
                            Entrée
                        </div>

                        <div class="absolute left-[40px] top-[400px] h-[110px] w-[260px] rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs text-slate-500">
                            <div class="font-semibold text-slate-700">Réception / Accueil</div>
                            <div class="mt-1">Zone commune</div>
                        </div>

                        @forelse($spaces as $space)
                            @php
                                $statusMapClasses = [
                                    'available' => 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100',
                                    'occupied' => 'border-blue-300 bg-blue-50 text-blue-800 hover:bg-blue-100',
                                    'reserved' => 'border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100',
                                    'unavailable' => 'border-slate-300 bg-slate-100 text-slate-700 hover:bg-slate-200',
                                    'maintenance' => 'border-red-300 bg-red-50 text-red-800 hover:bg-red-100',
                                ];

                                $x = $space->map_x ?? 60;
                                $y = $space->map_y ?? 60;
                                $width = $space->map_width ?? 150;
                                $height = $space->map_height ?? 100;
                            @endphp

                            <button type="button"
                            class="space-card absolute rounded-xl border-2 p-3 text-left text-xs shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $statusMapClasses[$space->status] ?? 'border-slate-300 bg-slate-100 text-slate-700' }}"
                            data-x="{{ $x }}"
                            data-y="{{ $y }}"
                            data-width="{{ $width }}"
                            data-height="{{ $height }}"
                            data-space='@json([
                                "name" => $space->name,
                                "code" => $space->internal_code,
                                "type" => $space->spaceType->name ?? "-",
                                "campus" => $space->campus->name ?? "-",
                                "floor" => $space->floor->name ?? "-",
                                "capacity" => $space->capacity,
                                "area" => $space->area_m2,
                                "status" => $space->status,
                                "description" => $space->description,
                                "price_hour" => $space->price_per_hour,
                                "price_day" => $space->price_per_day,
                                "price_month" => $space->price_per_month,
                                "accessories" => $space->accessories->pluck("name")->values(),
                            ])'>
                                <div class="font-bold leading-tight">{{ $space->name }}</div>
                                <div class="mt-1 opacity-80">{{ $space->spaceType->name ?? '-' }}</div>
                                <div class="mt-2 text-[11px] opacity-70">{{ $space->capacity ?? '-' }} pers.</div>
                            </button>
                        @empty
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-8 py-6 text-center shadow-sm">
                                    <p class="font-semibold text-slate-700">Aucun espace trouvé pour cet étage.</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Ajoutez des espaces avec des positions sur la carte.
                                    </p>
                                </div>
                            </div>
                        @endforelse

                    </div>
                </div>
            </div>

            <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div id="emptyPanel">
                    <p class="text-sm font-semibold text-[#284625]">Détails de l’espace</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Sélectionnez un espace</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Cliquez sur un bureau, une salle ou une position dans la carte pour afficher les informations.
                    </p>
                </div>

                <div id="detailsPanel" class="hidden">
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-[#284625]" id="detailType">-</p>
                        <h2 class="mt-1 text-2xl font-bold text-slate-900" id="detailName">-</h2>
                        <p class="mt-1 text-xs text-slate-500" id="detailCode">-</p>
                    </div>

                    <div class="mb-5">
                        <span id="detailStatus"
                              class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset">
                            -
                        </span>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Campus</div>
                            <div class="mt-1 font-medium text-slate-800" id="detailCampus">-</div>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Étage</div>
                            <div class="mt-1 font-medium text-slate-800" id="detailFloor">-</div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Capacité</div>
                                <div class="mt-1 font-medium text-slate-800" id="detailCapacity">-</div>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Surface</div>
                                <div class="mt-1 font-medium text-slate-800" id="detailArea">-</div>
                            </div>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Prix</div>
                            <div class="mt-2 space-y-1 font-medium text-slate-800" id="detailPrices">-</div>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Équipements</div>
                            <div class="mt-2 flex flex-wrap gap-2" id="detailAccessories">-</div>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Description</div>
                            <div class="mt-1 leading-6 text-slate-700" id="detailDescription">-</div>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3">
                        <a href="#"
                           class="rounded-xl bg-[#284625] px-4 py-3 text-center text-sm font-semibold text-white shadow-sm hover:bg-[#1f351d]">
                            Créer une réservation
                        </a>

                        <a href="#"
                           class="rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-100">
                            Voir les détails
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
    const statusLabels = {
        available: 'Disponible',
        occupied: 'Occupé',
        reserved: 'Réservé',
        unavailable: 'Indisponible',
        maintenance: 'En maintenance'
    };

    const statusClasses = {
        available: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        occupied: 'bg-blue-50 text-blue-700 ring-blue-600/20',
        reserved: 'bg-amber-50 text-amber-700 ring-amber-600/20',
        unavailable: 'bg-slate-100 text-slate-700 ring-slate-500/20',
        maintenance: 'bg-red-50 text-red-700 ring-red-600/20'
    };

    const emptyPanel = document.getElementById('emptyPanel');
    const detailsPanel = document.getElementById('detailsPanel');

    document.querySelectorAll('.space-card').forEach((card) => {
        card.style.left = `${card.dataset.x}px`;
        card.style.top = `${card.dataset.y}px`;
        card.style.width = `${card.dataset.width}px`;
        card.style.height = `${card.dataset.height}px`;
        card.addEventListener('click', () => {
            const space = JSON.parse(card.dataset.space);

            document.querySelectorAll('.space-card').forEach((item) => {
                item.classList.remove('ring-4', 'ring-[#284625]/30');
            });

            card.classList.add('ring-4', 'ring-[#284625]/30');

            emptyPanel.classList.add('hidden');
            detailsPanel.classList.remove('hidden');

            document.getElementById('detailType').textContent = space.type || '-';
            document.getElementById('detailName').textContent = space.name || '-';
            document.getElementById('detailCode').textContent = space.code || 'Sans code interne';
            document.getElementById('detailCampus').textContent = space.campus || '-';
            document.getElementById('detailFloor').textContent = space.floor || '-';
            document.getElementById('detailCapacity').textContent = space.capacity ? `${space.capacity} personne(s)` : '-';
            document.getElementById('detailArea').textContent = space.area ? `${space.area} m²` : '-';
            document.getElementById('detailDescription').textContent = space.description || 'Aucune description.';

            const statusBadge = document.getElementById('detailStatus');
            statusBadge.textContent = statusLabels[space.status] || space.status || '-';
            statusBadge.className = 'inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ' + (statusClasses[space.status] || statusClasses.unavailable);

            const prices = [];

            if (space.price_hour) {
                prices.push(`${Number(space.price_hour).toFixed(2)} DH / heure`);
            }

            if (space.price_day) {
                prices.push(`${Number(space.price_day).toFixed(2)} DH / jour`);
            }

            if (space.price_month) {
                prices.push(`${Number(space.price_month).toFixed(2)} DH / mois`);
            }

            document.getElementById('detailPrices').innerHTML = prices.length
                ? prices.map(price => `<div>${price}</div>`).join('')
                : '-';

            const accessoriesContainer = document.getElementById('detailAccessories');

            if (space.accessories && space.accessories.length > 0) {
                accessoriesContainer.innerHTML = space.accessories.map(accessory => {
                    return `<span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">${accessory}</span>`;
                }).join('');
            } else {
                accessoriesContainer.innerHTML = '<span class="text-sm text-slate-500">Aucun équipement.</span>';
            }
        });
    });
</script>
@endsection