@extends('layouts.app')

@section('title', 'Détail paiement - Hello Desk')

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

<div class="mx-auto max-w-7xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ route('client.payments.index') }}"
            class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour à mes paiements
        </a>

        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Détail du paiement
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Consultez les informations de votre échéance et les documents associés.
                </p>
            </div>

            <div class="flex flex-col items-start gap-3 sm:items-end">
                <span class="inline-flex w-fit rounded-full border px-3 py-1 text-xs font-bold {{ $statusClass }}">
                    {{ $realStatus }}
                </span>

                @if($payment->status === 'paid')
                    <a href="{{ route('client.payments.receipt', $payment) }}"
                       target="_blank"
                       class="inline-flex items-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-100">
                        Voir le reçu
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">
                Informations de paiement
            </h2>

            <dl class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold uppercase text-gray-400">Date d’échéance</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">
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
                    <dt class="text-xs font-semibold uppercase text-gray-400">Payé le</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $payment->paid_at?->format('d/m/Y H:i') ?? 'Non payé' }}
                    </dd>
                </div>
            </dl>

            <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-4">
                <h3 class="text-sm font-bold text-gray-900">
                    Mode de paiement
                </h3>

                <div class="mt-4 grid gap-4 md:grid-cols-2 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Mode</p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $payment->payment_method_label }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Référence générale</p>
                        <p class="mt-1 text-gray-700">
                            {{ $payment->reference ?? 'Non précisée' }}
                        </p>
                    </div>

                    @if($payment->payment_method === 'cheque')
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">N° chèque</p>
                        <p class="mt-1 text-gray-700">{{ $payment->cheque_number ?? 'Non précisé' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Banque</p>
                        <p class="mt-1 text-gray-700">{{ $payment->cheque_bank ?? 'Non précisée' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Date du chèque</p>
                        <p class="mt-1 text-gray-700">{{ $payment->cheque_date?->format('d/m/Y') ?? 'Non précisée' }}</p>
                    </div>
                    @endif

                    @if($payment->payment_method === 'bank_transfer')
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Référence virement</p>
                        <p class="mt-1 text-gray-700">{{ $payment->bank_transfer_reference ?? 'Non précisée' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Banque</p>
                        <p class="mt-1 text-gray-700">{{ $payment->bank_name ?? 'Non précisée' }}</p>
                    </div>
                    @endif

                    @if($payment->payment_method === 'tpe')
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Référence transaction TPE</p>
                        <p class="mt-1 text-gray-700">{{ $payment->tpe_transaction_reference ?? 'Non précisée' }}</p>
                    </div>
                    @endif
                </div>
            </div>
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

                    @if($payment->contract)
                    <a href="{{ route('client.contracts.show', $payment->contract) }}"
                        class="inline-flex w-full justify-center rounded-xl border border-[#284625] bg-white px-4 py-2 text-sm font-semibold text-[#284625] hover:bg-gray-50">
                        Voir le contrat
                    </a>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">
                    Documents
                </h2>

                <div class="mt-5 space-y-4 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">N° reçu</p>
                        <p class="mt-1 text-gray-700">
                            {{ $payment->receipt_number ?? 'Non précisé' }}
                        </p>

                        @if($payment->receipt_file)
                        <a href="{{ asset('storage/' . $payment->receipt_file) }}"
                            target="_blank"
                            class="mt-2 inline-flex font-semibold text-[#284625] hover:underline">
                            Voir le reçu
                        </a>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">N° facture</p>
                        <p class="mt-1 text-gray-700">
                            {{ $payment->invoice_number ?? 'Non précisé' }}
                        </p>

                        @if($payment->invoice_file)
                        <a href="{{ asset('storage/' . $payment->invoice_file) }}"
                            target="_blank"
                            class="mt-2 inline-flex font-semibold text-[#284625] hover:underline">
                            Voir la facture
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection