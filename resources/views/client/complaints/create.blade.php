@extends('layouts.app')

@section('title', 'Nouvelle réclamation - Hello Desk')

@section('content')
<div class="mx-auto max-w-4xl px-6 py-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500">Espace client</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Nouvelle réclamation</h1>
            <p class="mt-2 text-sm text-slate-600">
                Décrivez votre problème pour que l’équipe Hello Desk puisse le traiter.
            </p>
        </div>

        <a href="{{ route('client.complaints.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Retour
        </a>
    </div>

    <form method="POST" action="{{ route('client.complaints.store') }}"
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf

        <div class="grid gap-5">
            <div>
                <label for="subject" class="block text-sm font-semibold text-slate-700">
                    Sujet <span class="text-red-500">*</span>
                </label>

                <input type="text"
                       id="subject"
                       name="subject"
                       value="{{ old('subject') }}"
                       class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#284625] focus:outline-none focus:ring-2 focus:ring-[#284625]/10"
                       placeholder="Exemple : Problème de connexion internet">

                @error('subject')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="type" class="block text-sm font-semibold text-slate-700">
                        Type <span class="text-red-500">*</span>
                    </label>

                    <select id="type"
                            name="type"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#284625] focus:outline-none focus:ring-2 focus:ring-[#284625]/10">
                        <option value="material_issue" @selected(old('type') === 'material_issue')>Problème matériel</option>
                        <option value="internet_issue" @selected(old('type') === 'internet_issue')>Problème internet</option>
                        <option value="air_conditioning" @selected(old('type') === 'air_conditioning')>Climatisation</option>
                        <option value="equipment_request" @selected(old('type') === 'equipment_request')>Demande d’équipement</option>
                        <option value="reservation_issue" @selected(old('type') === 'reservation_issue')>Problème de réservation</option>
                        <option value="other" @selected(old('type', 'other') === 'other')>Autre</option>
                    </select>

                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="priority" class="block text-sm font-semibold text-slate-700">
                        Priorité <span class="text-red-500">*</span>
                    </label>

                    <select id="priority"
                            name="priority"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#284625] focus:outline-none focus:ring-2 focus:ring-[#284625]/10">
                        <option value="low" @selected(old('priority') === 'low')>Faible</option>
                        <option value="normal" @selected(old('priority', 'normal') === 'normal')>Normale</option>
                        <option value="high" @selected(old('priority') === 'high')>Élevée</option>
                        <option value="urgent" @selected(old('priority') === 'urgent')>Urgente</option>
                    </select>

                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="reservation_id" class="block text-sm font-semibold text-slate-700">
                        Réservation liée
                    </label>

                    <select id="reservation_id"
                            name="reservation_id"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#284625] focus:outline-none focus:ring-2 focus:ring-[#284625]/10">
                        <option value="">Aucune réservation</option>

                        @foreach($reservations as $reservation)
                            <option value="{{ $reservation->id }}" @selected(old('reservation_id') == $reservation->id)>
                                #{{ $reservation->id }} - {{ $reservation->space->name ?? 'Espace' }}
                            </option>
                        @endforeach
                    </select>

                    @error('reservation_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="contract_id" class="block text-sm font-semibold text-slate-700">
                        Contrat lié
                    </label>

                    <select id="contract_id"
                            name="contract_id"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#284625] focus:outline-none focus:ring-2 focus:ring-[#284625]/10">
                        <option value="">Aucun contrat</option>

                        @foreach($contracts as $contract)
                            <option value="{{ $contract->id }}" @selected(old('contract_id') == $contract->id)>
                                Contrat #{{ $contract->id }} - {{ ucfirst($contract->status ?? '—') }}
                            </option>
                        @endforeach
                    </select>

                    @error('contract_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700">
                    Description
                </label>

                <textarea id="description"
                          name="description"
                          rows="6"
                          class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#284625] focus:outline-none focus:ring-2 focus:ring-[#284625]/10"
                          placeholder="Expliquez clairement le problème rencontré...">{{ old('description') }}</textarea>

                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('client.complaints.index') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Annuler
                </a>

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1f351d]">
                    Envoyer la réclamation
                </button>
            </div>
        </div>
    </form>
</div>
@endsection