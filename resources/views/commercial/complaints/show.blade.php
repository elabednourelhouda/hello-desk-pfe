@extends('layouts.app')

@section('title', 'Détail réclamation - Espace commercial')

@section('content')
@php
    $statusClasses = [
        'new' => 'bg-sky-50 text-sky-700 border-sky-200',
        'in_progress' => 'bg-amber-50 text-amber-700 border-amber-200',
        'waiting' => 'bg-violet-50 text-violet-700 border-violet-200',
        'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'closed' => 'bg-slate-100 text-slate-700 border-slate-200',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
    ];

    $priorityClasses = [
        'low' => 'bg-slate-100 text-slate-700',
        'normal' => 'bg-sky-50 text-sky-700',
        'high' => 'bg-amber-50 text-amber-700',
        'urgent' => 'bg-rose-50 text-rose-700',
    ];

    $statusClass = $statusClasses[$complaint->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    $priorityClass = $priorityClasses[$complaint->priority] ?? 'bg-slate-100 text-slate-700';
@endphp

<div class="min-h-screen bg-[#f6f8f6]">
    <div class="mx-auto max-w-6xl px-6 py-8">

        <div class="mb-6">
            <a href="{{ route('commercial.complaints.index') }}"
               class="text-sm font-semibold text-[#284625] hover:underline">
                ← Retour aux réclamations
            </a>
        </div>

        <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="grid gap-0 lg:grid-cols-[1.3fr_0.7fr]">
                <div class="bg-gradient-to-br from-[#284625] via-[#315f32] to-[#4b7f3f] p-7 text-white">
                    <p class="text-sm font-semibold uppercase tracking-wide text-white/75">
                        Espace commercial
                    </p>

                    <h1 class="mt-3 text-3xl font-bold">
                        Détail de la réclamation
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                        Consultez la demande du client et mettez à jour son état de traitement.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold text-white">
                            {{ $complaint->type_label }}
                        </span>

                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold text-white">
                            Créée le {{ $complaint->created_at?->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>

                <div class="bg-white p-7">
                    <div class="grid gap-3">
                        <div class="rounded-2xl border {{ $statusClass }} p-4">
                            <p class="text-xs font-bold uppercase tracking-wide opacity-70">
                                Statut
                            </p>

                            <p class="mt-2 text-2xl font-bold">
                                {{ $complaint->status_label }}
                            </p>
                        </div>

                        <div class="rounded-2xl {{ $priorityClass }} p-4">
                            <p class="text-xs font-bold uppercase tracking-wide opacity-70">
                                Priorité
                            </p>

                            <p class="mt-2 text-2xl font-bold">
                                {{ $complaint->priority_label }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if(session('success'))
            <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">Veuillez corriger les erreurs suivantes :</p>

                <ul class="mt-2 list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <section class="space-y-6 lg:col-span-2">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#284625]">
                        Sujet
                    </p>

                    <h2 class="mt-2 text-2xl font-bold text-gray-900">
                        {{ $complaint->subject }}
                    </h2>

                    <div class="mt-5 rounded-2xl bg-slate-50 p-5">
                        <p class="text-sm font-bold text-gray-900">
                            Description du client
                        </p>

                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-600">
                            {{ $complaint->description ?: 'Aucune description détaillée fournie.' }}
                        </p>
                    </div>

                    @if($complaint->admin_response)
                        <div class="mt-5 rounded-2xl border border-[#284625]/20 bg-[#284625]/5 p-5">
                            <p class="text-sm font-bold text-[#284625]">
                                Réponse / traitement
                            </p>

                            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700">
                                {{ $complaint->admin_response }}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">
                        Traiter la réclamation
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Mettez à jour le statut, la priorité et la réponse visible dans le dossier.
                    </p>

                    <form method="POST"
                          action="{{ route('commercial.complaints.update', $complaint) }}"
                          class="mt-5 space-y-5">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Statut <span class="text-red-500">*</span>
                                </label>

                                <select name="status"
                                        required
                                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                                    <option value="new" @selected(old('status', $complaint->status) === 'new')>Nouvelle</option>
                                    <option value="in_progress" @selected(old('status', $complaint->status) === 'in_progress')>En cours</option>
                                    <option value="waiting" @selected(old('status', $complaint->status) === 'waiting')>En attente</option>
                                    <option value="resolved" @selected(old('status', $complaint->status) === 'resolved')>Résolue</option>
                                    <option value="closed" @selected(old('status', $complaint->status) === 'closed')>Fermée</option>
                                    <option value="rejected" @selected(old('status', $complaint->status) === 'rejected')>Rejetée</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Priorité <span class="text-red-500">*</span>
                                </label>

                                <select name="priority"
                                        required
                                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                                    <option value="low" @selected(old('priority', $complaint->priority) === 'low')>Faible</option>
                                    <option value="normal" @selected(old('priority', $complaint->priority) === 'normal')>Normale</option>
                                    <option value="high" @selected(old('priority', $complaint->priority) === 'high')>Élevée</option>
                                    <option value="urgent" @selected(old('priority', $complaint->priority) === 'urgent')>Urgente</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                Réponse / note de traitement
                            </label>

                            <textarea name="admin_response"
                                      rows="5"
                                      class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                                      placeholder="Ex : Réclamation prise en charge, intervention programmée...">{{ old('admin_response', $complaint->admin_response) }}</textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                                Enregistrer le traitement
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <aside class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">
                        Client
                    </h2>

                    <div class="mt-5 space-y-4 text-sm">
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Nom</p>
                            <p class="mt-1 font-semibold text-gray-900">
                                {{ $complaint->client?->full_name ?? 'Client supprimé' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Email</p>
                            <p class="mt-1 text-gray-700">
                                {{ $complaint->client?->email ?? 'Non disponible' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Téléphone</p>
                            <p class="mt-1 text-gray-700">
                                {{ $complaint->client?->phone ?? 'Non disponible' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">
                        Réservation liée
                    </h2>

                    <div class="mt-5 space-y-4 text-sm">
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Espace</p>
                            <p class="mt-1 text-gray-700">
                                {{ $complaint->reservation?->space?->name ?? 'Non précisé' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Campus / étage</p>
                            <p class="mt-1 text-gray-700">
                                {{ $complaint->reservation?->campus?->name ?? 'Non précisé' }}
                                —
                                {{ $complaint->reservation?->floor?->name ?? 'Non précisé' }}
                            </p>
                        </div>

                        @if($complaint->reservation)
                            <a href="{{ route('commercial.reservations.show', $complaint->reservation) }}"
                               class="inline-flex w-full justify-center rounded-xl border border-[#284625] bg-white px-4 py-2 text-sm font-semibold text-[#284625] hover:bg-gray-50">
                                Voir la réservation
                            </a>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">
                        Contrat lié
                    </h2>

                    <div class="mt-5 space-y-4 text-sm">
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Contrat</p>
                            <p class="mt-1 text-gray-700">
                                {{ $complaint->contract?->title ?? 'Non précisé' }}
                            </p>
                        </div>

                        @if($complaint->contract)
                            <a href="{{ route('commercial.contracts.show', $complaint->contract) }}"
                               class="inline-flex w-full justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Voir le contrat
                            </a>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">
                        Dates
                    </h2>

                    <div class="mt-5 space-y-4 text-sm">
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Créée le</p>
                            <p class="mt-1 text-gray-700">
                                {{ $complaint->created_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Résolue le</p>
                            <p class="mt-1 text-gray-700">
                                {{ $complaint->resolved_at?->format('d/m/Y H:i') ?? 'Non résolue' }}
                            </p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection