@extends('layouts.app')

@section('title', 'Paiements - Hello Desk')

@section('content')
@php
    $pagePayments = method_exists($payments, 'getCollection')
        ? $payments->getCollection()
        : collect($payments);

    $totalPayments = method_exists($payments, 'total')
        ? $payments->total()
        : $pagePayments->count();

    $visibleCount = $pagePayments->count();

    $totalDueVisible = $pagePayments->sum(fn ($payment) => (float) ($payment->amount_due ?? 0));
    $totalPaidVisible = $pagePayments->sum(fn ($payment) => (float) ($payment->amount_paid ?? 0));
    $remainingVisible = max($totalDueVisible - $totalPaidVisible, 0);

    $dueVisible = $pagePayments->where('status', 'due')->count();
    $paidVisible = $pagePayments->where('status', 'paid')->count();
    $lateVisible = $pagePayments->where('status', 'late')->count();
    $cancelledVisible = $pagePayments->where('status', 'cancelled')->count();

    $paidAmountRate = $totalDueVisible > 0
        ? min(100, round(($totalPaidVisible / $totalDueVisible) * 100))
        : 0;

    $remainingAmountRate = $totalDueVisible > 0
        ? min(100, round(($remainingVisible / $totalDueVisible) * 100))
        : 0;

    $lateRate = $visibleCount > 0
        ? round(($lateVisible / $visibleCount) * 100)
        : 0;

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

    $statusDots = [
        'due' => 'bg-amber-500',
        'paid' => 'bg-emerald-500',
        'late' => 'bg-rose-500',
        'cancelled' => 'bg-slate-500',
    ];
@endphp

<div class="min-h-screen bg-[#f6f8f6]">
    <div class="mx-auto max-w-7xl px-6 py-8">

        {{-- Header --}}
        <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="grid gap-0 lg:grid-cols-[1.35fr_0.65fr]">
                <div class="bg-gradient-to-br from-[#284625] via-[#315f32] to-[#4b7f3f] p-8 text-white">
                    <p class="text-sm font-semibold uppercase tracking-wide text-white/75">
                        Administration
                    </p>

                    <h1 class="mt-3 text-3xl font-bold">
                        Paiements et échéances
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                        Suivez les montants dus, les paiements reçus, les retards et les échéances
                        liées aux contrats Hello Desk.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('admin.payments.create') }}"
                           class="inline-flex h-11 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#284625] shadow-sm transition hover:bg-gray-100">
                            Ajouter une échéance
                        </a>

                        <a href="#payments-list"
                           class="inline-flex h-11 items-center justify-center rounded-xl border border-white/25 bg-white/10 px-5 text-sm font-bold text-white transition hover:bg-white/15">
                            Voir les paiements
                        </a>
                    </div>
                </div>

                {{-- Mini summary --}}
                <div class="bg-white p-8">
                    <h2 class="text-lg font-bold text-gray-900">
                        Suivi financier
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        État des montants sur les échéances affichées.
                    </p>

                    <div class="mt-6 space-y-5">
                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Montant payé</span>
                                <span class="font-bold text-emerald-600">{{ $paidAmountRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-emerald-500" @style(['width: ' . $paidAmountRate . '%'])></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Reste à payer</span>
                                <span class="font-bold text-amber-600">{{ $remainingAmountRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-amber-500" @style(['width: ' . $remainingAmountRate . '%'])></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Échéances en retard</span>
                                <span class="font-bold text-rose-600">{{ $lateRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-rose-500" @style(['width: ' . $lateRate . '%'])></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Success --}}
        @if(session('success'))
            <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- KPI cards --}}
        <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Total échéances</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $totalPayments }}</p>
                    </div>

                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">
                        Global
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 w-full rounded-full bg-sky-500"></div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Montant dû</p>
                        <p class="mt-3 text-2xl font-bold text-gray-900">
                            {{ number_format($totalDueVisible, 2, ',', ' ') }} DH
                        </p>
                    </div>

                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                        À payer
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 w-full rounded-full bg-amber-500"></div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Montant payé</p>
                        <p class="mt-3 text-2xl font-bold text-gray-900">
                            {{ number_format($totalPaidVisible, 2, ',', ' ') }} DH
                        </p>
                    </div>

                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                        Payé
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-emerald-500" @style(['width: ' . $paidAmountRate . '%'])></div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">En retard</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $lateVisible }}</p>
                    </div>

                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">
                        Alertes
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-rose-500" @style(['width: ' . $lateRate . '%'])></div>
                </div>
            </div>
        </section>

        {{-- Filters --}}
        <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        Recherche et filtres
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Filtrez les échéances par client, email, contrat ou statut.
                    </p>
                </div>

                <a href="{{ route('admin.payments.index') }}#payments-list"
                   class="text-sm font-bold text-slate-500 transition hover:text-[#284625] hover:underline">
                    Réinitialiser
                </a>
            </div>

            <form id="paymentFilters"
                  method="GET"
                  action="{{ route('admin.payments.index') }}#payments-list"
                  class="grid gap-4 lg:grid-cols-[1.5fr_1fr_auto]">

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Recherche
                    </label>

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Client, email, contrat..."
                           class="filter-search h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Statut
                    </label>

                    <select name="status"
                            class="filter-auto h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="all" @selected(request('status', 'all') === 'all')>Tous les statuts</option>
                        <option value="due" @selected(request('status') === 'due')>À payer</option>
                        <option value="paid" @selected(request('status') === 'paid')>Payé</option>
                        <option value="late" @selected(request('status') === 'late')>En retard</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Annulé</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                        Filtrer
                    </button>
                </div>
            </form>
        </section>

        {{-- List --}}
        <section id="payments-list" class="mt-8 scroll-mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Liste des échéances
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Suivez les montants dus, les paiements reçus et les retards.
                        </p>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                        {{ $totalPayments }} résultat(s)
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Client</th>
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
                                $statusClass = $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';
                                $statusDot = $statusDots[$status] ?? 'bg-slate-500';
                                $statusLabel = $statusLabels[$status] ?? ucfirst($status);

                                $amountDue = (float) ($payment->amount_due ?? 0);
                                $amountPaid = (float) ($payment->amount_paid ?? 0);
                                $remaining = max($amountDue - $amountPaid, 0);
                                $rowPaidRate = $amountDue > 0 ? min(100, round(($amountPaid / $amountDue) * 100)) : 0;
                            @endphp

                            <tr class="transition hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sm font-bold text-sky-700 ring-1 ring-sky-100">
                                            {{ strtoupper(substr($payment->client?->full_name ?? 'C', 0, 1)) }}
                                        </div>

                                        <div>
                                            <p class="font-bold text-gray-900">
                                                {{ $payment->client?->full_name ?? 'Client supprimé' }}
                                            </p>

                                            <p class="mt-0.5 text-xs text-gray-500">
                                                {{ $payment->client?->email ?? 'Email non disponible' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800">
                                        {{ $payment->contract?->title ?? 'Contrat supprimé' }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        Contrat #{{ $payment->contract?->id ?? '-' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <p class="font-medium">
                                        {{ $payment->due_date?->format('d/m/Y') ?? '-' }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        Date limite de paiement
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="min-w-[170px]">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500">Payé</span>
                                            <span class="font-bold text-gray-900">
                                                {{ number_format($amountPaid, 2, ',', ' ') }} DH
                                            </span>
                                        </div>

                                        <div class="mt-2 h-1.5 rounded-full bg-gray-100">
                                            <div class="h-1.5 rounded-full bg-emerald-500" @style(['width: ' . $rowPaidRate . '%'])></div>
                                        </div>

                                        <div class="mt-2 flex justify-between text-xs">
                                            <span class="text-gray-500">
                                                Dû : {{ number_format($amountDue, 2, ',', ' ') }} DH
                                            </span>

                                            <span class="font-semibold text-amber-700">
                                                Reste : {{ number_format($remaining, 2, ',', ' ') }} DH
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">
                                        <span class="h-2 w-2 rounded-full {{ $statusDot }}"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-end">
                                        <a href="{{ route('admin.payments.show', $payment) }}"
                                           class="inline-flex h-9 items-center justify-center rounded-xl bg-sky-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-sky-700">
                                            Voir dossier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl font-bold text-slate-400">
                                            +
                                        </div>

                                        <p class="mt-4 font-bold text-gray-800">
                                            Aucune échéance trouvée
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            Ajoutez une échéance pour suivre les paiements des clients.
                                        </p>

                                        <a href="{{ route('admin.payments.create') }}"
                                           class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                                            Ajouter une échéance
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $payments->links() }}
                </div>
            @endif
        </section>
    </div>
</div>

<script>
    const paymentFilterForm = document.getElementById('paymentFilters');
    const paymentAutoFilters = document.querySelectorAll('.filter-auto');
    const paymentSearchInput = document.querySelector('.filter-search');

    paymentAutoFilters.forEach((filter) => {
        filter.addEventListener('change', () => {
            paymentFilterForm.submit();
        });
    });

    let paymentSearchTimer;

    if (paymentSearchInput) {
        paymentSearchInput.addEventListener('input', () => {
            clearTimeout(paymentSearchTimer);

            paymentSearchTimer = setTimeout(() => {
                paymentFilterForm.submit();
            }, 600);
        });
    }
</script>
@endsection