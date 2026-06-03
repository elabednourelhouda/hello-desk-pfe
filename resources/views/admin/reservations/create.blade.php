@extends('layouts.app')

@section('title', 'Créer une réservation - Hello Desk')

@section('content')
<div class="mx-auto max-w-5xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.interactive-map.index') }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour à la carte interactive
        </a>

        <h1 class="mt-4 text-2xl font-bold text-gray-900">
            Créer une réservation
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Complétez les informations de réservation pour le client sélectionné.
        </p>
    </div>

    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Veuillez corriger les erreurs suivantes :</p>

            <ul class="mt-2 list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($selectedSpace)
        <section class="mb-6 rounded-2xl border border-[#284625]/20 bg-[#284625]/5 p-6 shadow-sm">
            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-[#284625]">
                        Espace sélectionné depuis la carte
                    </p>

                    <h2 class="mt-2 text-xl font-bold text-gray-900">
                        {{ $selectedSpace->name }}
                    </h2>

                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                        <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                            Code : {{ $selectedSpace->code }}
                        </span>

                        <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                            Type : {{ $selectedSpace->spaceType?->name ?? 'Non précisé' }}
                        </span>

                        <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                            Campus : {{ $selectedSpace->campus?->name ?? 'Non précisé' }}
                        </span>

                        <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                            Étage : {{ $selectedSpace->floor?->name ?? 'Non précisé' }}
                        </span>

                        <span class="rounded-full bg-green-50 px-3 py-1 text-green-700">
                            Statut : {{ $selectedSpace->status }}
                        </span>
                    </div>
                </div>

                <a href="{{ route('admin.interactive-map.index', [
                    'campus_id' => $selectedSpace->campus_id,
                    'floor_id' => $selectedSpace->floor_id,
                ]) }}"
                   class="rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Changer d’espace
                </a>
            </div>
        </section>
    @endif

    <form method="POST"
          action="{{ route('admin.reservations.store') }}"
          class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Client
                </label>

                <select name="client_id"
                        required
                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <option value="">Choisir un client</option>

                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>
                            {{ $client->full_name }} — {{ $client->email }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Espace
                </label>

                @if($selectedSpace)
                    <input type="hidden"
                           name="space_id"
                           value="{{ old('space_id', $selectedSpace->id) }}">

                    <div class="flex h-12 items-center rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-gray-700">
                        {{ $selectedSpace->name }} — {{ $selectedSpace->code }}
                    </div>
                @else
                    <select name="space_id"
                            required
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Choisir un espace</option>

                        @foreach($spaces as $space)
                            <option value="{{ $space->id }}" @selected(old('space_id') == $space->id)>
                                {{ $space->name }} — {{ $space->status }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Date et heure de début
                </label>

                <input type="datetime-local"
                       name="starts_at"
                       value="{{ old('starts_at') }}"
                       required
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Date et heure de fin
                </label>

                <input type="datetime-local"
                       name="ends_at"
                       value="{{ old('ends_at') }}"
                       required
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Type de durée
                </label>

                <select name="duration_type"
                        required
                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <option value="hourly" @selected(old('duration_type') === 'hourly')>
                        À l’heure
                    </option>

                    <option value="daily" @selected(old('duration_type') === 'daily')>
                        À la journée
                    </option>

                    <option value="monthly" @selected(old('duration_type') === 'monthly')>
                        Au mois
                    </option>

                    <option value="custom" @selected(old('duration_type', 'custom') === 'custom')>
                        Personnalisée
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Prix négocié
                </label>

                <input type="number"
                       step="0.01"
                       min="0"
                       name="negotiated_price"
                       value="{{ old('negotiated_price', 0) }}"
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Titre du contrat
            </label>

            <input type="text"
                   name="contract_title"
                   value="{{ old('contract_title') }}"
                   placeholder="Exemple : Contrat bureau privé - Juin 2026"
                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

            <p class="mt-2 text-xs text-gray-500">
                Un contrat brouillon sera créé automatiquement avec cette réservation.
            </p>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Notes internes
            </label>

            <textarea name="notes"
                      rows="4"
                      class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                      placeholder="Notes commerciales, conditions particulières...">{{ old('notes') }}</textarea>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ $selectedSpace
                    ? route('admin.interactive-map.index', ['campus_id' => $selectedSpace->campus_id, 'floor_id' => $selectedSpace->floor_id])
                    : route('admin.reservations.index') }}"
               class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-300 px-5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Annuler
            </a>

            <button type="submit"
                    class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                Créer la réservation
            </button>
        </div>
    </form>
</div>
@endsection