@extends('layouts.app')

@section('title', 'Mes paiements - Hello Desk')

@section('content')
@php
$formatMoney = function ($value) {
    return number_format((float) $value, 2, ',', ' ') . ' DH';
};
@endphp

<div class="mx-auto max-w-5xl px-6 py-8">

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
                    Un aperçu simple par contrat : ce qui est déjà réglé, et ce qu'il reste à payer.
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

    {{-- One status card per contract --}}
    <div class="space-y-5">
        @forelse($contracts as $row)
            @php
                $contract = $row['contract'];
                $current = $row['current_payment'];

                $isLate = $current
                    ? ($current->status === 'late' || ($current->status === 'due' && $current->due_date && $current->due_date->lt(today())))
                    : false;

                if (!$current) {
                    // Nothing left unpaid: fully up to date.
                    $alertLabel = 'Payé';
                    $alertClass = 'bg-emerald-50 border-emerald-200 text-emerald-700';
                    $alertDot = 'bg-emerald-500';
                    $alertMessage = 'Toutes les échéances de ce contrat sont réglées.';
                } elseif ($isLate) {
                    $alertLabel = 'En retard';
                    $alertClass = 'bg-rose-50 border-rose-200 text-rose-700';
                    $alertDot = 'bg-rose-500';
                    $amountTtc = (float) ($current->amount_ttc ?? $current->amount_due ?? 0);
                    $alertMessage = $formatMoney($amountTtc) . ' en retard depuis le ' . ($current->due_date?->format('d/m/Y') ?? '-') . '.';
                } else {
                    $alertLabel = 'À payer';
                    $alertClass = 'bg-amber-50 border-amber-200 text-amber-700';
                    $alertDot = 'bg-amber-500';
                    $amountTtc = (float) ($current->amount_ttc ?? $current->amount_due ?? 0);
                    $alertMessage = $formatMoney($amountTtc) . ' à régler avant le ' . ($current->due_date?->format('d/m/Y') ?? '-') . '.';
                }
            @endphp

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                            {{ $contract->reservation?->space?->name ?? 'Espace non précisé' }}
                        </p>

                        <h2 class="mt-1 text-lg font-black text-slate-900">
                            {{ $contract->title ?? 'Contrat #' . $contract->id }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $row['paid_installments_count'] }} / {{ $row['installments_count'] }} échéances déjà réglées
                        </p>
                    </div>

                    <a href="{{ route('client.payments.schedule', $contract) }}"
                       class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Voir l'historique
                    </a>
                </div>

                <div class="flex items-center gap-3 border-t border-slate-100 px-6 py-4 {{ $alertClass }}">
                    <span class="h-2.5 w-2.5 flex-shrink-0 rounded-full {{ $alertDot }}"></span>

                    <p class="text-sm font-bold">
                        {{ $alertLabel }}
                        <span class="ml-1 font-medium">
                            — {{ $alertMessage }}
                        </span>
                    </p>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                <p class="font-black text-slate-800">
                    Aucun paiement trouvé
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    Vos échéances apparaîtront ici dès qu'elles seront créées.
                </p>
            </div>
        @endforelse
    </div>
</div>
@endsection
