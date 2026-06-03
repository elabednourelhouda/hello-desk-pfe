@extends('layouts.app')

@section('title', 'Paiements - Hello Desk')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-8">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Paiements et échéances</h1>
            <p class="mt-1 text-sm text-gray-500">
                Suivez les montants à payer, payés, en retard ou annulés.
            </p>
        </div>

        <a href="{{ route('admin.payments.create') }}"
           class="rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:opacity-90">
            Ajouter une échéance
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET"
          action="{{ route('admin.payments.index') }}"
          class="mb-6 grid gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-3">
        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">Recherche</label>

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Client, email, contrat..."
                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">Statut</label>

            <select name="status"
                    class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                <option value="all" @selected(request('status', 'all') === 'all')>Tous</option>
                <option value="due" @selected(request('status') === 'due')>À payer</option>
                <option value="paid" @selected(request('status') === 'paid')>Payé</option>
                <option value="late" @selected(request('status') === 'late')>En retard</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Annulé</option>
            </select>
        </div>

        <div class="flex items-end gap-3">
            <button type="submit"
                    class="h-12 flex-1 rounded-xl bg-[#284625] px-5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                Filtrer
            </button>

            <a href="{{ route('admin.payments.index') }}"
               class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-300 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-4">Client</th>
                    <th class="px-5 py-4">Contrat</th>
                    <th class="px-5 py-4">Échéance</th>
                    <th class="px-5 py-4">Montant</th>
                    <th class="px-5 py-4">Payé</th>
                    <th class="px-5 py-4">Statut</th>
                    <th class="px-5 py-4 text-right">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($payments as $payment)
                    @php
                        $statusLabels = [
                            'due' => 'À payer',
                            'paid' => 'Payé',
                            'late' => 'En retard',
                            'cancelled' => 'Annulé',
                        ];

                        $statusClasses = [
                            'due' => 'bg-yellow-50 text-yellow-700',
                            'paid' => 'bg-green-50 text-green-700',
                            'late' => 'bg-red-50 text-red-700',
                            'cancelled' => 'bg-gray-100 text-gray-700',
                        ];
                    @endphp

                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4 font-semibold text-gray-900">
                            {{ $payment->client?->full_name ?? 'Client supprimé' }}
                        </td>

                        <td class="px-5 py-4 text-gray-700">
                            {{ $payment->contract?->title ?? 'Contrat supprimé' }}
                        </td>

                        <td class="px-5 py-4 text-gray-600">
                            {{ $payment->due_date?->format('d/m/Y') }}
                        </td>

                        <td class="px-5 py-4 text-gray-700">
                            {{ number_format($payment->amount_due, 2, ',', ' ') }} DH
                        </td>

                        <td class="px-5 py-4 text-gray-700">
                            {{ number_format($payment->amount_paid, 2, ',', ' ') }} DH
                        </td>

                        <td class="px-5 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$payment->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$payment->status] ?? $payment->status }}
                            </span>
                        </td>

                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.payments.show', $payment) }}"
                               class="font-semibold text-[#284625] hover:underline">
                                Voir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                            Aucune échéance trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $payments->links() }}
    </div>
</div>
@endsection