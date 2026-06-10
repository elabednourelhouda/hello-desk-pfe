@extends('layouts.app')

@section('title', 'Modifier contrat - Hello Desk')

@section('content')
<div class="mx-auto max-w-4xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.contracts.show', $contract) }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Retour au contrat
        </a>

        <h1 class="mt-4 text-2xl font-bold text-gray-900">
            Modifier le contrat / importer le PDF signé
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Importez la version signée du contrat et mettez à jour son statut.
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

    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.contracts.update', $contract) }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="mb-6 rounded-2xl bg-[#284625]/5 p-5">
            <p class="text-xs font-bold uppercase tracking-wide text-[#284625]">
                Réservation liée
            </p>

            <h2 class="mt-2 text-lg font-bold text-gray-900">
                {{ $contract->reservation?->space?->name ?? 'Espace non précisé' }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Client : {{ $contract->client?->full_name ?? 'Client supprimé' }}
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Titre du contrat
                </label>

                <input type="text"
                       name="title"
                       value="{{ old('title', $contract->title) }}"
                       required
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Date début
                </label>

                <input type="date"
                       name="start_date"
                       value="{{ old('start_date', $contract->start_date?->format('Y-m-d')) }}"
                       required
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Date fin
                </label>

                <input type="date"
                       name="end_date"
                       value="{{ old('end_date', $contract->end_date?->format('Y-m-d')) }}"
                       required
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Statut
                </label>

                <select name="status"
                        required
                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <option value="draft" @selected(old('status', $contract->status) === 'draft')>
                        Brouillon
                    </option>

                    <option value="active" @selected(old('status', $contract->status) === 'active')>
                        Actif
                    </option>

                    <option value="expired" @selected(old('status', $contract->status) === 'expired')>
                        Expiré
                    </option>

                    <option value="cancelled" @selected(old('status', $contract->status) === 'cancelled')>
                        Annulé
                    </option>
                </select>

                <p class="mt-2 text-xs text-gray-500">
                    Si le contrat devient actif, la réservation devient confirmée.
                </p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    PDF signé du contrat
                </label>

                <input type="file"
                       name="pdf_file"
                       accept="application/pdf"
                       class="block h-12 w-full rounded-xl border border-gray-300 px-4 py-2 text-sm shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[#284625] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90">

                @if($contract->pdf_path)
                    <p class="mt-2 text-xs text-green-700">
                        Un PDF signé est déjà importé. Importer un nouveau fichier remplacera l’ancien.
                    </p>
                @else
                    <p class="mt-2 text-xs text-gray-500">
                        Aucun PDF signé importé pour le moment.
                    </p>
                @endif
            </div>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Notes
            </label>

            <textarea name="notes"
                      rows="4"
                      class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                      placeholder="Notes internes sur le contrat...">{{ old('notes', $contract->notes) }}</textarea>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.contracts.show', $contract) }}"
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