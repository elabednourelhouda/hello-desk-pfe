@extends('layouts.app')

@section('title', 'Détail paiement - Hello Desk')

@section('content')
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
                    Paiement lié à un contrat Hello Desk.
                </p>
            </div>

            <a href="{{ route('admin.payments.edit', $payment) }}"
               class="rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                Modifier le paiement
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Informations paiement</h2>

            <dl class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Client</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">
                        {{ $payment->client?->full_name ?? 'Client supprimé' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Échéance</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $payment->due_date?->format('d/m/Y') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Montant à payer</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ number_format($payment->amount_due, 2, ',', ' ') }} DH
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Montant payé</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ number_format($payment->amount_paid, 2, ',', ' ') }} DH
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Statut</dt>
                    <dd class="mt-1">
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                            {{ $payment->status }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Payé le</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $payment->paid_at?->format('d/m/Y H:i') ?? 'Non payé' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Méthode</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $payment->payment_method ?? 'Non précisée' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Référence</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $payment->reference ?? 'Non précisée' }}
                    </dd>
                </div>
            </dl>

            @if($payment->notes)
                <div class="mt-6 rounded-xl bg-gray-50 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Notes</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        {{ $payment->notes }}
                    </p>
                </div>
            @endif
        </section>

        <aside class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Contrat lié</h2>

            <div class="mt-5 space-y-4 text-sm">
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-400">Contrat</p>
                    <p class="mt-1 font-medium text-gray-900">
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
                    <a href="{{ route('admin.contracts.show', $payment->contract) }}"
                       class="inline-flex w-full justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Voir le contrat
                    </a>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection