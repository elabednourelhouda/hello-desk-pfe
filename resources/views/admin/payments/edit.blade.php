@extends('layouts.app')

@section('title', 'Modifier paiement - Hello Desk')

@section('content')
<div class="mx-auto max-w-4xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.payments.show', $payment) }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour au paiement
        </a>

        <h1 class="mt-4 text-2xl font-bold text-gray-900">
            Modifier le paiement
        </h1>
    </div>

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Veuillez corriger les erreurs suivantes :</p>

            <ul class="mt-2 list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.payments.update', $payment) }}"
          class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="mb-6 rounded-2xl bg-[#284625]/5 p-5">
            <p class="text-xs font-bold uppercase tracking-wide text-[#284625]">
                Contrat lié
            </p>

            <h2 class="mt-2 text-lg font-bold text-gray-900">
                {{ $payment->contract?->title ?? 'Contrat supprimé' }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Client : {{ $payment->client?->full_name ?? 'Client supprimé' }}
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Date d’échéance
                </label>

                <input type="date"
                       name="due_date"
                       value="{{ old('due_date', $payment->due_date?->format('Y-m-d')) }}"
                       required
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Montant à payer
                </label>

                <input type="number"
                       step="0.01"
                       min="0"
                       name="amount_due"
                       value="{{ old('amount_due', $payment->amount_due) }}"
                       required
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Montant payé
                </label>

                <input type="number"
                       step="0.01"
                       min="0"
                       name="amount_paid"
                       value="{{ old('amount_paid', $payment->amount_paid) }}"
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Statut
                </label>

                <select name="status"
                        required
                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <option value="due" @selected(old('status', $payment->status) === 'due')>À payer</option>
                    <option value="paid" @selected(old('status', $payment->status) === 'paid')>Payé</option>
                    <option value="late" @selected(old('status', $payment->status) === 'late')>En retard</option>
                    <option value="cancelled" @selected(old('status', $payment->status) === 'cancelled')>Annulé</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Méthode de paiement
                </label>

                <input type="text"
                       name="payment_method"
                       value="{{ old('payment_method', $payment->payment_method) }}"
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Référence
                </label>

                <input type="text"
                       name="reference"
                       value="{{ old('reference', $payment->reference) }}"
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Notes
            </label>

            <textarea name="notes"
                      rows="4"
                      class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">{{ old('notes', $payment->notes) }}</textarea>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.payments.show', $payment) }}"
               class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-300 px-5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Annuler
            </a>

            <button type="submit"
                    class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection