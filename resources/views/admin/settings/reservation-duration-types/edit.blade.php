@extends('layouts.app')

@section('title', 'Modifier un type de durée - Administration')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-2xl px-6 py-8">

        <p class="text-sm font-medium text-[#284625]">
            <a href="{{ route('admin.settings.index') }}" class="hover:underline">Configuration</a>
            /
            <a href="{{ route('admin.settings.reservation-duration-types.index') }}" class="hover:underline">Types de durée de réservation</a>
        </p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Modifier « {{ $durationType->name }} »</h1>

        <form method="POST"
              action="{{ route('admin.settings.reservation-duration-types.update', $durationType) }}"
              class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid gap-5">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Nom <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $durationType->name) }}"
                           required
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="code"
                           value="{{ old('code', $durationType->code) }}"
                           required
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <p class="mt-1 text-xs text-gray-500">
                        @if($durationType->reservations()->exists() || $durationType->prospects()->exists())
                            ⚠️ Ce code est déjà utilisé par des réservations ou prospects existants. Le renommer
                            n’affecte pas les enregistrements déjà créés, seulement les nouveaux.
                        @else
                            Valeur technique stockée en base (lettres, chiffres, tirets, underscores).
                        @endif
                    </p>

                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Types d’espaces compatibles
                    </label>
                    <p class="mb-3 text-xs text-gray-500">
                        Cochez les types d’espaces sur lesquels ce type de durée peut être réservé. Ne rien cocher
                        signifie « disponible sur tout type d’espace qui n’a pas de restriction explicite » —
                        et non « disponible nulle part ».
                    </p>

                    <div class="grid gap-2 rounded-xl border border-gray-200 p-4 sm:grid-cols-2">
                        @forelse($spaceTypes as $spaceType)
                            <label class="flex items-center gap-2">
                                <input type="checkbox"
                                       name="space_type_ids[]"
                                       value="{{ $spaceType->id }}"
                                       {{ in_array($spaceType->id, old('space_type_ids', $selectedSpaceTypeIds)) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-[#284625] focus:ring-[#284625]">
                                <span class="text-sm text-gray-700">{{ $spaceType->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">Aucun type d’espace configuré pour le moment.</p>
                        @endforelse
                    </div>

                    @error('space_type_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-3">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $durationType->is_active) ? 'checked' : '' }}
                           class="h-5 w-5 rounded border-gray-300 text-[#284625] focus:ring-[#284625]">
                    <span class="text-sm font-semibold text-gray-700">Actif</span>
                </label>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.settings.reservation-duration-types.index') }}"
                   class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-300 px-6 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                    Annuler
                </a>

                <button type="submit"
                        class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
