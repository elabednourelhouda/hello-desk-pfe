@extends('layouts.app')

@section('title', 'Modifier client - Administration')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-4xl px-6 py-8">

        <div class="mb-6">
            <a href="{{ route('admin.clients.show', $client) }}"
                class="text-sm font-semibold text-[#284625] hover:underline">
                ← Retour au dossier client
            </a>
        </div>

        <div class="mb-8 rounded-3xl bg-[#284625] p-8 text-white shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-white/70">
                Administration
            </p>

            <h1 class="mt-2 text-3xl font-bold">
                Modifier le client
            </h1>

            <p class="mt-3 max-w-2xl text-sm leading-6 text-white/75">
                Mettez à jour les informations du client.
            </p>
        </div>

        @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-bold">Veuillez corriger les erreurs suivantes :</p>

            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST"
            action="{{ route('admin.clients.update', $client) }}"
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid gap-5 md:grid-cols-2">

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Nom complet
                    </label>
                    <input type="text"
                        name="full_name"
                        value="{{ old('full_name', $client->full_name) }}"
                        class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                        required>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Date d’entrée client
                    </label>
                    <input type="date"
                        name="registered_at"
                        value="{{ old('registered_at', $client->registered_at ? $client->registered_at->format('Y-m-d') : now()->toDateString()) }}"
                        class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Email
                    </label>
                    <input type="email"
                        name="email"
                        value="{{ old('email', $client->email) }}"
                        class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                        required>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Téléphone
                    </label>
                    <input type="text"
                        name="phone"
                        value="{{ old('phone', $client->phone) }}"
                        class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Statut
                    </label>
                    <select name="status"
                        class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="active" @selected(old('status', $client->status) === 'active')>
                            Actif
                        </option>
                        <option value="inactive" @selected(old('status', $client->status) === 'inactive')>
                            Inactif
                        </option>
                    </select>
                </div>

                @isset($campuses)
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Site principal
                    </label>
                    <select name="main_campus_id"
                        class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Aucun site</option>

                        @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}"
                            @selected(old('main_campus_id', $client->main_campus_id) == $campus->id)>
                            {{ $campus->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endisset

                @include('admin.clients._legal-fields', ['client' => $client])

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Notes
                    </label>
                    <textarea name="notes"
                        rows="5"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">{{ old('notes', $client->notes) }}</textarea>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.clients.show', $client) }}"
                    class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Annuler
                </a>

                <button type="submit"
                    class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm hover:bg-[#1f351d]">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
        <div class="mt-6">
            @include('shared.clients._attachments', ['client' => $client, 'mode' => 'manage'])
        </div>
    </div>
</div>
@endsection