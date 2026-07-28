@extends('layouts.app')

@section('title', 'Gestion des commerciaux - Hello Desk')

@section('content')
@php
    $safeTotal = max($totalCount ?? 0, 1);
    $unassignedCount = max(($totalCount ?? 0) - ($assignedCount ?? 0), 0);

    $assignedRate = round((($assignedCount ?? 0) / $safeTotal) * 100);
    $unassignedRate = round(($unassignedCount / $safeTotal) * 100);
@endphp

<div class="min-h-screen bg-[#f6f8f6]">
    <div class="mx-auto max-w-7xl px-6 py-8">

        {{-- Header --}}
        <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="grid gap-0 lg:grid-cols-[1.35fr_0.65fr]">
                <div class="bg-gradient-to-br from-[#284625] via-[#315f32] to-[#4b7f3f] p-8 text-white">
                    <p class="text-sm font-semibold uppercase tracking-wide text-white/75">
                        Administration
                    </p>

                    <h1 class="mt-3 text-3xl font-bold">
                        Gestion des commerciaux
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                        Créez, consultez et suivez les comptes du personnel commercial Hello Desk,
                        ainsi que leurs affectations par site ou étage.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('admin.commercials.create') }}"
                           class="inline-flex h-11 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#284625] shadow-sm transition hover:bg-gray-100">
                            Ajouter un commercial
                        </a>

                        <a href="#commercials-list"
                           class="inline-flex h-11 items-center justify-center rounded-xl border border-white/25 bg-white/10 px-5 text-sm font-bold text-white transition hover:bg-white/15">
                            Voir la liste
                        </a>
                    </div>
                </div>

                {{-- Mini summary --}}
                <div class="bg-white p-8">
                    <h2 class="text-lg font-bold text-gray-900">
                        Affectations du personnel
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Répartition actuelle des commerciaux affectés.
                    </p>

                    <div class="mt-6 space-y-5">
                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Commerciaux affectés</span>
                                <span class="font-bold text-emerald-600">{{ $assignedRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-emerald-500" @style(['width: ' . $assignedRate . '%'])></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-gray-600">Non affectés</span>
                                <span class="font-bold text-sky-600">{{ $unassignedRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-sky-500" @style(['width: ' . $unassignedRate . '%'])></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- KPI cards --}}
        <section class="mt-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Total commerciaux</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $totalCount }}</p>
                    </div>

                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">
                        Équipe
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 w-full rounded-full bg-violet-500"></div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Affectés</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $assignedCount }}</p>
                    </div>

                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                        Assignés
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-emerald-500" @style(['width: ' . $assignedRate . '%'])></div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Non affectés</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $unassignedCount }}</p>
                    </div>

                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">
                        À traiter
                    </span>
                </div>

                <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full bg-sky-500" @style(['width: ' . $unassignedRate . '%'])></div>
                </div>
            </div>
        </section>

        {{-- Search --}}
        <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        Recherche
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Recherchez un commercial par nom ou adresse email.
                    </p>
                </div>

                @if(request('search'))
                    <a href="{{ route('admin.commercials.index') }}#commercials-list"
                       class="text-sm font-bold text-slate-500 transition hover:text-[#284625] hover:underline">
                        Réinitialiser
                    </a>
                @endif
            </div>

            <form method="GET"
                  action="{{ route('admin.commercials.index') }}#commercials-list"
                  class="grid gap-4 lg:grid-cols-[1fr_auto]">

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Recherche
                    </label>

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Nom ou email..."
                           class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                        Rechercher
                    </button>
                </div>
            </form>
        </section>

        {{-- List --}}
        <section id="commercials-list" class="mt-8 scroll-mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Liste des commerciaux
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Consultez les comptes commerciaux et leurs affectations.
                        </p>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                        {{ $commercials->total() }} résultat(s)
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                Commercial
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                Email
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                Affectations
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                Créé le
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                Statut
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wide text-gray-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($commercials as $commercial)
                            <tr class="transition hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-sm font-bold text-violet-700 ring-1 ring-violet-100">
                                            {{ strtoupper(substr($commercial->name, 0, 1)) }}
                                        </div>

                                        <div>
                                            <p class="font-bold text-gray-900">
                                                {{ $commercial->name }}
                                            </p>

                                            <p class="mt-0.5 text-xs text-gray-500">
                                                Personnel commercial
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $commercial->email }}
                                </td>

                                <td class="px-6 py-4">
                                    @if(($commercial->staff_assignments_count ?? 0) > 0)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-600/20">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            {{ $commercial->staff_assignments_count }} affectation(s)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700 ring-1 ring-sky-600/20">
                                            <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                            Non affecté
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ optional($commercial->created_at)->format('d/m/Y') }}
                                </td>

                                <td class="px-6 py-4">
                                    @if($commercial->is_active)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-600/20">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            Actif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700 ring-1 ring-red-600/20">
                                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                            Désactivé
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.commercials.show', $commercial) }}"
                                           class="inline-flex h-9 items-center justify-center rounded-xl bg-sky-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-sky-700">
                                            Voir dossier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl font-bold text-slate-400">
                                            +
                                        </div>

                                        <p class="mt-4 font-bold text-gray-800">
                                            Aucun commercial trouvé
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            Ajoutez un commercial ou modifiez votre recherche.
                                        </p>

                                        <a href="{{ route('admin.commercials.create') }}"
                                           class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                                            Ajouter un commercial
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($commercials->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $commercials->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection