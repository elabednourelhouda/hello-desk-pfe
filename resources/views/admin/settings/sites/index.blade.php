@extends('layouts.app')

@section('title', 'Sites - Administration')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-5xl px-6 py-8">

        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-medium text-[#284625]">
                    <a href="{{ route('admin.settings.index') }}" class="hover:underline">Configuration</a>
                    /
                    Sites
                </p>
                <h1 class="mt-1 text-3xl font-bold text-gray-900">Sites</h1>
                <p class="mt-2 text-sm text-gray-500">
                    Ces valeurs alimentent le champ « Site souhaité » et les filtres par site
                    partout dans l’application.
                </p>
            </div>

            <a href="{{ route('admin.settings.sites.create') }}"
                class="inline-flex items-center justify-center rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                + Ajouter un site
            </a>
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

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Nom</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Code</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Ville</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Étages / Espaces</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Statut</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($sites as $site)
                        <tr>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                {{ $site->name }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $site->code ?? '—' }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $site->city ?? '—' }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $site->floors_count }} étage(s) · {{ $site->spaces_count }} espace(s)
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                        {{ $site->is_active
                                            ? 'bg-green-100 text-green-700 ring-1 ring-green-500/20'
                                            : 'bg-gray-100 text-gray-500 ring-1 ring-gray-400/20' }}">
                                    {{ $site->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.settings.sites.show', $site) }}"
                                        class="inline-flex h-9 items-center justify-center rounded-xl bg-sky-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-sky-700">
                                        Étages
                                    </a>

                                    <a href="{{ route('admin.settings.sites.edit', $site) }}"
                                        class="inline-flex h-9 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-bold text-blue-700 transition hover:bg-blue-100">
                                        Modifier
                                    </a>

                                    <form method="POST"
                                        action="{{ route('admin.settings.sites.toggleActive', $site) }}">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                            class="inline-flex h-9 items-center justify-center rounded-xl border px-4 text-sm font-bold transition
                                                        {{ $site->is_active
                                                            ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                                            : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                            {{ $site->is_active ? 'Désactiver' : 'Activer' }}
                                        </button>
                                    </form>

                                    <form method="POST"
                                        action="{{ route('admin.settings.sites.destroy', $site) }}"
                                        onsubmit="return confirm('Supprimer définitivement ce site ?');">
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
                            <td colspan="6" class="px-6 py-14 text-center text-sm text-gray-500">
                                Aucun site enregistré pour le moment.
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