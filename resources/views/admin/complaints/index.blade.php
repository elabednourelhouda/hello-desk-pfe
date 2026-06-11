@extends('layouts.app')

@section('title', 'Réclamations - Administration')

@section('content')
@php
    $statusLabels = [
        'new' => 'Nouvelle',
        'in_progress' => 'En cours',
        'waiting' => 'En attente',
        'resolved' => 'Résolue',
        'closed' => 'Fermée',
        'rejected' => 'Rejetée',
    ];

    $statusClasses = [
        'new' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'in_progress' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'waiting' => 'bg-violet-50 text-violet-700 ring-violet-600/20',
        'resolved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'closed' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
        'rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
    ];

    $priorityClasses = [
        'low' => 'bg-slate-100 text-slate-700',
        'normal' => 'bg-sky-50 text-sky-700',
        'high' => 'bg-amber-50 text-amber-700',
        'urgent' => 'bg-rose-50 text-rose-700',
    ];
@endphp

<div class="min-h-screen bg-[#f6f8f6]">
    <div class="mx-auto max-w-7xl px-6 py-8">

        {{-- Compact header with quick filters --}}
        <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="grid gap-0 lg:grid-cols-[1.35fr_0.65fr]">
                <div class="bg-gradient-to-br from-[#284625] via-[#315f32] to-[#4b7f3f] p-7 text-white">
                    <p class="text-sm font-semibold uppercase tracking-wide text-white/75">
                        Administration
                    </p>

                    <h1 class="mt-3 text-3xl font-bold">
                        Réclamations clients
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                        Consultez les demandes envoyées par les clients, suivez leur priorité
                        et mettez à jour leur état de traitement.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold text-white">
                            Support client
                        </span>

                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold text-white">
                            Suivi des incidents
                        </span>
                    </div>
                </div>

                <div class="bg-white p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">
                                Filtres rapides
                            </h2>

                            <p class="mt-1 text-xs text-gray-400">
                                Cliquez sur une carte pour filtrer.
                            </p>
                        </div>

                        <a href="{{ route('admin.complaints.index') }}"
                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 hover:bg-slate-200">
                            Reset
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('admin.complaints.index', ['status' => 'new']) }}"
                        class="rounded-2xl border p-4 transition hover:-translate-y-0.5 hover:shadow-md
                        {{ request('status') === 'new' ? 'border-sky-500 bg-sky-100 ring-2 ring-sky-200' : 'border-sky-200 bg-sky-50' }}">
                            <p class="text-xs font-bold uppercase text-sky-700">Nouvelles</p>
                            <p class="mt-1 text-2xl font-bold text-sky-900">{{ $stats['new'] }}</p>
                        </a>

                        <a href="{{ route('admin.complaints.index', ['status' => 'in_progress']) }}"
                        class="rounded-2xl border p-4 transition hover:-translate-y-0.5 hover:shadow-md
                        {{ request('status') === 'in_progress' ? 'border-amber-500 bg-amber-100 ring-2 ring-amber-200' : 'border-amber-200 bg-amber-50' }}">
                            <p class="text-xs font-bold uppercase text-amber-700">En cours</p>
                            <p class="mt-1 text-2xl font-bold text-amber-900">{{ $stats['in_progress'] }}</p>
                        </a>

                        <a href="{{ route('admin.complaints.index', ['status' => 'resolved']) }}"
                        class="rounded-2xl border p-4 transition hover:-translate-y-0.5 hover:shadow-md
                        {{ request('status') === 'resolved' ? 'border-emerald-500 bg-emerald-100 ring-2 ring-emerald-200' : 'border-emerald-200 bg-emerald-50' }}">
                            <p class="text-xs font-bold uppercase text-emerald-700">Résolues</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-900">{{ $stats['resolved'] }}</p>
                        </a>

                        <a href="{{ route('admin.complaints.index', ['priority' => 'urgent']) }}"
                        class="rounded-2xl border p-4 transition hover:-translate-y-0.5 hover:shadow-md
                        {{ request('priority') === 'urgent' ? 'border-rose-500 bg-rose-100 ring-2 ring-rose-200' : 'border-rose-200 bg-rose-50' }}">
                            <p class="text-xs font-bold uppercase text-rose-700">Urgentes</p>
                            <p class="mt-1 text-2xl font-bold text-rose-900">{{ $stats['urgent'] }}</p>
                        </a>
                    </div>
                </div>
            </div>
        </section>

{{-- Filter cards --}}
<section class="mt-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">

                <h2 class="text-lg font-bold text-gray-900">
                    Recherche et filtres
                </h2>
            </div>

            <p class="mt-2 text-sm text-gray-500">
                Recherchez par sujet, client ou espace. Vous pouvez aussi filtrer par statut et par type.
            </p>
        </div>

        <a href="{{ route('admin.complaints.index') }}"
        class="text-sm font-bold text-slate-500 transition hover:text-[#284625] hover:underline">
            Réinitialiser
        </a>
    </div>

    <form method="GET"
          action="{{ route('admin.complaints.index') }}"
          class="grid gap-4 lg:grid-cols-[1.5fr_1fr_1fr_auto]">

        @if(request('priority'))
            <input type="hidden" name="priority" value="{{ request('priority') }}">
        @endif

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Recherche
            </label>

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Sujet, client, description, espace..."
                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Statut
            </label>

            <select name="status"
                    class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                <option value="all" @selected(request('status', 'all') === 'all')>Tous</option>
                <option value="new" @selected(request('status') === 'new')>Nouvelle</option>
                <option value="in_progress" @selected(request('status') === 'in_progress')>En cours</option>
                <option value="waiting" @selected(request('status') === 'waiting')>En attente</option>
                <option value="resolved" @selected(request('status') === 'resolved')>Résolue</option>
                <option value="closed" @selected(request('status') === 'closed')>Fermée</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejetée</option>
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Type
            </label>

            <select name="type"
                    class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                <option value="all" @selected(request('type', 'all') === 'all')>Tous</option>
                <option value="material_issue" @selected(request('type') === 'material_issue')>Problème matériel</option>
                <option value="internet_issue" @selected(request('type') === 'internet_issue')>Problème internet</option>
                <option value="air_conditioning" @selected(request('type') === 'air_conditioning')>Climatisation</option>
                <option value="equipment_request" @selected(request('type') === 'equipment_request')>Demande d’équipement</option>
                <option value="reservation_issue" @selected(request('type') === 'reservation_issue')>Problème de réservation</option>
                <option value="other" @selected(request('type') === 'other')>Autre</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit"
                    class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                Filtrer
            </button>
        </div>
    </form>
</section>

        @if(session('success'))
            <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Dossiers de réclamation
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Suivi des demandes envoyées par les clients.
                        </p>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                        {{ $complaints->total() }} résultat(s)
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Client</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Réclamation</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Priorité</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Statut</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($complaints as $complaint)
                            @php
                                $statusClass = $statusClasses[$complaint->status] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';
                                $priorityClass = $priorityClasses[$complaint->priority] ?? 'bg-slate-100 text-slate-700';
                            @endphp

                            <tr class="transition hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900">
                                        {{ $complaint->client?->full_name ?? 'Client supprimé' }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ $complaint->client?->email ?? 'Email non disponible' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-900">
                                        {{ $complaint->subject }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ $complaint->created_at?->format('d/m/Y H:i') }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $complaint->type_label }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $priorityClass }}">
                                        {{ $complaint->priority_label }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">
                                        {{ $complaint->status_label }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.complaints.show', $complaint) }}"
                                       class="inline-flex h-9 items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                                        Voir dossier
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center">
                                    <p class="font-bold text-gray-800">
                                        Aucune réclamation trouvée
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        Les réclamations envoyées par les clients apparaîtront ici.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($complaints->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $complaints->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection