@extends('layouts.app')

@section('title', 'Détail réservation - Administration')

@section('content')
<div class="mx-auto max-w-5xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.reservations.index') }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour aux réservations
        </a>

        <h1 class="mt-4 text-2xl font-bold text-gray-900">
            Détail de la réservation
        </h1>

        @if(session('success'))
            <div class="mt-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Informations réservation</h2>

            <dl class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Client</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">
                        {{ $reservation->client?->full_name ?? 'Client supprimé' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Espace</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">
                        {{ $reservation->space?->name ?? 'Espace supprimé' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Début</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $reservation->starts_at?->format('d/m/Y H:i') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Fin</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $reservation->ends_at?->format('d/m/Y H:i') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Type de durée</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $reservation->duration_type }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Prix négocié</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ number_format($reservation->negotiated_price, 2, ',', ' ') }} DH
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Statut</dt>
                    <dd class="mt-1">
                        <span class="rounded-full bg-yellow-50 px-3 py-1 text-xs font-semibold text-yellow-700">
                            {{ $reservation->status }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Créée par</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $reservation->creator?->name ?? 'Non précisé' }}
                    </dd>
                </div>
            </dl>

            @if($reservation->notes)
                <div class="mt-6 rounded-xl bg-gray-50 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Notes internes</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        {{ $reservation->notes }}
                    </p>
                </div>
            @endif
        </div>

        <aside class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Contrat lié</h2>

            @if($reservation->contract)
                <div class="mt-5 space-y-4">
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Titre</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">
                            {{ $reservation->contract->title }}
                        </p>
                    </div>

                    <a href="{{ route('admin.contracts.show', $reservation->contract) }}"
                    class="inline-flex w-full justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                        Gérer le contrat
                    </a>

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
                        <span class="mt-1 inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                            {{ $reservation->contract->status }}
                        </span>
                    </div>

                    @if($reservation->contract->pdf_path)
                        <a href="{{ asset('storage/' . $reservation->contract->pdf_path) }}"
                           target="_blank"
                           class="inline-flex rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                            Voir le PDF
                        </a>
                    @else
                        <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-700">
                            Le PDF du contrat n’est pas encore importé.
                        </div>
                    @endif
                </div>
            @else
                <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    Aucun contrat lié. Normalement, chaque réservation doit avoir un contrat.
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection