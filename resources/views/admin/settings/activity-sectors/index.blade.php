@extends('layouts.app')

@section('title', 'Secteurs d’activité - Administration')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-5xl px-6 py-8">

        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-medium text-[#284625]">
                    <a href="{{ route('admin.settings.index') }}" class="hover:underline">Configuration</a>
                    /
                    Secteurs d’activité
                </p>
                <h1 class="mt-1 text-3xl font-bold text-gray-900">Secteurs d’activité</h1>
                <p class="mt-2 text-sm text-gray-500">
                    Ces valeurs alimentent le champ « Secteur d’activité » dans le formulaire
                    de création/édition d’un prospect.
                </p>
            </div>

            <a href="{{ route('admin.settings.activity-sectors.create') }}"
               class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                + Nouveau secteur
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-4">Nom</th>
                            <th class="px-6 py-4">Prospects</th>
                            <th class="px-6 py-4">Statut</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($activitySectors as $activitySector)
                            <tr>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                                    {{ $activitySector->name }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $activitySector->prospects_count }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold
                                        {{ $activitySector->is_active
                                            ? 'bg-green-100 text-green-700 ring-1 ring-green-500/20'
                                            : 'bg-gray-100 text-gray-500 ring-1 ring-gray-400/20' }}">
                                        {{ $activitySector->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.settings.activity-sectors.edit', $activitySector) }}"
                                           class="inline-flex h-9 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-bold text-blue-700 transition hover:bg-blue-100">
                                            Modifier
                                        </a>

                                        <form method="POST"
                                              action="{{ route('admin.settings.activity-sectors.toggleActive', $activitySector) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex h-9 items-center justify-center rounded-xl border px-4 text-sm font-bold transition
                                                        {{ $activitySector->is_active
                                                            ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                                            : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                                {{ $activitySector->is_active ? 'Désactiver' : 'Activer' }}
                                            </button>
                                        </form>

                                        <form method="POST"
                                              action="{{ route('admin.settings.activity-sectors.destroy', $activitySector) }}"
                                              onsubmit="return confirm('Supprimer définitivement ce secteur ?');">
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
                                <td colspan="4" class="px-6 py-14 text-center text-sm text-gray-500">
                                    Aucun secteur d’activité enregistré pour le moment.
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