@extends('layouts.app')

@section('title', 'Modifier paiement - Hello Desk')

@section('content')
@php
    $oldTaxRate = old('tax_rate', $payment->tax_rate ?? 20);
    $oldAmountHt = old('amount_ht', $payment->amount_ht ?? null);

    if (!$oldAmountHt && $payment->amount_due) {
        $oldAmountHt = round((float) $payment->amount_due / (1 + ((float) $oldTaxRate / 100)), 2);
    }
@endphp

<div class="mx-auto max-w-5xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.payments.show', $payment) }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour au paiement
        </a>

        <h1 class="mt-4 text-2xl font-bold text-gray-900">
            Modifier le paiement
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Modifiez les informations de l’échéance, du règlement, du reçu et de la facture.
        </p>
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
          enctype="multipart/form-data"
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

        <div class="space-y-8">

            <section>
                <h2 class="text-lg font-bold text-gray-900">
                    1. Échéance
                </h2>

                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Date d’échéance <span class="text-red-500">*</span>
                        </label>

                        <input type="date"
                               name="due_date"
                               value="{{ old('due_date', $payment->due_date?->format('Y-m-d')) }}"
                               required
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Durée <span class="text-gray-400">(optionnel)</span>
                        </label>

                        <input type="text"
                               name="duration_label"
                               value="{{ old('duration_label', $payment->duration_label) }}"
                               placeholder="Ex : 1 mois, 3 jours, 2 heures..."
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-lg font-bold text-gray-900">
                    2. Montants
                </h2>

                <div class="mt-4 grid gap-5 md:grid-cols-4">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Montant HT <span class="text-red-500">*</span>
                        </label>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="amount_ht"
                               id="amount_ht"
                               value="{{ $oldAmountHt }}"
                               required
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            TVA %
                        </label>

                        <input type="number"
                               step="0.01"
                               min="0"
                               max="100"
                               name="tax_rate"
                               id="tax_rate"
                               value="{{ $oldTaxRate }}"
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Montant TVA
                        </label>

                        <input type="text"
                               id="tax_amount_preview"
                               value="0.00 DH"
                               disabled
                               class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-gray-700">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Montant TTC
                        </label>

                        <input type="text"
                               id="amount_ttc_preview"
                               value="0.00 DH"
                               disabled
                               class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-bold text-[#284625]">
                    </div>

                    <div class="md:col-span-2">
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

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Statut <span class="text-red-500">*</span>
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
                </div>
            </section>

            <section>
                <h2 class="text-lg font-bold text-gray-900">
                    3. Mode de paiement
                </h2>

                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Mode de paiement
                        </label>

                        <select name="payment_method"
                                id="payment_method"
                                class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                            <option value="">Non précisé</option>
                            <option value="cash" @selected(old('payment_method', $payment->payment_method) === 'cash')>Espèces</option>
                            <option value="cheque" @selected(old('payment_method', $payment->payment_method) === 'cheque')>Chèque</option>
                            <option value="bank_transfer" @selected(old('payment_method', $payment->payment_method) === 'bank_transfer')>Virement bancaire</option>
                            <option value="tpe" @selected(old('payment_method', $payment->payment_method) === 'tpe')>TPE</option>
                            <option value="other" @selected(old('payment_method', $payment->payment_method) === 'other')>Autre</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Référence générale
                        </label>

                        <input type="text"
                               name="reference"
                               value="{{ old('reference', $payment->reference) }}"
                               placeholder="Référence interne ou transaction..."
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>
                </div>

                <div id="cheque_fields" class="mt-5 hidden rounded-2xl border border-gray-200 bg-gray-50 p-5">
                    <h3 class="text-sm font-bold text-gray-900">
                        Détails du chèque
                    </h3>

                    <div class="mt-4 grid gap-5 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">N° chèque</label>
                            <input type="text"
                                   name="cheque_number"
                                   value="{{ old('cheque_number', $payment->cheque_number) }}"
                                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Banque</label>
                            <input type="text"
                                   name="cheque_bank"
                                   value="{{ old('cheque_bank', $payment->cheque_bank) }}"
                                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Date du chèque</label>
                            <input type="date"
                                   name="cheque_date"
                                   value="{{ old('cheque_date', $payment->cheque_date?->format('Y-m-d')) }}"
                                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        </div>
                    </div>
                </div>

                <div id="bank_transfer_fields" class="mt-5 hidden rounded-2xl border border-gray-200 bg-gray-50 p-5">
                    <h3 class="text-sm font-bold text-gray-900">
                        Détails du virement bancaire
                    </h3>

                    <div class="mt-4 grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Référence virement</label>
                            <input type="text"
                                   name="bank_transfer_reference"
                                   value="{{ old('bank_transfer_reference', $payment->bank_transfer_reference) }}"
                                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Banque</label>
                            <input type="text"
                                   name="bank_name"
                                   value="{{ old('bank_name', $payment->bank_name) }}"
                                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        </div>
                    </div>
                </div>

                <div id="tpe_fields" class="mt-5 hidden rounded-2xl border border-gray-200 bg-gray-50 p-5">
                    <h3 class="text-sm font-bold text-gray-900">
                        Détails TPE
                    </h3>

                    <div class="mt-4">
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Référence transaction TPE</label>
                        <input type="text"
                               name="tpe_transaction_reference"
                               value="{{ old('tpe_transaction_reference', $payment->tpe_transaction_reference) }}"
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-lg font-bold text-gray-900">
                    4. Reçu et facture
                </h2>

                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            N° reçu
                        </label>

                        <input type="text"
                               name="receipt_number"
                               value="{{ old('receipt_number', $payment->receipt_number) }}"
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

                        @if($payment->receipt_file)
                            <a href="{{ asset('storage/' . $payment->receipt_file) }}"
                               target="_blank"
                               class="mt-2 inline-flex text-xs font-semibold text-[#284625] hover:underline">
                                Voir le reçu actuel
                            </a>
                        @endif
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Nouveau fichier reçu
                        </label>

                        <input type="file"
                               name="receipt_file"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[#284625] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            N° facture
                        </label>

                        <input type="text"
                               name="invoice_number"
                               value="{{ old('invoice_number', $payment->invoice_number) }}"
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

                        @if($payment->invoice_file)
                            <a href="{{ asset('storage/' . $payment->invoice_file) }}"
                               target="_blank"
                               class="mt-2 inline-flex text-xs font-semibold text-[#284625] hover:underline">
                                Voir la facture actuelle
                            </a>
                        @endif
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Nouveau fichier facture
                        </label>

                        <input type="file"
                               name="invoice_file"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[#284625] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90">
                    </div>
                </div>
            </section>

            <section>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Notes internes
                </label>

                <textarea name="notes"
                          rows="4"
                          class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">{{ old('notes', $payment->notes) }}</textarea>
            </section>
        </div>

        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
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

<script>
    const amountHtInput = document.getElementById('amount_ht');
    const taxRateInput = document.getElementById('tax_rate');
    const taxAmountPreview = document.getElementById('tax_amount_preview');
    const amountTtcPreview = document.getElementById('amount_ttc_preview');

    const paymentMethod = document.getElementById('payment_method');
    const chequeFields = document.getElementById('cheque_fields');
    const bankTransferFields = document.getElementById('bank_transfer_fields');
    const tpeFields = document.getElementById('tpe_fields');

    function formatMoney(value) {
        return Number(value || 0).toFixed(2) + ' DH';
    }

    function calculateAmounts() {
        const amountHt = parseFloat(amountHtInput.value) || 0;
        const taxRate = parseFloat(taxRateInput.value) || 0;

        const taxAmount = amountHt * taxRate / 100;
        const amountTtc = amountHt + taxAmount;

        taxAmountPreview.value = formatMoney(taxAmount);
        amountTtcPreview.value = formatMoney(amountTtc);
    }

    function togglePaymentFields() {
        chequeFields.classList.add('hidden');
        bankTransferFields.classList.add('hidden');
        tpeFields.classList.add('hidden');

        if (paymentMethod.value === 'cheque') {
            chequeFields.classList.remove('hidden');
        }

        if (paymentMethod.value === 'bank_transfer') {
            bankTransferFields.classList.remove('hidden');
        }

        if (paymentMethod.value === 'tpe') {
            tpeFields.classList.remove('hidden');
        }
    }

    amountHtInput.addEventListener('input', calculateAmounts);
    taxRateInput.addEventListener('input', calculateAmounts);
    paymentMethod.addEventListener('change', togglePaymentFields);

    calculateAmounts();
    togglePaymentFields();
</script>
@endsection