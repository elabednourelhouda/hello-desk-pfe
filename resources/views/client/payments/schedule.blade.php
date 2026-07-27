@extends('layouts.app')

@section('title', 'Historique paiements - Hello Desk')

@section('content')
@php
    $statusLabels = [
        'À payer' => 'À payer',
        'Payé' => 'Payé',
        'En retard' => 'En retard',
        'Annulé' => 'Annulé',
    ];

    $statusClasses = [
        'À payer' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'Payé' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'En retard' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'Annulé' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
    ];
@endphp

<div class="mx-auto max-w-4xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ route('client.payments.index') }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour à mes paiements
        </a>

        <div class="mt-4">
            <h1 class="text-2xl font-bold text-gray-900">
                Historique — {{ $contract->title ?? 'Contrat #' . $contract->id }}
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                {{ $contract->reservation?->space?->name ?? 'Espace non précisé' }}
                ·
                {{ $summary['paid_installments_count'] }} / {{ $summary['installments_count'] }} échéances réglées
            </p>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-5">
            <h2 class="text-lg font-bold text-gray-900">
                Détail mois par mois
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Statut de chaque échéance. Pour le détail comptable ou une facture, ouvrez le reçu.
            </p>
        </div>

        <ul class="divide-y divide-gray-100">
            @forelse($contract->payments as $payment)
                @php
                    $realStatus = $payment->real_status;
                    $statusClass = $statusClasses[$realStatus] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';
                    $amountTtc = (float) ($payment->amount_ttc ?? $payment->amount_due ?? 0);
                @endphp

                <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-gray-900">
                            {{ $payment->duration_label ?? ('Échéance du ' . ($payment->due_date?->format('d/m/Y') ?? '-')) }}
                        </p>

                        <p class="mt-0.5 text-xs text-gray-500">
                            Échéance : {{ $payment->due_date?->format('d/m/Y') ?? '-' }}
                            <span class="text-gray-300">•</span>
                            {{ number_format($amountTtc, 2, ',', ' ') }} DH
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">
                            {{ $statusLabels[$realStatus] ?? $realStatus }}
                        </span>

                        @if($payment->status === 'paid')
                            <a href="{{ route('client.payments.receipt', $payment) }}"
                               target="_blank"
                               class="inline-flex h-9 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-xs font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                                Reçu
                            </a>
                        @endif
                    </div>
                </li>
            @empty
                <li class="px-6 py-14 text-center text-sm text-gray-500">
                    Aucune échéance enregistrée pour ce contrat.
                </li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
