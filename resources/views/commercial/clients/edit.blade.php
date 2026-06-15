@extends('layouts.app')

@section('title', 'Modifier client - Hello Desk')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-5xl px-6 py-8">

        <div class="mb-6">
            <a href="{{ route('commercial.clients.show', $client) }}"
                class="text-sm font-semibold text-[#284625] hover:underline">
                ← Retour au dossier client
            </a>

            <h1 class="mt-4 text-3xl font-bold text-slate-900">
                Modifier le client
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Mettez à jour les informations client et le dossier légal.
            </p>
        </div>

        @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            Veuillez vérifier les informations saisies.
        </div>
        @endif

        <form method="POST"
            action="{{ route('commercial.clients.update', $client) }}"
            class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            @csrf
            @method('PUT')

            <section>
                <h2 class="text-lg font-bold text-slate-900">
                    Informations générales
                </h2>

                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Nom complet <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="full_name" value="{{ old('full_name', $client->full_name) }}" required
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $client->email) }}" required
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Entreprise</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $client->company_name) }}"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Campus principal</label>
                        <select name="main_campus_id"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                            <option value="">Non précisé</option>
                            @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected(old('main_campus_id', $client->main_campus_id) == $campus->id)>
                                {{ $campus->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('main_campus_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Statut</label>
                        <select name="status"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                            <option value="active" @selected(old('status', $client->status) === 'active')>Actif</option>
                            <option value="inactive" @selected(old('status', $client->status) === 'inactive')>Inactif</option>
                        </select>
                    </div>
                </div>
            </section>

            <div class="my-8 border-t border-slate-100"></div>

            <section>
                <h2 class="text-lg font-bold text-slate-900">
                    Type et dossier légal
                </h2>

                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Type client</label>
                        <select name="client_type"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                            <option value="">Non précisé</option>
                            <option value="physique" @selected(old('client_type', $client->client_type) === 'physique')>Personne physique</option>
                            <option value="morale" @selected(old('client_type', $client->client_type) === 'morale')>Personne morale</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Email de facturation</label>
                        <input type="email" name="billing_email" value="{{ old('billing_email', $client->billing_email) }}"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Prénom</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $client->first_name) }}"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Nom</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $client->last_name) }}"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Type pièce d’identité</label>
                        <input type="text" name="identity_document_type" value="{{ old('identity_document_type', $client->identity_document_type) }}"
                            placeholder="CIN, Passeport..."
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Numéro pièce d’identité</label>
                        <input type="text" name="identity_document_number" value="{{ old('identity_document_number', $client->identity_document_number) }}"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Forme juridique</label>
                        <input type="text" name="legal_form" value="{{ old('legal_form', $client->legal_form) }}"
                            placeholder="SARL, SA..."
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">ICE</label>
                        <input type="text" name="ice_number" value="{{ old('ice_number', $client->ice_number) }}"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Représentant légal</label>
                        <input type="text" name="legal_representative_full_name" value="{{ old('legal_representative_full_name', $client->legal_representative_full_name) }}"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Type pièce représentant</label>
                        <input type="text" name="legal_representative_identity_document_type" value="{{ old('legal_representative_identity_document_type', $client->legal_representative_identity_document_type) }}"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Numéro pièce représentant</label>
                        <input type="text" name="legal_representative_identity_document_number" value="{{ old('legal_representative_identity_document_number', $client->legal_representative_identity_document_number) }}"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>
                </div>
            </section>

            <div class="my-8 border-t border-slate-100"></div>

            <section>
                <h2 class="text-lg font-bold text-slate-900">
                    Notes internes
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Ajoutez uniquement les remarques générales liées au client. Les descriptions des documents se gèrent dans les pièces jointes.
                </p>

                <div class="mt-5">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Notes
                    </label>

                    <textarea name="notes"
                        rows="3"
                        placeholder="Ex : préférence du client, remarque commerciale, information importante..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">{{ old('notes', $client->notes) }}</textarea>
                </div>
            </section>

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('commercial.clients.show', $client) }}"
                    class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-300 px-6 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    Annuler
                </a>

                <button type="submit"
                    class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-6 text-sm font-semibold text-white shadow-sm hover:bg-[#1f351d]">
                    Enregistrer
                </button>
            </div>
        </form>
        <div class="mt-6">
            @include('shared.clients._attachments', ['client' => $client, 'mode' => 'manage'])
        </div>
    </div>
</div>
@endsection