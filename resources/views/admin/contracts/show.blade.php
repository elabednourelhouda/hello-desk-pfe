@extends('layouts.app')

@section('title', 'Détail contrat - Hello Desk')

@section('content')
<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.contracts.index') }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour aux contrats
        </a>

        <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $contract->title }}
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Contrat lié à une réservation Hello Desk.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.payments.create', ['contract_id' => $contract->id]) }}"
                   class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Ajouter une échéance
                </a>

                <a href="{{ route('admin.contracts.document', $contract) }}"
                class="inline-flex justify-center rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                    Générer le contrat
                </a>

                <a href="{{ route('admin.contracts.edit', $contract) }}"
                   class="inline-flex justify-center rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                    Modifier / Importer PDF
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-lg font-bold text-gray-900">Informations du contrat</h2>

            <dl class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Client</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">
                        {{ $contract->client?->full_name ?? 'Client supprimé' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Email client</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $contract->client?->email ?? 'Non précisé' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Date début</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $contract->start_date?->format('d/m/Y') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Date fin</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $contract->end_date?->format('d/m/Y') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">Statut</dt>
                    <dd class="mt-1">
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                            {{ $contract->status }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-400">PDF</dt>
                    <dd class="mt-1 text-sm text-gray-700">
                        {{ $contract->pdf_path ? 'Importé' : 'Non importé' }}
                    </dd>
                </div>
            </dl>

            @if($contract->notes)
                <div class="mt-6 rounded-xl bg-gray-50 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Notes</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        {{ $contract->notes }}
                    </p>
                </div>
            @endif
        </section>

        <aside class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Réservation liée</h2>

            <div class="mt-5 space-y-4 text-sm">
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-400">Espace</p>
                    <p class="mt-1 font-medium text-gray-900">
                        {{ $contract->reservation?->space?->name ?? 'Non précisé' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase text-gray-400">Campus</p>
                    <p class="mt-1 text-gray-700">
                        {{ $contract->reservation?->campus?->name ?? 'Non précisé' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase text-gray-400">Étage</p>
                    <p class="mt-1 text-gray-700">
                        {{ $contract->reservation?->floor?->name ?? 'Non précisé' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase text-gray-400">Statut réservation</p>
                    <p class="mt-1 text-gray-700">
                        {{ $contract->reservation?->status ?? 'Non précisé' }}
                    </p>
                </div>

                @if($contract->reservation)
                    <a href="{{ route('admin.reservations.show', $contract->reservation) }}"
                       class="inline-flex w-full justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Voir la réservation
                    </a>
                @endif
            </div>

            <div class="mt-6 border-t border-gray-100 pt-6">
                <h3 class="text-sm font-bold text-gray-900">Fichier PDF</h3>

                @if($contract->pdf_path)
                    <a href="{{ asset('storage/' . $contract->pdf_path) }}"
                       target="_blank"
                       class="mt-3 inline-flex w-full justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                        Ouvrir le PDF
                    </a>
                @else
                    <div class="mt-3 rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-700">
                        Le PDF du contrat n’est pas encore importé.
                    </div>
                @endif
            </div>
        </aside>
    </div>

    @php
        $payments = $contract->payments ?? collect();
        $totalDue = $payments->sum('amount_due');
        $totalPaid = $payments->sum('amount_paid');
        $remaining = max(0, $totalDue - $totalPaid);
    @endphp

    <section class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Échéances de paiement</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Suivi des paiements liés à ce contrat.
                </p>
            </div>

            <a href="{{ route('admin.payments.create', ['contract_id' => $contract->id]) }}"
               class="inline-flex justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                Ajouter une échéance
            </a>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-3">
            <div class="rounded-xl bg-gray-50 p-4">
                <p class="text-xs font-semibold uppercase text-gray-400">Total à payer</p>
                <p class="mt-1 text-lg font-bold text-gray-900">
                    {{ number_format($totalDue, 2, ',', ' ') }} DH
                </p>
            </div>

            <div class="rounded-xl bg-green-50 p-4">
                <p class="text-xs font-semibold uppercase text-green-600">Total payé</p>
                <p class="mt-1 text-lg font-bold text-green-700">
                    {{ number_format($totalPaid, 2, ',', ' ') }} DH
                </p>
            </div>

            <div class="rounded-xl bg-yellow-50 p-4">
                <p class="text-xs font-semibold uppercase text-yellow-600">Reste à payer</p>
                <p class="mt-1 text-lg font-bold text-yellow-700">
                    {{ number_format($remaining, 2, ',', ' ') }} DH
                </p>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-xl border border-gray-200">
            @if($payments->count())
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Échéance</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Montant dû</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Montant payé</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Statut</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($payments as $payment)
                            <tr>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $payment->due_date?->format('d/m/Y') }}
                                </td>

                                <td class="px-4 py-3 text-gray-700">
                                    {{ number_format($payment->amount_due, 2, ',', ' ') }} DH
                                </td>

                                <td class="px-4 py-3 text-gray-700">
                                    {{ number_format($payment->amount_paid, 2, ',', ' ') }} DH
                                </td>

                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        {{ $payment->real_status }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.payments.show', $payment) }}"
                                           class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                            Voir
                                        </a>

                                        @if($payment->status !== 'paid')
                                            <form method="POST" action="{{ route('admin.payments.markAsPaid', $payment) }}">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        class="rounded-lg bg-[#284625] px-3 py-2 text-xs font-semibold text-white hover:opacity-90">
                                                    Marquer payé
                                                </button>
                                            </form>
                                        @else
                                            <span class="rounded-lg bg-green-50 px-3 py-2 text-xs font-semibold text-green-700">
                                                Payé
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-6 text-center text-sm text-gray-500">
                    Aucune échéance n’a encore été ajoutée pour ce contrat.
                </div>
            @endif
        </div>
    </section>
</div>
@endsection