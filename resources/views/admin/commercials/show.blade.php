@extends('layouts.app')

@section('title', 'Détail commercial - Hello Desk')

@section('content')
<div class="mx-auto max-w-6xl px-6 py-8">

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <a href="{{ route('admin.commercials.index') }}"
               class="text-sm font-semibold text-[#284625] hover:underline">
                ← Retour aux commerciaux
            </a>

            <h1 class="mt-4 text-3xl font-bold text-gray-900">
                {{ $commercial->name }}
            </h1>

            <p class="mt-2 text-sm text-gray-600">
                Profil commercial, affectations et accès au compte.
            </p>
        </div>

        <span class="w-fit rounded-full bg-[#284625]/10 px-4 py-2 text-sm font-semibold text-[#284625]">
            Personnel commercial
        </span>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if(session('temporary_password'))
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
            <p class="font-semibold">Identifiants de connexion du commercial</p>

            <div class="mt-3 grid gap-3 md:grid-cols-2">
                <p>
                    Email :
                    <span class="rounded bg-white px-2 py-1 font-mono font-bold">
                        {{ $commercial->email }}
                    </span>
                </p>

                <p>
                    Mot de passe temporaire :
                    <span class="rounded bg-white px-2 py-1 font-mono font-bold">
                        {{ session('temporary_password') }}
                    </span>
                </p>
            </div>

            <p class="mt-3 text-xs leading-5">
                Copiez ces informations et envoyez-les au commercial manuellement.
                Le mot de passe ne sera affiché qu’une seule fois.
            </p>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Left side --}}
        <div class="space-y-6 lg:col-span-2">

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Informations du compte
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Données principales du compte commercial.
                        </p>
                    </div>

                    @if($commercial->must_change_password)
                        <span class="w-fit rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">
                            Mot de passe à changer
                        </span>
                    @else
                        <span class="w-fit rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">
                            Mot de passe changé
                        </span>
                    @endif
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nom complet</p>
                        <p class="mt-1 font-bold text-gray-900">{{ $commercial->name }}</p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Email</p>
                        <p class="mt-1 font-bold text-gray-900">{{ $commercial->email }}</p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Rôle</p>
                        <p class="mt-1 font-bold text-gray-900">Commercial</p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Créé le</p>
                        <p class="mt-1 font-bold text-gray-900">
                            {{ optional($commercial->created_at)->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h2 class="text-lg font-bold text-gray-900">
                        Affectations actuelles
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Périmètre de travail attribué à ce commercial.
                    </p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($commercial->staffAssignments as $assignment)
                        <div class="flex flex-col gap-4 px-6 py-5 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ $assignment->campus->name ?? 'Campus supprimé' }}
                                </p>

                                <p class="mt-1 text-sm text-gray-600">
                                    @if($assignment->floor)
                                        Étage : {{ $assignment->floor->name }}
                                    @else
                                        Tous les étages du campus
                                    @endif
                                </p>

                                @if($assignment->notes)
                                    <p class="mt-2 text-sm text-gray-500">
                                        {{ $assignment->notes }}
                                    </p>
                                @endif

                                <p class="mt-2 text-xs text-gray-400">
                                    Affecté par :
                                    {{ $assignment->assignedBy->name ?? 'Administrateur' }}
                                    · {{ optional($assignment->created_at)->format('d/m/Y') }}
                                </p>
                            </div>

                            <form method="POST"
                                  action="{{ route('admin.commercials.assignments.destroy', [$commercial, $assignment]) }}"
                                  onsubmit="return confirm('Supprimer cette affectation ?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center">
                            <p class="text-sm text-gray-500">
                                Aucune affectation pour ce commercial.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- Right side --}}
        <aside class="space-y-6">

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">
                    Ajouter une affectation
                </h2>

                <p class="mt-2 text-sm leading-6 text-gray-600">
                    Affectez ce commercial à un campus complet ou à un étage précis.
                </p>

                <form method="POST"
                      action="{{ route('admin.commercials.assignments.store', $commercial) }}"
                      class="mt-5 space-y-4">
                    @csrf

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
                            Si vous laissez ce champ vide, le commercial sera affecté à tout le campus.
                        </p>

                        @error('floor_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Notes internes
                        </label>

                        <textarea name="notes"
                                  rows="3"
                                  class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                                  placeholder="Ex: Responsable des visites et clients du 2e étage.">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-[#284625] px-4 py-3 text-sm font-semibold text-white hover:bg-[#20391f]">
                        Enregistrer l’affectation
                    </button>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">
                    Actions du compte
                </h2>

                <form method="POST"
                      action="{{ route('admin.commercials.resetPassword', $commercial) }}"
                      class="mt-5">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            class="w-full rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 hover:bg-amber-100">
                        Réinitialiser le mot de passe
                    </button>
                </form>
            </section>
        </aside>
    </div>
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