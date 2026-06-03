@extends('layouts.app')

@section('title', 'Ajouter un commercial - Hello Desk')

@section('content')
<div class="mx-auto max-w-4xl px-6 py-8">

    <div>
        <a href="{{ route('admin.commercials.index') }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour aux commerciaux
        </a>

        <h1 class="mt-4 text-3xl font-bold text-gray-900">
            Ajouter un commercial
        </h1>

        <p class="mt-2 text-sm text-gray-600">
            Créez le compte du commercial et affectez-le directement à un campus ou à un étage.
        </p>
    </div>

    @if(session('error'))
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.commercials.store') }}"
          class="mt-8 space-y-6">
        @csrf

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">
                Informations du compte
            </h2>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Nom complet
                    </label>

                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                           placeholder="Ex: Yassine Amrani">

                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Email professionnel
                    </label>

                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                           placeholder="commercial@hellodesk.ma">

                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                <p class="font-semibold">
                    Mot de passe temporaire
                </p>

                <p class="mt-1 leading-6">
                    Le système va générer automatiquement un mot de passe temporaire.
                    Le commercial devra le changer lors de sa première connexion.
                </p>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">
                Affectation du commercial
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Choisissez le campus, puis l’étage si le commercial doit gérer un étage précis.
            </p>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Campus
                    </label>

                    <select id="campus_id"
                            name="campus_id"
                            required
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Choisir un campus</option>

                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('campus_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Étage
                    </label>

                    <select id="floor_id"
                        name="floor_id"
                        data-old-floor="{{ old('floor_id') }}"
                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <option value="">Tous les étages du campus</option>

                    @foreach($campuses as $campus)
                        @foreach($campus->floors as $floor)
                            <option value="{{ $floor->id }}"
                                    data-campus-id="{{ $campus->id }}"
                                    @selected(old('floor_id') == $floor->id)>
                                {{ $floor->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>

                    <p class="mt-2 text-xs text-gray-500">
                        Exemple : Centre Ville peut avoir 1er étage, 2e étage ou 6e étage. Ce n’est pas le rez-de-chaussée.
                    </p>

                    @error('floor_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-5">
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Notes internes
                </label>

                <textarea name="notes"
                          rows="3"
                          class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                          placeholder="Ex: Responsable des visites et clients du 2e étage.">{{ old('notes') }}</textarea>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.commercials.index') }}"
               class="rounded-xl border border-gray-300 px-5 py-3 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Annuler
            </a>

            <button type="submit"
                    class="rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#20391f]">
                Créer le commercial
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const campusSelect = document.getElementById('campus_id');
        const floorSelect = document.getElementById('floor_id');

        if (!campusSelect || !floorSelect) {
            return;
        }

        const allFloorOptions = Array.from(
            floorSelect.querySelectorAll('option[data-campus-id]')
        );

        function updateFloorOptions() {
            const selectedCampusId = campusSelect.value;

            allFloorOptions.forEach(function (option) {
                const belongsToSelectedCampus = option.dataset.campusId === selectedCampusId;

                option.hidden = !belongsToSelectedCampus;
                option.disabled = !belongsToSelectedCampus;

                if (!belongsToSelectedCampus && option.selected) {
                    option.selected = false;
                }
            });

            if (!selectedCampusId) {
                floorSelect.value = '';
            }
        }

        campusSelect.addEventListener('change', function () {
            floorSelect.value = '';
            updateFloorOptions();
        });

        updateFloorOptions();
    });
</script>
@endsection