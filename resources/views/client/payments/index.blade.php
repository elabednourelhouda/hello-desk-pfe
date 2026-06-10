@extends('layouts.app')

@section('title', 'Mes paiements - Hello Desk')

@section('content')
@php
    $pagePayments = method_exists($payments, 'getCollection')
        ? $payments->getCollection()
        : collect();

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
        'due' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'late' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'cancelled' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
    ];
@endphp

<div class="min-h-screen bg-[#f6f8f6]">
    <div class="mx-auto max-w-7xl px-6 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                Mes paiements
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Consultez vos échéances, montants TTC, paiements reçus et documents liés.
            </p>
        </div>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-gray-500">Total TTC</p>
                <p class="mt-3 text-2xl font-bold text-gray-900">
                    {{ number_format($totalTtc, 2, ',', ' ') }} DH
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-gray-500">Montant payé</p>
                <p class="mt-3 text-2xl font-bold text-emerald-700">
                    {{ number_format($totalPaid, 2, ',', ' ') }} DH
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-gray-500">Reste à payer</p>
                <p class="mt-3 text-2xl font-bold {{ $totalRemaining > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                    {{ number_format($totalRemaining, 2, ',', ' ') }} DH
                </p>
            </div>
        </section>

        <section class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5">
                <h2 class="text-lg font-bold text-gray-900">
                    Liste des échéances
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Vous pouvez consulter les détails de chaque paiement.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Contrat</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Échéance</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Montants</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Statut</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
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
                                $statusClass = $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';

                                $amountHt = (float) ($payment->amount_ht ?? 0);
                                $taxAmount = (float) ($payment->tax_amount ?? 0);
                                $amountTtc = (float) ($payment->amount_ttc ?? $payment->amount_due ?? 0);
                                $amountPaid = (float) ($payment->amount_paid ?? 0);
                                $remaining = max($amountTtc - $amountPaid, 0);
                            @endphp

                            <tr class="transition hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900">
                                        {{ $payment->contract?->title ?? 'Contrat supprimé' }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $payment->contract?->reservation?->space?->name ?? 'Espace non précisé' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $payment->due_date?->format('d/m/Y') ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-sm">
                                    <p class="font-semibold text-gray-900">
                                        TTC : {{ number_format($amountTtc, 2, ',', ' ') }} DH
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        HT : {{ number_format($amountHt, 2, ',', ' ') }} DH —
                                        TVA : {{ number_format($taxAmount, 2, ',', ' ') }} DH
                                    </p>

                                    <p class="mt-1 text-xs font-semibold {{ $remaining > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                        Reste : {{ number_format($remaining, 2, ',', ' ') }} DH
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('client.payments.show', $payment) }}"
                                       class="inline-flex h-9 items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white shadow-sm hover:opacity-90">
                                        Voir détail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center">
                                    <p class="font-bold text-gray-800">
                                        Aucun paiement trouvé
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        Vos échéances apparaîtront ici dès qu’elles seront créées.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($payments, 'hasPages') && $payments->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $payments->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection