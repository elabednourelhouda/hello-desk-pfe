@extends('layouts.app')

@section('title', 'Créer une réservation - Espace commercial')

@section('content')
@php
    $commercialMapUrl = \Illuminate\Support\Facades\Route::has('commercial.interactive-map.index')
        ? route('commercial.interactive-map.index')
        : route('commercial.dashboard');

    $selectedSpaceMapUrl = $selectedSpace && \Illuminate\Support\Facades\Route::has('commercial.interactive-map.index')
        ? route('commercial.interactive-map.index', [
            'campus_id' => $selectedSpace->campus_id,
            'floor_id' => $selectedSpace->floor_id,
        ])
        : $commercialMapUrl;
@endphp

<div class="mx-auto max-w-5xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ $commercialMapUrl }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour à la carte interactive
        </a>

        <h1 class="mt-4 text-2xl font-bold text-gray-900">
            Créer une réservation
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Créer une réservation pour un client de votre périmètre et préparer son contrat brouillon automatiquement.
        </p>
    </div>

    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Veuillez corriger les erreurs suivantes:</p>

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
                        Espace sélectionné
                    </p>

                    <h2 class="mt-2 text-xl font-bold text-gray-900">
                        {{ $selectedSpace->name }}
                    </h2>

                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                        <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                            Code: {{ $selectedSpace->code }}
                        </span>

                        <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                            Type: {{ $selectedSpace->spaceType?->name ?? 'Non spécifié' }}
                        </span>

                        <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                            Site: {{ $selectedSpace->campus?->name ?? 'Non spécifié' }}
                        </span>

                        <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                            Étage : {{ $selectedSpace->floor?->name ?? 'Non spécifié' }}
                        </span>

                        <span class="rounded-full bg-green-50 px-3 py-1 text-green-700">
                            Statut: {{ ucfirst($selectedSpace->status) }}
                        </span>
                    </div>

                    <div class="mt-4 grid gap-3 text-sm text-gray-700 sm:grid-cols-3">
                        <div class="rounded-xl bg-white px-4 py-3">
                            <p class="text-xs font-semibold uppercase text-gray-400">Prix horaire</p>
                            <p class="mt-1 font-bold">
                                {{ number_format($selectedSpace->display_price_per_hour, 2) }} MAD
                            </p>
                        </div>

                        <div class="rounded-xl bg-white px-4 py-3">
                            <p class="text-xs font-semibold uppercase text-gray-400">Prix journalier</p>
                            <p class="mt-1 font-bold">
                                {{ number_format($selectedSpace->display_price_per_day, 2) }} MAD
                            </p>
                        </div>

                        <div class="rounded-xl bg-white px-4 py-3">
                            <p class="text-xs font-semibold uppercase text-gray-400">Prix mensuel</p>
                            <p class="mt-1 font-bold">
                                {{ number_format($selectedSpace->display_price_per_month, 2) }} MAD
                            </p>
                        </div>
                    </div>
                </div>

                <a href="{{ $selectedSpaceMapUrl }}"
                   class="rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Changer d'espace
                </a>
            </div>
        </section>
    @endif

    <form method="POST"
          action="{{ route('commercial.reservations.store') }}"
          class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Client <span class="text-red-500">*</span>
                </label>

                <select name="client_id"
                        required
                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <option value="">Sélectionner un client</option>

                    @foreach($clients as $client)
                        @php
                            $hasCompleteLegalFile = $client->hasCompleteLegalFile();
                        @endphp

                        <option value="{{ $client->id }}"
                                @selected(old('client_id') == $client->id)
                                @disabled(! $hasCompleteLegalFile)>
                            {{ $client->full_name }} — {{ $client->email }}
                            —
                            Dossier juridique: {{ $hasCompleteLegalFile ? 'complet' : 'incomplet' }}
                            @if(! $hasCompleteLegalFile)
                                (à compléter avant réservation)
                            @endif
                        </option>
                    @endforeach
                </select>

                <p class="mt-2 text-xs text-gray-500">
                    Seuls les clients de votre périmètre avec un dossier juridique complet et sans risque détecté peuvent avoir une réservation et un contrat.
                </p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Espace <span class="text-red-500">*</span>
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
                        <option value="">Sélectionner un espace</option>

                        @foreach($spaces as $space)
                            @php
                                $spaceStatus = mb_strtolower($space->status ?? 'available');

                                $isNotReservable = in_array($spaceStatus, [
                                    'occupied',
                                    'reserved',
                                    'unavailable',
                                    'maintenance',
                                    'in maintenance',

                                    'occupé',
                                    'occupe',
                                    'réservé',
                                    'reserve',
                                    'réservée',
                                    'indisponible',
                                    'en maintenance',
                                ], true);
                            @endphp

                            <option value="{{ $space->id }}"
                                    @selected(old('space_id') == $space->id)
                                    @disabled($isNotReservable)>
                                {{ $space->name }}
                                —
                                {{ $space->code }}
                                —
                                {{ ucfirst($space->status) }}
                                @if($isNotReservable)
                                    (non réservable)
                                @endif
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Date et heure de début <span class="text-red-500">*</span>
                </label>

                <input type="datetime-local"
                       name="starts_at"
                       value="{{ old('starts_at') }}"
                       required
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Date et heure de fin <span class="text-red-500">*</span>
                </label>

                <input type="datetime-local"
                       name="ends_at"
                       value="{{ old('ends_at') }}"
                       required
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Type de durée <span class="text-red-500">*</span>
                </label>

                <select name="duration_type"
                        required
                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    @foreach($durationTypes ?? [
                        'hourly' => 'À l’heure',
                        'daily' => 'À la journée',
                        'monthly' => 'Au mois',
                        'custom' => 'Personnalisée',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('duration_type', 'custom') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Durée d’engagement <span class="text-red-500">*</span>
                    </label>

                    <input type="number"
                           name="engagement_duration_value"
                           min="1"
                           max="999"
                           required
                           value="{{ old('engagement_duration_value', 1) }}"
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Unité <span class="text-red-500">*</span>
                    </label>

                    <select name="engagement_duration_unit"
                            required
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="hour" @selected(old('engagement_duration_unit') === 'hour')>Heure</option>
                        <option value="half_day" @selected(old('engagement_duration_unit') === 'half_day')>Demi-journée</option>
                        <option value="day" @selected(old('engagement_duration_unit') === 'day')>Jour</option>
                        <option value="month" @selected(old('engagement_duration_unit', 'month') === 'month')>Mois</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Prix négocié <span class="text-red-500">*</span>
                </label>

                <input type="number"
                       step="0.01"
                       min="0"
                       name="negotiated_price"
                       required
                       value="{{ old('negotiated_price', $selectedSpace?->display_price_per_day ?? $selectedSpace?->display_price_per_hour ?? $selectedSpace?->display_price_per_month ?? 0) }}"
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

                <p class="mt-2 text-xs text-gray-500">
                    Ce prix peut être différent du prix officiel de l'espace.
                </p>
            </div>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Titre du contrat <span class="text-gray-400">(optionnel)</span>
            </label>

            <input type="text"
                   name="contract_title"
                   value="{{ old('contract_title') }}"
                   placeholder="Exemple : Contrat bureau privé - Juin 2026"
                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

            <p class="mt-2 text-xs text-gray-500">
                Un contrat brouillon sera créé automatiquement avec cette réservation.
                Le PDF signé pourra être téléchargé plus tard à partir de la page du contrat.
            </p>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Notes internes
            </label>

            <textarea name="notes"
                      rows="4"
                      class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                      placeholder="Notes commerciales, conditions spéciales, détails internes...">{{ old('notes') }}</textarea>
        </div>

        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <p class="font-semibold">Règle métier importante</p>
            <p class="mt-1">
                Chaque réservation doit être liée à un contrat. Pour cette raison, le système créera automatiquement un contrat brouillon après avoir enregistré la réservation.
            </p>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ $selectedSpace ? $selectedSpaceMapUrl : route('commercial.reservations.index') }}"
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