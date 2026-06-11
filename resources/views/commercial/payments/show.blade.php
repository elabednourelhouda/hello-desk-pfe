@extends('layouts.app')

@section('title', 'Détail paiement - Espace commercial')

@section('content')
@php
    $realStatus = $payment->real_status;

    $statusClasses = [
        'À payer' => 'bg-amber-50 text-amber-700 border-amber-200',
        'Payé' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'En retard' => 'bg-rose-50 text-rose-700 border-rose-200',
        'Annulé' => 'bg-slate-100 text-slate-700 border-slate-200',
    ];

    $statusClass = $statusClasses[$realStatus] ?? 'bg-slate-100 text-slate-700 border-slate-200';

    $amountHt = (float) ($payment->amount_ht ?? 0);
    $taxRate = (float) ($payment->tax_rate ?? 20);
    $taxAmount = (float) ($payment->tax_amount ?? 0);
    $amountTtc = (float) ($payment->amount_ttc ?? $payment->amount_due ?? 0);
    $amountPaid = (float) ($payment->amount_paid ?? 0);
    $remaining = max($amountTtc - $amountPaid, 0);
@endphp

<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ route('commercial.payments.index') }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour aux paiements
        </a>

        <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Détail de l’échéance
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Dossier complet du paiement : échéance, montants, mode de paiement, reçu et facture.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                @if(!in_array($payment->status, ['paid', 'cancelled']))
                    <form method="POST" action="{{ route('commercial.payments.markAsPaid', $payment) }}">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                                class="rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:opacity-90"
                                onclick="return confirm('Confirmer que cette échéance est payée ?')">
                            Marquer comme payé
                        </button>
                    </form>
                @endif

                <a href="{{ route('commercial.payments.edit', $payment) }}"
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

    <section class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Statut</p>

            <span class="mt-3 inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $statusClass }}">
                {{ $realStatus }}
            </span>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Montant TTC</p>

            <p class="mt-3 text-xl font-bold text-[#284625]">
                {{ number_format($amountTtc, 2, ',', ' ') }} DH
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Montant payé</p>

            <p class="mt-3 text-xl font-bold text-emerald-700">
                {{ number_format($amountPaid, 2, ',', ' ') }} DH
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Reste à payer</p>

            <p class="mt-3 text-xl font-bold {{ $remaining > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                {{ number_format($remaining, 2, ',', ' ') }} DH
            </p>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="space-y-4 lg:col-span-2">

            <details open class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Informations générales
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Client, échéance, durée et enregistrement.
                        </p>
                    </div>

                    <span class="toggle-label text-xs font-semibold text-[#284625]"></span>
                </summary>

                <div class="border-t border-gray-100 p-6">
                    <dl class="grid gap-4 md:grid-cols-2">
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
                            <dt class="text-xs font-semibold uppercase text-gray-400">Durée</dt>
                            <dd class="mt-1 text-sm text-gray-700">
                                {{ $payment->duration_label ?? 'Non précisée' }}
                            </dd>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase text-gray-400">Payé le</dt>
                            <dd class="mt-1 text-sm text-gray-700">
                                {{ $payment->paid_at?->format('d/m/Y H:i') ?? 'Non payé' }}
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
                </div>
            </details>

            <details open class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Montants HT, TVA et TTC
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Détail comptable de l’échéance.
                        </p>
                    </div>

                    <span class="toggle-label text-xs font-semibold text-[#284625]"></span>
                </summary>

                <div class="border-t border-gray-100 p-6">
                    <dl class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase text-gray-400">Montant HT</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">
                                {{ number_format($amountHt, 2, ',', ' ') }} DH
                            </dd>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase text-gray-400">TVA</dt>
                            <dd class="mt-1 text-sm text-gray-700">
                                {{ number_format($taxRate, 2, ',', ' ') }} %
                                —
                                {{ number_format($taxAmount, 2, ',', ' ') }} DH
                            </dd>
                        </div>

                        <div class="rounded-xl bg-[#284625]/5 p-4">
                            <dt class="text-xs font-semibold uppercase text-[#284625]">Montant TTC</dt>
                            <dd class="mt-1 text-lg font-bold text-[#284625]">
                                {{ number_format($amountTtc, 2, ',', ' ') }} DH
                            </dd>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase text-gray-400">Montant payé</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">
                                {{ number_format($amountPaid, 2, ',', ' ') }} DH
                            </dd>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase text-gray-400">Reste à payer</dt>
                            <dd class="mt-1 text-sm font-semibold {{ $remaining > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                {{ number_format($remaining, 2, ',', ' ') }} DH
                            </dd>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase text-gray-400">Ancien champ montant dû</dt>
                            <dd class="mt-1 text-sm text-gray-700">
                                {{ number_format($payment->amount_due, 2, ',', ' ') }} DH
                            </dd>
                        </div>
                    </dl>
                </div>
            </details>

            <details open class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Mode de paiement
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Espèces, chèque, virement bancaire, TPE ou autre.
                        </p>
                    </div>

                    <span class="toggle-label text-xs font-semibold text-[#284625]"></span>
                </summary>

                <div class="border-t border-gray-100 p-6">
                    <dl class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase text-gray-400">Mode</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $payment->payment_method_label }}
                            </dd>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase text-gray-400">Référence générale</dt>
                            <dd class="mt-1 text-sm text-gray-700">
                                {{ $payment->reference ?? 'Non précisée' }}
                            </dd>
                        </div>

                        @if($payment->payment_method === 'cheque')
                            <div class="rounded-xl bg-gray-50 p-4">
                                <dt class="text-xs font-semibold uppercase text-gray-400">N° chèque</dt>
                                <dd class="mt-1 text-sm text-gray-700">
                                    {{ $payment->cheque_number ?? 'Non précisé' }}
                                </dd>
                            </div>

                            <div class="rounded-xl bg-gray-50 p-4">
                                <dt class="text-xs font-semibold uppercase text-gray-400">Banque du chèque</dt>
                                <dd class="mt-1 text-sm text-gray-700">
                                    {{ $payment->cheque_bank ?? 'Non précisée' }}
                                </dd>
                            </div>

                            <div class="rounded-xl bg-gray-50 p-4">
                                <dt class="text-xs font-semibold uppercase text-gray-400">Date du chèque</dt>
                                <dd class="mt-1 text-sm text-gray-700">
                                    {{ $payment->cheque_date?->format('d/m/Y') ?? 'Non précisée' }}
                                </dd>
                            </div>
                        @endif

                        @if($payment->payment_method === 'bank_transfer')
                            <div class="rounded-xl bg-gray-50 p-4">
                                <dt class="text-xs font-semibold uppercase text-gray-400">Référence virement</dt>
                                <dd class="mt-1 text-sm text-gray-700">
                                    {{ $payment->bank_transfer_reference ?? 'Non précisée' }}
                                </dd>
                            </div>

                            <div class="rounded-xl bg-gray-50 p-4">
                                <dt class="text-xs font-semibold uppercase text-gray-400">Banque</dt>
                                <dd class="mt-1 text-sm text-gray-700">
                                    {{ $payment->bank_name ?? 'Non précisée' }}
                                </dd>
                            </div>
                        @endif

                        @if($payment->payment_method === 'tpe')
                            <div class="rounded-xl bg-gray-50 p-4">
                                <dt class="text-xs font-semibold uppercase text-gray-400">Référence transaction TPE</dt>
                                <dd class="mt-1 text-sm text-gray-700">
                                    {{ $payment->tpe_transaction_reference ?? 'Non précisée' }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>

            <details open class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Reçu et facture
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Documents justificatifs liés au paiement.
                        </p>
                    </div>

                    <span class="toggle-label text-xs font-semibold text-[#284625]"></span>
                </summary>

                <div class="border-t border-gray-100 p-6">
                    <dl class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase text-gray-400">N° reçu</dt>
                            <dd class="mt-1 text-sm text-gray-700">
                                {{ $payment->receipt_number ?? 'Non précisé' }}
                            </dd>

                            @if($payment->receipt_file)
                                <a href="{{ asset('storage/' . $payment->receipt_file) }}"
                                   target="_blank"
                                   class="mt-3 inline-flex rounded-xl border border-[#284625] px-4 py-2 text-xs font-semibold text-[#284625] hover:bg-[#284625]/5">
                                    Voir le reçu
                                </a>
                            @endif
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase text-gray-400">N° facture</dt>
                            <dd class="mt-1 text-sm text-gray-700">
                                {{ $payment->invoice_number ?? 'Non précisé' }}
                            </dd>

                            @if($payment->invoice_file)
                                <a href="{{ asset('storage/' . $payment->invoice_file) }}"
                                   target="_blank"
                                   class="mt-3 inline-flex rounded-xl border border-[#284625] px-4 py-2 text-xs font-semibold text-[#284625] hover:bg-[#284625]/5">
                                    Voir la facture
                                </a>
                            @endif
                        </div>
                    </dl>
                </div>
            </details>

            @if($payment->notes)
                <details class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">
                                Notes internes
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Notes visibles uniquement côté commercial.
                            </p>
                        </div>

                        <span class="toggle-label text-xs font-semibold text-[#284625]"></span>
                    </summary>

                    <div class="border-t border-gray-100 p-6">
                        <p class="text-sm leading-6 text-gray-600">
                            {{ $payment->notes }}
                        </p>
                    </div>
                </details>
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
                        <a href="{{ route('commercial.contracts.show', $payment->contract) }}"
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
                        <a href="{{ route('commercial.reservations.show', $payment->reservation) }}"
                           class="inline-flex w-full justify-center rounded-xl border border-[#284625] bg-white px-4 py-2 text-sm font-semibold text-[#284625] hover:bg-gray-50">
                            Voir la réservation
                        </a>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
    document.querySelectorAll('details').forEach((detail) => {
        const label = detail.querySelector('.toggle-label');

        if (!label) {
            return;
        }

        function updateLabel() {
            label.textContent = detail.open ? 'Masquer' : 'Afficher';
        }

        updateLabel();

        detail.addEventListener('toggle', updateLabel);
    });
</script>
@endsection