@extends('layouts.app')

@section('title', 'Détail contrat - Hello Desk')

@section('content')
@php
    $statusLabels = [
        'draft' => 'En préparation',
        'active' => 'Actif',
        'expired' => 'Expiré',
        'cancelled' => 'Annulé',
    ];

    $statusClasses = [
        'draft' => 'bg-purple-50 text-purple-700 ring-purple-200',
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'expired' => 'bg-pink-50 text-pink-700 ring-pink-200',
        'cancelled' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];

    $paymentStatusClasses = [
        'À payer' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'Payé' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'En retard' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'Annulé' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];

    $statusClass = $statusClasses[$contract->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
@endphp

<div class="mx-auto max-w-7xl px-6 py-8">

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <a href="{{ route('client.contracts.index') }}"
               class="text-sm font-bold text-[#284625] hover:underline">
                ← Retour aux contrats
            </a>

            <h1 class="mt-3 text-3xl font-black text-slate-900">
                Détail du contrat
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Consultez les informations de votre contrat Hello Desk.
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            @if($contract->pdf_path)
                <a href="{{ asset('storage/' . $contract->pdf_path) }}"
                   target="_blank"
                   class="inline-flex h-11 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#20391f]">
                    Télécharger le PDF signé
                </a>
            @elseif($contract->status === 'active')
                <a href="{{ route('client.contracts.document', $contract) }}"
                   class="inline-flex h-11 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#20391f]">
                    Voir / exporter le contrat
                </a>
            @else
                <span class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-600 shadow-sm">
                    Contrat en préparation
                </span>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.4fr_0.8fr]">

        {{-- Main contract card --}}
        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-[#284625]">
                            Contrat
                        </p>

                        <h2 class="mt-2 text-xl font-black text-slate-900">
                            {{ $contract->title }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Réf. HD-CONTRAT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>

                    <span class="w-fit rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">
                        {{ $statusLabels[$contract->status] ?? ucfirst($contract->status) }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                            Client
                        </p>
                        <p class="mt-2 text-sm font-bold text-slate-900">
                            {{ $contract->client?->full_name ?? 'Non précisé' }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                            Espace
                        </p>
                        <p class="mt-2 text-sm font-bold text-slate-900">
                            {{ $contract->reservation?->space?->name ?? 'Non précisé' }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                            Campus
                        </p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">
                            {{ $contract->reservation?->campus?->name ?? 'Non précisé' }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                            Étage
                        </p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">
                            {{ $contract->reservation?->floor?->name ?? 'Non précisé' }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                            Date début
                        </p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">
                            {{ $contract->start_date?->format('d/m/Y') }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                            Date fin
                        </p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">
                            {{ $contract->end_date?->format('d/m/Y') }}
                        </p>
                    </div>
                </div>

                @if($contract->notes)
                    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5">
                        <h3 class="text-sm font-black text-slate-900">
                            Notes du contrat
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $contract->notes }}
                        </p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Right side --}}
        <div class="space-y-6">

            {{-- Document status --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">
                    Document
                </h2>

                <div class="mt-4 rounded-2xl bg-slate-50 p-5">
                    @if($contract->pdf_path)
                        <p class="text-sm font-black text-[#284625]">
                            PDF signé disponible
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            Le contrat signé a été importé par l’administration.
                        </p>
                    @elseif($contract->status === 'active')
                        <p class="text-sm font-black text-blue-700">
                            Export disponible
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            Vous pouvez consulter et exporter le contrat généré.
                        </p>
                    @else
                        <p class="text-sm font-black text-slate-700">
                            Contrat en préparation
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            Le contrat sera disponible après validation.
                        </p>
                    @endif
                </div>
            </section>

            {{-- Payments --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">
                    Échéances liées
                </h2>

                <div class="mt-5 space-y-3">
                    @forelse($contract->payments as $payment)
                        @php
                            $paymentClass = $paymentStatusClasses[$payment->real_status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                        @endphp

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-black text-slate-900">
                                        {{ number_format($payment->amount_due, 2, ',', ' ') }} DH
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Échéance : {{ $payment->due_date?->format('d/m/Y') }}
                                    </p>
                                </div>

                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $paymentClass }}">
                                    {{ $payment->real_status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">
                            Aucune échéance liée.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
@endsection