@extends('layouts.app')

@section('title', $site->name . ' - Administration')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-5xl px-6 py-8">

        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-medium text-[#284625]">
                    <a href="{{ route('admin.settings.index') }}" class="hover:underline">Configuration</a>
                    /
                    <a href="{{ route('admin.settings.sites.index') }}" class="hover:underline">Sites</a>
                    /
                    {{ $site->name }}
                </p>
                <h1 class="mt-1 text-3xl font-bold text-gray-900">{{ $site->name }}</h1>
                <p class="mt-2 text-sm text-gray-500">
                    {{ $site->floors->count() }} étage(s) · {{ $site->spaces_count }} espace(s) au total.
                    Gérez ici les étages de ce site, puis les espaces à l’intérieur de chaque étage.
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('admin.settings.sites.edit', $site) }}"
                   class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                    Modifier le site
                </a>

                <a href="{{ route('admin.settings.sites.floors.create', $site) }}"
                   class="inline-flex items-center justify-center rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                    + Ajouter un étage
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

        <div class="grid gap-4">
            @forelse($site->floors as $floor)
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-lg font-bold text-gray-900">{{ $floor->name }}</h2>

                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                    {{ $floor->is_active
                                        ? 'bg-green-100 text-green-700 ring-1 ring-green-500/20'
                                        : 'bg-gray-100 text-gray-500 ring-1 ring-gray-400/20' }}">
                                    {{ $floor->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>

                            <p class="mt-1 text-sm text-gray-500">
                                {{ $floor->code ?? 'Sans code' }}
                                @if(!is_null($floor->level))
                                    · Niveau {{ $floor->level }}
                                @endif
                                · {{ $floor->spaces_count }} espace(s)
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('admin.settings.sites.floors.show', [$site, $floor]) }}"
                               class="inline-flex h-10 items-center justify-center rounded-xl bg-sky-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-sky-700">
                                Voir les espaces
                            </a>

                            <a href="{{ route('admin.settings.sites.floors.edit', [$site, $floor]) }}"
                               class="inline-flex h-10 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-bold text-blue-700 transition hover:bg-blue-100">
                                Modifier
                            </a>

                            <form method="POST"
                                  action="{{ route('admin.settings.sites.floors.toggleActive', [$site, $floor]) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                        class="inline-flex h-10 items-center justify-center rounded-xl border px-4 text-sm font-bold transition
                                            {{ $floor->is_active
                                                ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                                : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                    {{ $floor->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>

                            <form method="POST"
                                  action="{{ route('admin.settings.sites.floors.destroy', [$site, $floor]) }}"
                                  onsubmit="return confirm('Supprimer définitivement cet étage ?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="inline-flex h-10 items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-bold text-red-700 transition hover:bg-red-100">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center">
                    <p class="font-semibold text-gray-800">Aucun étage pour ce site.</p>
                    <p class="mt-1 text-sm text-gray-500">
                        Ajoutez un premier étage pour pouvoir y créer des espaces.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
