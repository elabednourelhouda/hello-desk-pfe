@extends('layouts.app')

@section('title', 'Espaces - Administration')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-7xl px-6 py-8">

        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-medium text-[#284625]">Administration</p>
                <h1 class="mt-1 text-3xl font-bold text-gray-900">Gestion des espaces</h1>
                <p class="mt-2 text-sm text-gray-500">
                    Consultez les bureaux, salles de réunion et positions par site et par étage.
                </p>
            </div>

            <a href="#"
                class="inline-flex items-center justify-center rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                + Ajouter un espace
            </a>
        </div>

        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.spaces.index') }}" class="grid gap-4 md:grid-cols-5">

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Site</label>
                    <select name="campus_id" class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les sites</option>
                        @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" @selected(request('campus_id')==$campus->id)>
                            {{ $campus->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Étage</label>
                    <select name="floor_id" class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les étages</option>
                        @foreach($floors as $floor)
                        <option value="{{ $floor->id }}" @selected(request('floor_id')==$floor->id)>
                            {{ $floor->campus->name }} - {{ $floor->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                    <select name="space_type_id" class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les types</option>
                        @foreach($spaceTypes as $type)
                        <option value="{{ $type->id }}" @selected(request('space_type_id')==$type->id)>
                            {{ $type->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Statut</label>
                    <select name="status" class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Tous les statuts</option>
                        <option value="available" @selected(request('status')==='available' )>Disponible</option>
                        <option value="occupied" @selected(request('status')==='occupied' )>Occupé</option>
                        <option value="reserved" @selected(request('status')==='reserved' )>Réservé</option>
                        <option value="unavailable" @selected(request('status')==='unavailable' )>Indisponible</option>
                        <option value="maintenance" @selected(request('status')==='maintenance' )>En maintenance</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="w-full rounded-xl bg-[#284625] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1f351d]">
                        Filtrer
                    </button>

                    <a href="{{ route('admin.spaces.index') }}"
                        class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Espace</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Site</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Étage</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Capacité</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Prix</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Statut</th>
                            <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($spaces as $space)
                        @php
                        $statusLabels = [
                        'available' => 'Disponible',
                        'occupied' => 'Occupé',
                        'reserved' => 'Réservé',
                        'unavailable' => 'Indisponible',
                        'maintenance' => 'Maintenance',
                        ];

                        $statusClasses = [
                        'available' => 'bg-green-50 text-green-700 ring-green-600/20',
                        'occupied' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                        'reserved' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                        'unavailable' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
                        'maintenance' => 'bg-red-50 text-red-700 ring-red-600/20',
                        ];
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $space->name }}</div>
                                <div class="text-xs text-gray-500">{{ $space->internal_code ?? 'Sans code' }}</div>
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-700">
                                {{ $space->campus->name ?? '-' }}
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-700">
                                {{ $space->floor->name ?? '-' }}
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-700">
                                {{ $space->spaceType->name ?? '-' }}
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-700">
                                {{ $space->capacity ?? '-' }} pers.
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-700">
                                @if($space->price_per_hour)
                                <div>{{ number_format($space->price_per_hour, 2) }} DH / h</div>
                                @endif

                                @if($space->price_per_day)
                                <div>{{ number_format($space->price_per_day, 2) }} DH / jour</div>
                                @endif

                                @if($space->price_per_month)
                                <div>{{ number_format($space->price_per_month, 2) }} DH / mois</div>
                                @endif

                                @if(!$space->price_per_hour && !$space->price_per_day && !$space->price_per_month)
                                -
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses[$space->status] ?? 'bg-gray-100 text-gray-700 ring-gray-500/20' }}">
                                    {{ $statusLabels[$space->status] ?? $space->status }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right text-sm font-medium">
                                <a href="{{ route('admin.spaces.show', $space) }}" class="text-[#284625] hover:underline">Voir</a>
                                <span class="mx-2 text-gray-300">|</span>
                                <a href="{{ route('admin.spaces.edit', $space) }}" class="text-gray-700 hover:underline">Modifier</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500">
                                Aucun espace trouvé.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-5 py-4">
                {{ $spaces->links() }}
            </div>
        </div>
    </div>
</div>
@endsection