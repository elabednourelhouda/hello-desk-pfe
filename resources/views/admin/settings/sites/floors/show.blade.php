@extends('layouts.app')

@section('title', $floor->name . ' - ' . $site->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-6xl px-6 py-8">

        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-medium text-[#284625]">
                    <a href="{{ route('admin.settings.index') }}" class="hover:underline">Configuration</a>
                    /
                    <a href="{{ route('admin.settings.sites.index') }}" class="hover:underline">Sites</a>
                    /
                    <a href="{{ route('admin.settings.sites.show', $site) }}" class="hover:underline">{{ $site->name }}</a>
                    /
                    {{ $floor->name }}
                </p>
                <h1 class="mt-1 text-3xl font-bold text-gray-900">{{ $floor->name }}</h1>
                <p class="mt-2 text-sm text-gray-500">
                    {{ $floor->spaces->count() }} espace(s) sur cet étage.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @if(Route::has('admin.map.index'))
                    <a href="{{ route('admin.map.index', ['campus_id' => $site->id, 'floor_id' => $floor->id]) }}"
                       class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                        Voir sur la carte
                    </a>
                @endif

                <a href="{{ route('admin.settings.sites.floors.edit', [$site, $floor]) }}"
                   class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                    Modifier l’étage
                </a>

                <a href="{{ route('admin.spaces.create', ['campus_id' => $site->id, 'floor_id' => $floor->id]) }}"
                   class="inline-flex items-center justify-center rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                    + Ajouter un espace
                </a>
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

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Espace</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Capacité</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tarifs</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Statut</th>
                            <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($floor->spaces as $space)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $space->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $space->code ?? 'Sans code' }}</div>
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
                                        {{ $statuses[$space->status] ?? $space->status }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.spaces.show', $space) }}"
                                           class="inline-flex h-9 items-center justify-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-bold text-gray-700 transition hover:bg-gray-100">
                                            Voir
                                        </a>

                                        <a href="{{ route('admin.spaces.edit', $space) }}"
                                           class="inline-flex h-9 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-bold text-blue-700 transition hover:bg-blue-100">
                                            Modifier
                                        </a>

                                        <form method="POST"
                                              action="{{ route('admin.spaces.destroy', $space) }}"
                                              onsubmit="return confirm('Supprimer définitivement cet espace ?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="inline-flex h-9 items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-bold text-red-700 transition hover:bg-red-100">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center text-sm text-gray-500">
                                    Aucun espace sur cet étage pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection