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
            Créez le compte du commercial et affectez-le directement à un site ou à un étage.
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
                        Nom complet <span class="text-red-500">*</span>
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
                        Email professionnel <span class="text-red-500">*</span>
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
                Choisissez si le commercial gère tous les sites, un site complet ou plusieurs étages.
            </p>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Type d’affectation <span class="text-red-500">*</span>
                    </label>

                    <select id="assignment_type"
                        name="assignment_type"
                        required
                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="campus" @selected(old('assignment_type', 'campus' )==='campus' )>
                            Un site complet
                        </option>
                        <option value="floors" @selected(old('assignment_type')==='floors' )>
                            Plusieurs étages
                        </option>
                        <option value="all_campuses" @selected(old('assignment_type')==='all_campuses' )>
                            Tous les sites
                        </option>
                    </select>

                    @error('assignment_type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div id="campus_block">
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Campus <span class="text-red-500">*</span>
                    </label>

                    <select id="campus_id"
                        name="campus_id"
                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Choisir un site</option>

                        @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" @selected(old('campus_id')==$campus->id)>
                            {{ $campus->name }}
                        </option>
                        @endforeach
                    </select>

                    @error('campus_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div id="floors_block" class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                <p class="text-sm font-bold text-gray-800">
                    Étages à affecter
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    Sélectionnez un ou plusieurs étages du site choisi.
                </p>

                <div class="mt-4 space-y-3">
                    @foreach($campuses as $campus)
                    <div data-floor-campus-id="{{ $campus->id }}" class="hidden space-y-2">
                        @forelse($campus->floors as $floor)
                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700">
                            <input type="checkbox"
                                name="floor_ids[]"
                                value="{{ $floor->id }}"
                                class="rounded border-gray-300 text-[#284625] focus:ring-[#284625]"
                                @checked(in_array($floor->id, old('floor_ids', [])) )>
                            <span>{{ $floor->name }}</span>
                        </label>
                        @empty
                        <p class="rounded-xl bg-white px-4 py-3 text-sm text-gray-500">
                            Aucun étage actif pour ce site.
                        </p>
                        @endforelse
                    </div>
                    @endforeach
                </div>

                @error('floor_ids')
                <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
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
    document.addEventListener('DOMContentLoaded', function() {
        const assignmentType = document.getElementById('assignment_type');
        const campusBlock = document.getElementById('campus_block');
        const campusSelect = document.getElementById('campus_id');
        const floorsBlock = document.getElementById('floors_block');
        const floorGroups = document.querySelectorAll('[data-floor-campus-id]');
        const floorCheckboxes = document.querySelectorAll('input[name="floor_ids[]"]');

        if (!assignmentType || !campusSelect || !floorsBlock) {
            return;
        }

        function updateAssignmentUi() {
            const type = assignmentType.value;
            const selectedCampusId = campusSelect.value;

            campusBlock.classList.toggle('hidden', type === 'all_campuses');
            floorsBlock.classList.toggle('hidden', type !== 'floors');

            campusSelect.required = type !== 'all_campuses';

            floorGroups.forEach(function(group) {
                const isVisible = type === 'floors' && group.dataset.floorCampusId === selectedCampusId;

                group.classList.toggle('hidden', !isVisible);

                group.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
                    checkbox.disabled = !isVisible;

                    if (!isVisible) {
                        checkbox.checked = false;
                    }
                });
            });

            if (type !== 'floors') {
                floorCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = false;
                    checkbox.disabled = true;
                });
            }
        }

        assignmentType.addEventListener('change', updateAssignmentUi);
        campusSelect.addEventListener('change', updateAssignmentUi);

        updateAssignmentUi();
    });
</script>
@endsection