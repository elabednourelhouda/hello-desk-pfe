@extends('layouts.app')

@section('title', 'Détails de la réservation - Espace commercial')

@section('content')
@php
    // Was a hardcoded array — now driven by Configuration -> Types de
    // durée de réservation, so a renamed/added duration type displays
    // correctly here too, instead of falling back to the raw code.
    $durationLabels = \App\Models\ReservationDurationType::pluck('name', 'code')->all();

    $engagementDurationValue = $reservation->engagement_duration_value;

    $engagementUnitLabels = [
        'hour' => (int) $engagementDurationValue === 1 ? 'heure' : 'heures',
        'half_day' => (int) $engagementDurationValue === 1 ? 'demi-journée' : 'demi-journées',
        'day' => (int) $engagementDurationValue === 1 ? 'jour' : 'jours',
        'month' => 'mois',
    ];
    $reservationStatusLabels = $reservationStatuses ?? [
        'pending' => 'En attente',
        'confirmed' => 'Confirmée',
        'in_progress' => 'En cours',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
        'expired' => 'Expirée',
    ];

    $reservationStatusClasses = [
        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
        'in_progress' => 'bg-green-50 text-green-700 border-green-200',
        'completed' => 'bg-gray-100 text-gray-700 border-gray-200',
        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
        'expired' => 'bg-orange-50 text-orange-700 border-orange-200',
    ];

    $contractStatusLabels = [
        'draft' => 'Brouillon',
        'active' => 'Actif',
        'expired' => 'Expiré',
        'cancelled' => 'Annulé',
    ];

    $contractStatusClasses = [
        'draft' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'active' => 'bg-green-50 text-green-700 border-green-200',
        'expired' => 'bg-orange-50 text-orange-700 border-orange-200',
        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
    ];

    $reservationStatusClass = $reservationStatusClasses[$reservation->status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
    $contractStatus = $reservation->contract?->status;
    $contractStatusClass = $contractStatusClasses[$contractStatus] ?? 'bg-gray-100 text-gray-700 border-gray-200';

    $commercialMapUrl = \Illuminate\Support\Facades\Route::has('commercial.interactive-map.index')
        ? route('commercial.interactive-map.index')
        : route('commercial.dashboard');

    $reservationMapUrl = $reservation->space && \Illuminate\Support\Facades\Route::has('commercial.interactive-map.index')
        ? route('commercial.interactive-map.index', [
            'campus_id' => $reservation->space->campus_id,
            'floor_id' => $reservation->space->floor_id,
        ])
        : $commercialMapUrl;
@endphp

<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <a href="{{ route('commercial.reservations.index') }}"
               class="text-sm font-semibold text-[#284625] hover:underline">
                ← Retour aux réservations
            </a>

            <h1 class="mt-4 text-2xl font-bold text-gray-900">
                Détails de la réservation
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Consultez la réservation, le contrat lié et les prochaines actions commerciales.
            </p>
        </div>

        @if($reservation->space)
            <a href="{{ $reservationMapUrl }}"
               class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Voir sur la carte
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Informations de la réservation
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Période, client, espace et informations commerciales liées à cette réservation.
                        </p>
                    </div>

                    <span class="inline-flex w-fit rounded-full border px-3 py-1 text-xs font-semibold {{ $reservationStatusClass }}">
                        {{ $reservationStatusLabels[$reservation->status] ?? ucfirst($reservation->status) }}
                    </span>
                </div>

                <dl class="mt-6 grid gap-5 md:grid-cols-2">
                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Client</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $reservation->client?->full_name ?? 'Client supprimé' }}
                        </dd>

                        @if($reservation->client?->email)
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $reservation->client->email }}
                            </p>
                        @endif
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Espace</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $reservation->space?->name ?? 'Espace supprimé' }}
                        </dd>

                        <p class="mt-1 text-xs text-gray-500">
                            {{ $reservation->space?->code ?? 'Aucun code' }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Site</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $reservation->campus?->name ?? $reservation->space?->campus?->name ?? 'Non précisé' }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Étage</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $reservation->floor?->name ?? $reservation->space?->floor?->name ?? 'Non précisé' }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Début</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $reservation->starts_at?->format('d/m/Y H:i') }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Fin</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $reservation->ends_at?->format('d/m/Y H:i') }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Type de durée</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $durationLabels[$reservation->duration_type] ?? ucfirst($reservation->duration_type) }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Durée d’engagement</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            @if($reservation->engagement_duration_value && $reservation->engagement_duration_unit)
                                {{ $reservation->engagement_duration_value }}
                                {{ $engagementUnitLabels[$reservation->engagement_duration_unit] ?? $reservation->engagement_duration_unit }}
                            @else
                                Non précisée
                            @endif
                        </dd>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Prix négocié</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">
                            {{ number_format($reservation->negotiated_price ?? 0, 2, ',', ' ') }} MAD
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Créée par</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $reservation->creator?->name ?? 'Non précisé' }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Créée le</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $reservation->created_at?->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                </dl>

                @if($reservation->notes)
                    <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Notes internes
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            {{ $reservation->notes }}
                        </p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">
                    Flux métier
                </h2>

                <div class="mt-5 grid gap-3 md:grid-cols-3">
                    <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                        <p class="text-xs font-semibold uppercase text-green-700">
                            Étape 1
                        </p>
                        <p class="mt-1 text-sm font-semibold text-green-900">
                            Réservation créée
                        </p>
                    </div>

                    <div class="rounded-xl border {{ $reservation->contract ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-4">
                        <p class="text-xs font-semibold uppercase {{ $reservation->contract ? 'text-green-700' : 'text-red-700' }}">
                            Étape 2
                        </p>
                        <p class="mt-1 text-sm font-semibold {{ $reservation->contract ? 'text-green-900' : 'text-red-900' }}">
                            Contrat {{ $reservation->contract ? 'créé' : 'manquant' }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase text-gray-500">
                            Étape 3
                        </p>
                        <p class="mt-1 text-sm font-semibold text-gray-800">
                            Échéances de paiement à suivre
                        </p>
                    </div>
                </div>

                <p class="mt-4 text-sm text-gray-500">
                    Ce flux montre qu’une réservation n’est pas isolée. Elle est liée à un contrat et sera ensuite liée aux échéances de paiement.
                </p>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Contrat lié
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Chaque réservation doit avoir un contrat.
                        </p>
                    </div>
                </div>

                @if($reservation->contract)
                    <div class="mt-5 space-y-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Titre</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $reservation->contract->title }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Période</p>
                            <p class="mt-1 text-sm text-gray-700">
                                {{ $reservation->contract->start_date?->format('d/m/Y') }}
                                →
                                {{ $reservation->contract->end_date?->format('d/m/Y') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Statut</p>
                            <span class="mt-1 inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $contractStatusClass }}">
                                {{ $contractStatusLabels[$contractStatus] ?? ucfirst($contractStatus) }}
                            </span>
                        </div>

                        @if($reservation->contract->pdf_path)
                            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                                Le PDF signé du contrat a été importé.
                            </div>

                            <a href="{{ asset('storage/' . $reservation->contract->pdf_path) }}"
                               target="_blank"
                               class="inline-flex w-full justify-center rounded-xl border border-[#284625] bg-white px-4 py-2 text-sm font-semibold text-[#284625] hover:bg-gray-50">
                                Voir le PDF
                            </a>
                        @else
                            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-700">
                                Le PDF signé du contrat n’a pas encore été importé.
                            </div>
                        @endif

                        @if(\Illuminate\Support\Facades\Route::has('commercial.contracts.show'))
                            <a href="{{ route('commercial.contracts.show', $reservation->contract) }}"
                               class="inline-flex w-full justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                                Gérer le contrat
                            </a>
                        @else
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                                Le module contrats côté commercial sera relié ici après sa création.
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        Aucun contrat lié. Cela ne devrait pas arriver, car chaque réservation doit avoir un contrat.
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">
                    Prochaine action
                </h2>

                @if($reservation->contract && !$reservation->contract->pdf_path)
                    <p class="mt-2 text-sm text-gray-600">
                        Complétez le contrat en important le PDF signé depuis la page du contrat.
                    </p>

                    @if(\Illuminate\Support\Facades\Route::has('commercial.contracts.show'))
                        <a href="{{ route('commercial.contracts.show', $reservation->contract) }}"
                           class="mt-4 inline-flex w-full justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                            Importer le PDF du contrat
                        </a>
                    @else
                        <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                            Le module contrats côté commercial sera relié ici après sa création.
                        </div>
                    @endif
                @elseif($reservation->contract && $reservation->contract->pdf_path)
                    <p class="mt-2 text-sm text-gray-600">
                        Le contrat est prêt. Le prochain module à finaliser est le suivi des échéances de paiement.
                    </p>
                @else
                    <p class="mt-2 text-sm text-gray-600">
                        Un contrat doit être créé pour cette réservation.
                    </p>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection