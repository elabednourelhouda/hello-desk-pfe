@extends('layouts.app')

@section('title', 'Ajouter client - Commercial')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-4xl px-6 py-8">

        <div class="mb-6">
            <a href="{{ route('commercial.clients.index') }}"
               class="text-sm font-semibold text-[#284625] hover:underline">
                ← Retour aux clients
            </a>
        </div>

        <div class="mb-8 rounded-3xl bg-[#284625] p-8 text-white shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-white/70">
                Gestion commerciale
            </p>

            <h1 class="mt-2 text-3xl font-bold">
                Ajouter un client
            </h1>

            <p class="mt-3 max-w-2xl text-sm leading-6 text-white/75">
                Créez un client directement lorsqu’il souhaite réserver rapidement un espace
                dans votre périmètre commercial.
            </p>
        </div>

        @if($assignedCampuses->isNotEmpty())
            <div class="mb-6 rounded-xl border border-[#284625]/20 bg-[#284625]/5 px-4 py-3 text-sm text-[#284625]">
                <span class="font-bold">Votre périmètre :</span>
                {{ $assignedCampuses->pluck('name')->join(', ') }}
            </div>
        @else
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Aucune affectation trouvée. Vous ne devriez pas créer un client hors périmètre.
            </div>
        @endif

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
              action="{{ route('commercial.clients.store') }}"
              class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Nom complet <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="full_name"
                           value="{{ old('full_name') }}"
                           required
                           placeholder="Ex: Sara Benali"
                           class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Date d’entrée client
                    </label>
                    <input type="date"
                           name="registered_at"
                           value="{{ old('registered_at', now()->toDateString()) }}"
                           class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           placeholder="Ex: client@email.com"
                           class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Téléphone
                    </label>
                    <input type="text"
                           name="phone"
                           value="{{ old('phone') }}"
                           placeholder="Ex: 06 00 00 00 00"
                           class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Campus principal
                    </label>
                    <select name="main_campus_id"
                            class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Aucun campus</option>

                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected(old('main_campus_id') == $campus->id)>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('main_campus_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @include('admin.clients._legal-fields', ['client' => null])

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Notes
                    </label>
                    <textarea name="notes"
                              rows="5"
                              placeholder="Notes internes sur le client..."
                              class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('commercial.clients.index') }}"
                   class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Annuler
                </a>

                <button type="submit"
                        class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm hover:bg-[#1f351d]">
                    Créer le client
                </button>
            </div>
        </form>
    </div>
</div>
@endsection