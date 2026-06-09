@extends('layouts.app')

@section('title', 'Détail paiement - Hello Desk')

@section('content')
@php
    $realStatus = $payment->real_status;

    $statusClasses = [
        'À payer' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'Payé' => 'bg-green-50 text-green-700 border-green-200',
        'En retard' => 'bg-red-50 text-red-700 border-red-200',
        'Annulé' => 'bg-gray-100 text-gray-700 border-gray-200',
    ];

    $statusClass = $statusClasses[$realStatus] ?? 'bg-gray-100 text-gray-700 border-gray-200';
@endphp

<div class="mx-auto max-w-5xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.payments.index') }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour aux paiements
        </a>

        <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Détail de l’échéance
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Suivi d’une échéance liée à un contrat Hello Desk.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                @if(!in_array($payment->status, ['paid', 'cancelled']))
                    <form method="POST" action="{{ route('admin.payments.markAsPaid', $payment) }}">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                                class="rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:opacity-90"
                                onclick="return confirm('Confirmer que cette échéance est payée ?')">
                            Marquer comme payé
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.payments.edit', $payment) }}"
                   class="rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Modifier le paiement
                </a>
            </div>
        </div>
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
        <section class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        Informations paiement
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Montants, statut, échéance et informations de règlement.
                    </p>
                </div>

                <span class="inline-flex w-fit rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                    {{ $realStatus }}
                </span>
            </div>

            <dl class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold uppercase text-gray-400">Client</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $payment->client?->full_name ?? 'Client supprimé' }}
                    </dd>

                    @if($payment->client?->email)
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $payment->client->email }}
                        </p>
                    @endif
                </div>

                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold uppercase text-gray-400">Date d’échéance</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $payment->due_date?->format('d/m/Y') ?? 'Non précisée' }}
                    </dd>
                </div>

                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold uppercase text-gray-400">Montant à payer</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">
                        {{ number_format($payment->amount_due, 2, ',', ' ') }} DH
                    </dd>
                </div>

                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold uppercase text-gray-400">Montant payé</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">
                        {{ number_format($payment->amount_paid, 2, ',', ' ') }} DH
                    </dd>
                </div>

                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold uppercase text-gray-400">Montant restant</dt>
                    <dd class="mt-1 text-sm font-semibold {{ $payment->remaining_amount > 0 ? 'text-red-700' : 'text-green-700' }}">
                        {{ number_format($payment->remaining_amount, 2, ',', ' ') }} DH
                    </dd>
                </div>

                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold uppercase text-gray-400">Payé le</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $payment->paid_at?->format('d/m/Y H:i') ?? 'Non payé' }}
                    </dd>
                </div>

                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold uppercase text-gray-400">Méthode</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $payment->payment_method ?? 'Non précisée' }}
                    </dd>
                </div>

                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold uppercase text-gray-400">Référence</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $payment->reference ?? 'Non précisée' }}
                    </dd>
                </div>

                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold uppercase text-gray-400">Enregistré par</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $payment->recorder?->name ?? 'Non précisé' }}
                    </dd>
                </div>

                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold uppercase text-gray-400">Créé le</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $payment->created_at?->format('d/m/Y H:i') }}
                    </dd>
                </div>
            </dl>

            @if($payment->notes)
                <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Notes internes</h3>

                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        {{ $payment->notes }}
                    </p>
                </div>
            @endif
        </section>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">
                    Contrat lié
                </h2>

                <div class="mt-5 space-y-4 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Contrat</p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $payment->contract?->title ?? 'Contrat supprimé' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Espace</p>
                        <p class="mt-1 text-gray-700">
                            {{ $payment->contract?->reservation?->space?->name ?? 'Non précisé' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Période du contrat</p>
                        <p class="mt-1 text-gray-700">
                            {{ $payment->contract?->start_date?->format('d/m/Y') ?? 'Non précisée' }}
                            →
                            {{ $payment->contract?->end_date?->format('d/m/Y') ?? 'Non précisée' }}
                        </p>
                    </div>

                    @if($payment->contract)
                        <a href="{{ route('admin.contracts.show', $payment->contract) }}"
                           class="inline-flex w-full justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Voir le contrat
                        </a>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">
                    Réservation liée
                </h2>

                <div class="mt-5 space-y-4 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Réservation</p>
                        <p class="mt-1 text-gray-700">
                            {{ $payment->reservation?->starts_at?->format('d/m/Y H:i') ?? 'Non précisée' }}
                            →
                            {{ $payment->reservation?->ends_at?->format('d/m/Y H:i') ?? 'Non précisée' }}
                        </p>
                    </div>

                    @if($payment->reservation)
                        <a href="{{ route('admin.reservations.show', $payment->reservation) }}"
                           class="inline-flex w-full justify-center rounded-xl border border-[#284625] bg-white px-4 py-2 text-sm font-semibold text-[#284625] hover:bg-gray-50">
                            Voir la réservation
                        </a>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection