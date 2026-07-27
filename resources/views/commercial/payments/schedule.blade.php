@extends('layouts.app')

@section('title', "Échéancier - Espace commercial")

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

    $overallStatusLabels = [
        'due' => 'En cours',
        'paid' => 'Soldé',
        'late' => 'En retard',
        'none' => 'Sans échéance',
    ];

    $overallStatusClasses = [
        'due' => 'bg-amber-50 text-amber-700 border-amber-200',
        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'late' => 'bg-rose-50 text-rose-700 border-rose-200',
        'none' => 'bg-slate-100 text-slate-700 border-slate-200',
    ];
@endphp

<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ route('commercial.payments.index') }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour aux contrats
        </a>

        <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Échéancier — {{ $contract->title ?? 'Contrat #' . $contract->id }}
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $contract->client?->full_name ?? 'Client supprimé' }}
                    ·
                    {{ $contract->reservation?->space?->name ?? 'Espace non précisé' }}
                </p>
            </div>

            <span class="inline-flex w-fit rounded-full border px-3 py-1 text-xs font-bold {{ $overallStatusClasses[$summary['overall_status']] ?? $overallStatusClasses['none'] }}">
                {{ $overallStatusLabels[$summary['overall_status']] ?? 'Sans échéance' }}
            </span>
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

    {{-- Contract-level summary --}}
    <section class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Échéances réglées</p>
            <p class="mt-3 text-xl font-bold text-gray-900">
                {{ $summary['paid_installments_count'] }} / {{ $summary['installments_count'] }}
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Montant TTC total</p>
            <p class="mt-3 text-xl font-bold text-[#284625]">
                {{ number_format($summary['total_ttc'], 2, ',', ' ') }} DH
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Montant payé</p>
            <p class="mt-3 text-xl font-bold text-emerald-700">
                {{ number_format($summary['total_paid'], 2, ',', ' ') }} DH
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Reste à payer</p>
            <p class="mt-3 text-xl font-bold {{ $summary['remaining'] > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                {{ number_format($summary['remaining'], 2, ',', ' ') }} DH
            </p>
        </div>
    </section>

    {{-- Monthly list --}}
    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-5">
            <h2 class="text-lg font-bold text-gray-900">
                Détail mois par mois
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $contract->payments->count() }} échéance(s) pour ce contrat.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Durée</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Échéance</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Montant TTC</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Payé</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($contract->payments as $payment)
                        @php
                            $realStatus = $payment->real_status;
                            $statusClass = $statusClasses[$realStatus] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';

                            $amountTtc = (float) ($payment->amount_ttc ?? $payment->amount_due ?? 0);
                            $amountPaid = (float) ($payment->amount_paid ?? 0);
                        @endphp

                        <tr class="transition hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                                {{ $payment->duration_label ?? 'Non précisée' }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $payment->due_date?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                {{ number_format($amountTtc, 2, ',', ' ') }} DH
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ number_format($amountPaid, 2, ',', ' ') }} DH
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">
                                    {{ $statusLabels[$realStatus] ?? $realStatus }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('commercial.payments.show', $payment) }}"
                                       class="inline-flex h-9 items-center justify-center rounded-xl bg-sky-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-sky-700">
                                        Voir dossier
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center text-sm text-gray-500">
                                Aucune échéance enregistrée pour ce contrat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
        <a href="{{ route('commercial.payments.create', ['contract_id' => $contract->id]) }}"
           class="inline-flex h-11 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
            Ajouter une échéance à ce contrat
        </a>

        <a href="{{ route('commercial.contracts.show', $contract) }}"
           class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-300 bg-white px-5 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">
            Voir le contrat
        </a>
    </div>
</div>
@endsection
