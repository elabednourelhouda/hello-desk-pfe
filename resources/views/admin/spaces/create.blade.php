@extends('layouts.app')

@section('title', 'Ajouter un espace - Administration')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-3xl px-6 py-8">

        <p class="text-sm font-medium text-[#284625]">
            <a href="{{ route('admin.spaces.index') }}" class="hover:underline">Espaces</a>
            /
            Ajouter
        </p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Ajouter un espace</h1>

        @if($errors->any())
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('admin.spaces.store') }}"
              class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            @csrf

            <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Emplacement</h2>

            <div class="mt-4 grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Site <span class="text-red-500">*</span>
                    </label>
                    <select name="campus_id"
                            required
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Choisir un site</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected(old('campus_id', $prefillCampusId) == $campus->id)>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('campus_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Étage <span class="text-red-500">*</span>
                    </label>
                    <select name="floor_id"
                            required
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Choisir un étage</option>
                        @foreach($floors as $floor)
                            <option value="{{ $floor->id }}" @selected(old('floor_id', $prefillFloorId) == $floor->id)>
                                {{ $floor->campus->name }} - {{ $floor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('floor_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Doit appartenir au site choisi ci-dessus.</p>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Type d’espace <span class="text-red-500">*</span>
                    </label>
                    <select name="space_type_id"
                            required
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Choisir un type</option>
                        @foreach($spaceTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('space_type_id') == $type->id)>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('space_type_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Statut <span class="text-red-500">*</span>
                    </label>
                    <select name="status"
                            required
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'available') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <h2 class="mt-8 text-sm font-bold uppercase tracking-wide text-gray-500">Informations générales</h2>

            <div class="mt-4 grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Nom <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           placeholder="Ex: Bureau A1"
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Code
                    </label>
                    <input type="text"
                           name="code"
                           value="{{ old('code') }}"
                           placeholder="Ex: CV-RDC-A1"
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Capacité (personnes)
                    </label>
                    <input type="number"
                           min="1"
                           name="capacity"
                           value="{{ old('capacity') }}"
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    @error('capacity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Surface (m²)
                    </label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="surface"
                           value="{{ old('surface') }}"
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    @error('surface')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <h2 class="mt-8 text-sm font-bold uppercase tracking-wide text-gray-500">Tarification</h2>

            <div class="mt-4 grid gap-5 md:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Prix / heure (DH)
                    </label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           id="price_per_hour"
                           name="price_per_hour"
                           value="{{ old('price_per_hour') }}"
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    @error('price_per_hour')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 flex items-center justify-between text-sm font-semibold text-gray-700">
                        <span>Prix / demi-journée (DH)</span>
                        <button type="button"
                                id="half-day-auto-btn"
                                class="text-xs font-semibold text-[#284625] hover:underline">
                            = 50% du jour
                        </button>
                    </label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           id="price_per_half_day"
                           name="price_per_half_day"
                           value="{{ old('price_per_half_day') }}"
                           placeholder="Auto : 50% du prix / jour"
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <p class="mt-1 text-xs text-gray-500">
                        Laissez vide pour appliquer automatiquement 50% du prix / jour.
                    </p>
                    @error('price_per_half_day')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Prix / jour (DH)
                    </label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           id="price_per_day"
                           name="price_per_day"
                           value="{{ old('price_per_day') }}"
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    @error('price_per_day')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Prix / mois (DH)
                    </label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="price_per_month"
                           value="{{ old('price_per_month') }}"
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    @error('price_per_month')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <script>
                document.getElementById('half-day-auto-btn')?.addEventListener('click', function () {
                    const dayInput = document.getElementById('price_per_day');
                    const halfDayInput = document.getElementById('price_per_half_day');
                    const dayValue = parseFloat(dayInput.value);

                    if (!isNaN(dayValue)) {
                        halfDayInput.value = (dayValue / 2).toFixed(2);
                    }
                });
            </script>

            <h2 class="mt-8 text-sm font-bold uppercase tracking-wide text-gray-500">Équipements</h2>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                @forelse($accessories as $accessory)
                    <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                        <input type="checkbox"
                               name="accessory_ids[]"
                               value="{{ $accessory->id }}"
                               @checked(collect(old('accessory_ids', []))->contains($accessory->id))
                               class="h-5 w-5 rounded border-gray-300 text-[#284625] focus:ring-[#284625]">
                        {{ $accessory->name }}
                    </label>
                @empty
                    <p class="text-sm text-gray-500">
                        Aucun équipement configuré. Ajoutez-en depuis la table "accessories".
                    </p>
                @endforelse
            </div>

            <h2 class="mt-8 text-sm font-bold uppercase tracking-wide text-gray-500">Description</h2>

            <div class="mt-4 grid gap-5">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Description publique
                    </label>
                    <textarea name="description"
                              rows="3"
                              placeholder="Visible par les commerciaux et clients."
                              class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Notes internes
                    </label>
                    <textarea name="notes"
                              rows="3"
                              placeholder="Visible uniquement par l’équipe interne (admin/commercial)."
                              class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-3">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-5 w-5 rounded border-gray-300 text-[#284625] focus:ring-[#284625]">
                    <span class="text-sm font-semibold text-gray-700">Actif (visible sur la carte interactive)</span>
                </label>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.spaces.index') }}"
                   class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-300 px-6 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                    Annuler
                </a>

                <button type="submit"
                        class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                    Créer l’espace
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
