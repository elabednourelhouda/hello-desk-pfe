@extends('layouts.app')

@section('title', 'Ajouter une échéance - Hello Desk')

@section('content')
<div class="mx-auto max-w-5xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ $selectedContract ? route('admin.contracts.show', $selectedContract) : route('admin.payments.index') }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← {{ $selectedContract ? 'Retour au contrat' : 'Retour aux paiements' }}
        </a>

        <h1 class="mt-4 text-2xl font-bold text-gray-900">
            Ajouter une échéance
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Créez une échéance de paiement avec durée, montant HT, TVA, TTC, mode de paiement, reçu et facture.
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

    @if($selectedContract)
        <section class="mb-6 rounded-2xl border border-[#284625]/20 bg-[#284625]/5 p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#284625]">
                Contrat sélectionné
            </p>

            <h2 class="mt-2 text-xl font-bold text-gray-900">
                {{ $selectedContract->title }}
            </h2>

            <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                    Client : {{ $selectedContract->client?->full_name }}
                </span>

                <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                    Espace : {{ $selectedContract->reservation?->space?->name ?? 'Non précisé' }}
                </span>

                <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                    Statut : {{ $selectedContract->status }}
                </span>
            </div>
        </section>
    @endif

    <form method="POST"
          action="{{ route('admin.payments.store') }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf

        @if($selectedContract)
            <input type="hidden" name="redirect_to_contract" value="1">
        @endif

        <div class="space-y-8">

            {{-- Contrat --}}
            <section>
                <h2 class="text-lg font-bold text-gray-900">
                    1. Contrat et échéance
                </h2>

                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Contrat <span class="text-red-500">*</span>
                        </label>

                        @if($selectedContract)
                            <input type="hidden" name="contract_id" value="{{ $selectedContract->id }}">

                            <div class="flex h-12 items-center rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-gray-700">
                                {{ $selectedContract->title }} — {{ $selectedContract->client?->full_name }}
                            </div>
                        @else
                            <select name="contract_id"
                                    required
                                    class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                                <option value="">Choisir un contrat</option>

                                @foreach($contracts as $contract)
                                    <option value="{{ $contract->id }}" @selected(old('contract_id') == $contract->id)>
                                        {{ $contract->title }} — {{ $contract->client?->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Date d’échéance <span class="text-red-500">*</span>
                        </label>

                        <input type="date"
                               name="due_date"
                               value="{{ old('due_date') }}"
                               required
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Durée <span class="text-gray-400">(optionnel)</span>
                        </label>

                        <input type="text"
                               name="duration_label"
                               value="{{ old('duration_label') }}"
                               placeholder="Ex : 1 mois, 3 jours, 2 heures..."
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>
                </div>
            </section>

            {{-- Montants --}}
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
                               value="{{ old('amount_ht') }}"
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
                               value="{{ old('tax_rate', 20) }}"
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
                               value="{{ old('amount_paid', 0) }}"
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Statut <span class="text-red-500">*</span>
                        </label>

                        <select name="status"
                                required
                                class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                            <option value="due" @selected(old('status', 'due') === 'due')>À payer</option>
                            <option value="paid" @selected(old('status') === 'paid')>Payé</option>
                            <option value="late" @selected(old('status') === 'late')>En retard</option>
                            <option value="cancelled" @selected(old('status') === 'cancelled')>Annulé</option>
                        </select>
                    </div>
                </div>
            </section>

            {{-- Paiement --}}
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
                            <option value="cash" @selected(old('payment_method') === 'cash')>Espèces</option>
                            <option value="cheque" @selected(old('payment_method') === 'cheque')>Chèque</option>
                            <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Virement bancaire</option>
                            <option value="tpe" @selected(old('payment_method') === 'tpe')>TPE</option>
                            <option value="other" @selected(old('payment_method') === 'other')>Autre</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Référence générale
                        </label>

                        <input type="text"
                               name="reference"
                               value="{{ old('reference') }}"
                               placeholder="Référence interne ou transaction..."
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>
                </div>

                {{-- Chèque --}}
                <div id="cheque_fields" class="mt-5 hidden rounded-2xl border border-gray-200 bg-gray-50 p-5">
                    <h3 class="text-sm font-bold text-gray-900">
                        Détails du chèque
                    </h3>

                    <div class="mt-4 grid gap-5 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">N° chèque</label>
                            <input type="text" name="cheque_number" value="{{ old('cheque_number') }}"
                                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Banque</label>
                            <input type="text" name="cheque_bank" value="{{ old('cheque_bank') }}"
                                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Date du chèque</label>
                            <input type="date" name="cheque_date" value="{{ old('cheque_date') }}"
                                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        </div>
                    </div>
                </div>

                {{-- Virement --}}
                <div id="bank_transfer_fields" class="mt-5 hidden rounded-2xl border border-gray-200 bg-gray-50 p-5">
                    <h3 class="text-sm font-bold text-gray-900">
                        Détails du virement bancaire
                    </h3>

                    <div class="mt-4 grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Référence virement</label>
                            <input type="text" name="bank_transfer_reference" value="{{ old('bank_transfer_reference') }}"
                                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Banque</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        </div>
                    </div>
                </div>

                {{-- TPE --}}
                <div id="tpe_fields" class="mt-5 hidden rounded-2xl border border-gray-200 bg-gray-50 p-5">
                    <h3 class="text-sm font-bold text-gray-900">
                        Détails TPE
                    </h3>

                    <div class="mt-4">
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Référence transaction TPE</label>
                        <input type="text" name="tpe_transaction_reference" value="{{ old('tpe_transaction_reference') }}"
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>
                </div>
            </section>

            {{-- Documents --}}
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
                               value="{{ old('receipt_number') }}"
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Fichier reçu
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
                               value="{{ old('invoice_number') }}"
                               class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Fichier facture
                        </label>

                        <input type="file"
                               name="invoice_file"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[#284625] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90">
                    </div>
                </div>
            </section>

            {{-- Notes --}}
            <section>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Notes internes
                </label>

                <textarea name="notes"
                          rows="4"
                          class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                          placeholder="Notes internes...">{{ old('notes') }}</textarea>
            </section>
        </div>

        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ $selectedContract ? route('admin.contracts.show', $selectedContract) : route('admin.payments.index') }}"
               class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-300 px-5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Annuler
            </a>

            <button type="submit"
                    class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                Créer l’échéance
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