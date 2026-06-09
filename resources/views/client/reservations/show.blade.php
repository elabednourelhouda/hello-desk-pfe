@extends('layouts.app')

@section('title', 'Détail réservation - Hello Desk')

@section('content')
@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    $statusLabels = [
        'pending' => 'En attente',
        'confirmed' => 'Confirmée',
        'active' => 'En cours',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
        'expired' => 'Expirée',
    ];

    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'active' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'completed' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
        'expired' => 'bg-red-50 text-red-700 ring-red-200',
    ];

    $status = $reservation->status ?? 'pending';
    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
    $statusClass = $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';

    $formatDate = function ($value) {
        if (!$value) {
            return '—';
        }

        return Carbon::parse($value)->format('d/m/Y H:i');
    };

    $formatMoney = function ($value) {
        if ($value === null || $value === '') {
            return '—';
        }

        return number_format((float) $value, 2, ',', ' ') . ' MAD';
    };

    $space = $reservation->space ?? null;
    $contract = $reservation->contract ?? null;
@endphp

<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500">Espace client</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">
                Détail de la réservation
            </h1>
        </div>

        <a href="{{ route('client.reservations.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            Retour aux réservations
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Réservation</p>
                        <h2 class="mt-1 text-xl font-bold text-slate-900">
                            {{ $space->name ?? 'Espace non renseigné' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Référence #{{ $reservation->id }}
                        </p>
                    </div>

                    <span class="inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Date de début
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $formatDate($reservation->start_date ?? $reservation->starts_at ?? null) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Date de fin
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $formatDate($reservation->end_date ?? $reservation->ends_at ?? null) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Type de durée
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ ucfirst($reservation->duration_type ?? '—') }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Prix négocié
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $formatMoney($reservation->negotiated_price ?? $reservation->price ?? null) }}
                        </p>
                    </div>
                </div>

                @if(!empty($reservation->notes))
                    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4">
                        <p class="text-sm font-semibold text-slate-900">Notes</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $reservation->notes }}
                        </p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900">
                    Informations de l’espace
                </h3>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Nom</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $space->name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-500">Type</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $space->spaceType->name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-500">Campus</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $reservation->campus->name ?? $space->campus->name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-500">Étage</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $reservation->floor->name ?? $space->floor->name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-500">Capacité</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $space->capacity ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-500">Statut espace</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ ucfirst($space->status ?? '—') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900">
                    Contrat lié
                </h3>

                @if($contract)
                    <div class="mt-4 rounded-xl bg-slate-50 p-4">
                        <p class="text-sm font-semibold text-slate-900">
                            Contrat #{{ $contract->id }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Statut :
                            <span class="font-semibold text-slate-700">
                                {{ ucfirst($contract->status ?? '—') }}
                            </span>
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Du {{ $formatDate($contract->start_date ?? null) }}
                            au {{ $formatDate($contract->end_date ?? null) }}
                        </p>
                    </div>

                    <div class="mt-4 space-y-3">
                        @if(Route::has('client.contracts.show'))
                            <a href="{{ route('client.contracts.show', $contract) }}"
                               class="flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Voir le contrat
                            </a>
                        @endif

                        @if(($contract->status ?? null) === 'active' && Route::has('client.contracts.document'))
                            <a href="{{ route('client.contracts.document', $contract) }}"
                               class="flex w-full items-center justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1f351d]">
                                Voir le document
                            </a>
                        @endif
                    </div>
                @else
                    <div class="mt-4 rounded-xl bg-amber-50 p-4 text-sm text-amber-700">
                        Aucun contrat n’est encore lié à cette réservation.
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900">
                    Besoin d’aide ?
                </h3>

                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Pour toute question concernant cette réservation, vous pouvez contacter l’équipe Hello Desk ou déposer une réclamation depuis votre espace client.
                </p>

                @if(Route::has('client.complaints.create'))
                    <a href="{{ route('client.complaints.create') }}"
                       class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Déposer une réclamation
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection