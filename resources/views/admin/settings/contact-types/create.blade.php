@extends('layouts.app')

@section('title', 'Ajouter un type de contact - Administration')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-2xl px-6 py-8">

        <p class="text-sm font-medium text-[#284625]">
            <a href="{{ route('admin.settings.index') }}" class="hover:underline">Configuration</a>
            /
            <a href="{{ route('admin.settings.contact-types.index') }}" class="hover:underline">Types de contact</a>
        </p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Ajouter un type de contact</h1>

        <form method="POST"
              action="{{ route('admin.settings.contact-types.store') }}"
              class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            @csrf

            <div class="grid gap-5">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Nom <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           placeholder="Ex: SMS"
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="code"
                           value="{{ old('code') }}"
                           required
                           placeholder="Ex: sms"
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        Identifiant technique (lettres minuscules, chiffres, tirets/underscores).
                        C’est cette valeur qui est réellement enregistrée sur chaque suivi CRM.
                    </p>
                </div>

                <label class="flex items-center gap-3">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-5 w-5 rounded border-gray-300 text-[#284625] focus:ring-[#284625]">
                    <span class="text-sm font-semibold text-gray-700">Actif (visible dans le formulaire « Ajouter un suivi »)</span>
                </label>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.settings.contact-types.index') }}"
                   class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-300 px-6 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                    Annuler
                </a>

                <button type="submit"
                        class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                    Créer le type
                </button>
            </div>
        </form>
    </div>
</div>
@endsection