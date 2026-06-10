@extends('layouts.app')

@section('title', 'Ajouter une échéance - Hello Desk')

@section('content')
<div class="mx-auto max-w-4xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ $selectedContract ? route('admin.contracts.show', $selectedContract) : route('admin.payments.index') }}"
        class="text-sm font-semibold text-[#284625] hover:underline">
            ← {{ $selectedContract ? 'Retour au contrat' : 'Retour aux paiements' }}
        </a>

        <h1 class="mt-4 text-2xl font-bold text-gray-900">
            Ajouter une échéance
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Créez une échéance de paiement liée à un contrat.
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
          class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf

        @if($selectedContract)
            <input type="hidden" name="redirect_to_contract" value="1">
        @endif

        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Contrat
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
                    Montant à payer <span class="text-red-500">*</span>
                </label>

                <input type="number"
                       step="0.01"
                       min="0"
                       name="amount_due"
                       value="{{ old('amount_due') }}"
                       required
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Montant payé <span class="text-gray-400">(optionnel)</span>
                </label>

                <input type="number"
                       step="0.01"
                       min="0"
                       name="amount_paid"
                       value="{{ old('amount_paid', 0) }}"
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
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

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Méthode de paiement <span class="text-gray-400">(optionnel)</span>
                </label>

                <input type="text"
                       name="payment_method"
                       value="{{ old('payment_method') }}"
                       placeholder="Espèces, virement, chèque..."
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Référence <span class="text-gray-400">(optionnel)</span>
                </label>

                <input type="text"
                       name="reference"
                       value="{{ old('reference') }}"
                       placeholder="Référence interne ou transaction..."
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Notes
            </label>

            <textarea name="notes"
                      rows="4"
                      class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                      placeholder="Notes internes...">{{ old('notes') }}</textarea>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
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
@endsection