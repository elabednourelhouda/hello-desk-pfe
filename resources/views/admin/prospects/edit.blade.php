@extends('layouts.app')

@section('title', 'Modifier un prospect - Administration')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-5xl px-6 py-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('admin.prospects.index') }}"
                class="text-sm font-semibold text-[#284625] hover:underline">
                    ← Retour aux prospects
                </a>

                <h1 class="mt-4 text-3xl font-bold text-gray-900">
                    Modifier un prospect
                </h1>

                <p class="mt-2 text-sm text-gray-500">
                    Mettez à jour les informations du prospect.
                </p>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3">
                <a href="{{ route('admin.prospects.index') }}"
                class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-300 bg-white px-5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                    Annuler
                </a>

                <button type="submit"
                        form="admin-prospect-edit-form"
                        class="inline-flex h-11 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                    Enregistrer les modifications
                </button>
            </div>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                Veuillez vérifier les informations saisies.
            </div>
        @endif

        {{-- Form card --}}
        <form id="admin-prospect-edit-form"
            method="POST"
            action="{{ route('admin.prospects.update', $prospect) }}"
            class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
        @csrf
        @method('PUT')

            {{-- Section 1 --}}
            <div class="mb-8">
                <h2 class="mb-4 text-lg font-bold text-gray-900">
                    Informations personnelles
                </h2>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Nom complet <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                            name="full_name"
                            value="{{ old('full_name', $prospect->full_name) }}"
                            required
                            placeholder="Ex: Sara Benali"
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

                        @error('full_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Date d’entrée CRM
                        </label>
                        <input type="date"
                            name="registered_at"
                            value="{{ old('registered_at', $prospect->registered_at ? \Carbon\Carbon::parse($prospect->registered_at)->format('Y-m-d') : now()->toDateString()) }}"
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

                        @error('registered_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Entreprise
                        </label>
                        <input type="text"
                            name="company_name"
                            value="{{ old('company_name', $prospect->company_name) }}"
                            placeholder="Ex: Atlas Consulting"
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Téléphone
                        </label>
                        <input type="text"
                            name="phone"
                            value="{{ old('phone', $prospect->phone) }}"
                            placeholder="Ex: 06 00 00 00 00"
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Email
                        </label>
                        <input type="email"
                            name="email"
                            value="{{ old('email', $prospect->email) }}"
                            placeholder="Ex: client@email.com"
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @include('shared.prospects._identity_form', ['prospect' => $prospect])
                </div>
            </div>

            {{-- Divider --}}
            <div class="my-8 border-t border-gray-100"></div>

            @include('shared.prospects._need_form', ['prospect' => $prospect])

            {{-- Divider --}}
            <div class="my-8 border-t border-gray-100"></div>

            {{-- Section 3 --}}
            <div class="mb-8">
                <h2 class="mb-4 text-lg font-bold text-gray-900">
                    Suivi CRM
                </h2>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Statut CRM
                        </label>
                        <select name="crm_status"
                                class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('crm_status', $prospect->crm_status) === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Commercial responsable
                        </label>
                        <select name="assigned_to"
                                class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                            <option value="">Non affecté</option>
                            @foreach($commercials as $commercial)
                                <option value="{{ $commercial->id }}" @selected(old('assigned_to', $prospect->assigned_to) == $commercial->id)>
                                    {{ $commercial->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-5">
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Notes commerciales
                    </label>
                    <textarea name="notes"
                              rows="4"
                              placeholder="Ex: Le prospect souhaite être rappelé cette semaine..."
                              class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">{{ old('notes', $prospect->notes) }}</textarea>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.prospects.index') }}"
                   class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-300 px-6 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                    Annuler
                </a>

                <button type="submit"
                        class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection