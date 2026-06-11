@extends('layouts.app')

@section('title', 'Mes paiements - Hello Desk')

@section('content')
@php
$pagePayments = method_exists($payments, 'getCollection')
? $payments->getCollection()
: collect($payments ?? []);

$totalTtc = $pagePayments->sum(fn ($payment) => (float) ($payment->amount_ttc ?? $payment->amount_due ?? 0));
$totalPaid = $pagePayments->sum(fn ($payment) => (float) ($payment->amount_paid ?? 0));
$totalRemaining = max($totalTtc - $totalPaid, 0);

$statusLabels = [
'due' => 'À payer',
'paid' => 'Payé',
'late' => 'En retard',
'cancelled' => 'Annulé',
];

$statusClasses = [
'due' => 'bg-amber-50 text-amber-700 ring-amber-200',
'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
'late' => 'bg-rose-50 text-rose-700 ring-rose-200',
'cancelled' => 'bg-slate-100 text-slate-700 ring-slate-200',
];

$formatMoney = function ($value) {
return number_format((float) $value, 2, ',', ' ') . ' DH';
};
@endphp

<div class="mx-auto max-w-7xl px-6 py-8">

    {{-- Header --}}
    <div class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-[1.4fr_0.7fr]">
            <div class="bg-gradient-to-br from-[#284625] via-[#2f6130] to-[#3f7a3b] p-8 text-white">
                <p class="text-sm font-bold uppercase tracking-wide text-white/70">
                    Espace client
                </p>

                <h1 class="mt-3 text-3xl font-black">
                    Mes paiements
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                    Consultez vos échéances, les montants réglés, le reste à payer et les documents associés.
                </p>
            </div>

            <div class="bg-white p-8">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                    Client connecté
                </p>

                <p class="mt-2 text-lg font-black text-slate-900">
                    {{ auth()->user()->name }}
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    {{ auth()->user()->email }}
                </p>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-slate-500">Total TTC</p>
            <p class="mt-3 text-3xl font-black text-slate-900">
                {{ $formatMoney($totalTtc) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-slate-500">Montant payé</p>
            <p class="mt-3 text-3xl font-black text-emerald-600">
                {{ $formatMoney($totalPaid) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-slate-500">Reste à payer</p>
            <p class="mt-3 text-3xl font-black {{ $totalRemaining > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                {{ $formatMoney($totalRemaining) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-slate-500">Échéances affichées</p>
            <p class="mt-3 text-3xl font-black text-blue-600">
                {{ $pagePayments->count() }}
            </p>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-black text-slate-900">
                Liste des échéances
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Paiements associés à vos contrats Hello Desk.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Contrat</th>
                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Échéance</th>
                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Montants</th>
                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Statut</th>
                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($payments as $payment)
                    @php
                    $status = $payment->status;

                    if (
                    $status === 'due'
                    && $payment->due_date
                    && $payment->due_date->lt(today())
                    ) {
                    $status = 'late';
                    }

                    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                    $statusClass = $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';

                    $amountHt = (float) ($payment->amount_ht ?? 0);
                    $taxAmount = (float) ($payment->tax_amount ?? 0);
                    $amountTtc = (float) ($payment->amount_ttc ?? $payment->amount_due ?? 0);
                    $amountPaid = (float) ($payment->amount_paid ?? 0);
                    $remaining = max($amountTtc - $amountPaid, 0);
                    @endphp

                    <tr class="transition hover:bg-slate-50">
                        <td class="px-5 py-4">
                            <p class="font-black text-slate-900">
                                {{ $payment->contract?->title ?? 'Contrat supprimé' }}
                            </p>

                            <p class="mt-1 text-xs font-medium text-slate-500">
                                {{ $payment->contract?->reservation?->space?->name ?? 'Espace non précisé' }}
                            </p>

                            @if($payment->invoice_number)
                            <p class="mt-1 text-xs text-slate-400">
                                Facture : {{ $payment->invoice_number }}
                            </p>
                            @endif
                        </td>

                        <td class="px-5 py-4">
                            <p class="text-sm font-bold text-slate-800">
                                {{ $payment->due_date?->format('d/m/Y') ?? 'Non précisée' }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ $payment->duration_label ?? 'Durée non précisée' }}
                            </p>
                        </td>

                        <td class="px-5 py-4 text-sm">
                            <p class="font-bold text-slate-900">
                                TTC : {{ $formatMoney($amountTtc) }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                HT : {{ $formatMoney($amountHt) }}
                                <span class="text-slate-300">•</span>
                                TVA : {{ $formatMoney($taxAmount) }}
                            </p>

                            <p class="mt-1 text-xs font-bold {{ $remaining > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                Reste : {{ $formatMoney($remaining) }}
                            </p>
                        </td>

                        <td class="px-5 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>

                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('client.payments.show', $payment) }}"
                                class="inline-flex items-center justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#20391f]">
                                Voir
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center">
                            <p class="font-bold text-slate-800">
                                Aucun paiement trouvé
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                Vos échéances apparaîtront ici dès qu’elles seront créées.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($payments, 'hasPages') && $payments->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection