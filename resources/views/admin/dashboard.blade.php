@extends('layouts.app')

@section('title', 'Tableau de bord administrateur - Hello Desk')

@section('content')
@php
    $safeProspects = max($prospectsCount, 1);

    $conversionRate = $conversionRate ?? round(($convertedProspectsCount / $safeProspects) * 100);
    $lostRate = $lostRate ?? round(($lostProspectsCount / $safeProspects) * 100);

    $activeProspectsCount = max($prospectsCount - $convertedProspectsCount - $lostProspectsCount, 0);
    $activeRate = max(0, 100 - $conversionRate - $lostRate);

    $clientsVisualRate = $clientsVisualRate ?? min(100, round(($clientsCount / $safeProspects) * 100));
    $commercialsVisualRate = $commercialsVisualRate ?? min(100, round(($commercialsCount / max($commercialsCount, $prospectsCount, 1)) * 100));
@endphp

<div class="mx-auto max-w-7xl px-6 py-8">

    {{-- Header --}}
    <section class="overflow-hidden rounded-3xl border border-[#284625]/20 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-[1.4fr_0.8fr]">
            <div class="bg-gradient-to-br from-[#284625] via-[#315f32] to-[#4b7f3f] p-8 text-white">
                <p class="text-sm font-semibold uppercase tracking-wide text-white/75">
                    Administration
                </p>

                <h1 class="mt-3 text-3xl font-bold">
                    Tableau de bord
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                    Vue globale de l’activité Hello Desk : prospects, clients, commerciaux, réservations,
                    contrats et suivi opérationnel.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('admin.prospects.create') }}"
                       class="inline-flex h-11 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#284625] shadow-sm transition hover:bg-gray-100">
                        Ajouter un prospect
                    </a>

                    <a href="{{ route('admin.prospects.index') }}"
                       class="inline-flex h-11 items-center justify-center rounded-xl border border-white/25 bg-white/10 px-5 text-sm font-bold text-white transition hover:bg-white/15">
                        Gérer les prospects
                    </a>
                </div>
            </div>

            {{-- Mini executive summary --}}
            <div class="bg-white p-8">
                <h2 class="text-lg font-bold text-gray-900">
                    Résumé exécutif
                </h2>

                <div class="mt-5 space-y-4">
                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-semibold text-gray-600">Conversion prospects</span>
                            <span class="font-bold text-[#284625]">{{ $conversionRate }}%</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-[#284625]" @style(['width: ' . $conversionRate . '%'])></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-semibold text-gray-600">Prospects actifs</span>
                            <span class="font-bold text-blue-600">{{ $activeRate }}%</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-blue-500" @style(['width: ' . $activeRate . '%'])></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-semibold text-gray-600">Prospects perdus</span>
                            <span class="font-bold text-rose-600">{{ $lostRate }}%</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-rose-500" @style(['width: ' . $lostRate . '%'])></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Main indicators --}}
    <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.prospects.index') }}"
           class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
            <p class="text-sm font-semibold text-gray-500">Prospects</p>
            <div class="mt-4 flex items-end justify-between">
                <p class="text-4xl font-bold text-gray-900">{{ $prospectsCount }}</p>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                    CRM
                </span>
            </div>
            <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                <div class="h-1.5 rounded-full bg-emerald-500" style="width: 100%"></div>
            </div>
        </a>

        <a href="{{ route('admin.clients.index') }}"
           class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
            <p class="text-sm font-semibold text-gray-500">Clients</p>
            <div class="mt-4 flex items-end justify-between">
                <p class="text-4xl font-bold text-gray-900">{{ $clientsCount }}</p>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                    Comptes
                </span>
            </div>
            <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                <div class="h-1.5 rounded-full bg-blue-500" @style(['width: ' . $clientsVisualRate . '%'])></div>
            </div>
        </a>

        <a href="{{ route('admin.commercials.index') }}"
           class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
            <p class="text-sm font-semibold text-gray-500">Commerciaux</p>
            <div class="mt-4 flex items-end justify-between">
                <p class="text-4xl font-bold text-gray-900">{{ $commercialsCount }}</p>
                <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">
                    Équipe
                </span>
            </div>
            <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                <div class="h-1.5 rounded-full bg-violet-500" @style(['width: ' . $commercialsVisualRate . '%'])></div>
            </div>
        </a>

        <a href="{{ Route::has('admin.reservations.index') ? route('admin.reservations.index') : '#' }}"
           class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
            <p class="text-sm font-semibold text-gray-500">Réservations</p>
            <div class="mt-4 flex items-end justify-between">
                <p class="text-4xl font-bold text-gray-900">{{ $reservationsCount }}</p>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                    Planning
                </span>
            </div>
            <div class="mt-5 h-1.5 rounded-full bg-gray-100">
                <div class="h-1.5 rounded-full bg-amber-500" @style(['width: ' . ($reservationsCount > 0 ? 60 : 0) . '%'])></div>
            </div>
        </a>
    </section>

    {{-- Main dashboard area --}}
    <div class="mt-8 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">

        {{-- CRM analytics --}}
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Analyse CRM</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Répartition des prospects selon leur avancement.
                    </p>
                </div>

                <a href="{{ route('admin.prospects.index') }}"
                   class="text-sm font-bold text-[#284625] hover:underline">
                    Voir les prospects
                </a>
            </div>

            {{-- Horizontal stacked visual --}}
            <div class="mt-6">
                <div class="mb-3 flex flex-wrap gap-4 text-xs font-bold">
                    <span class="inline-flex items-center gap-2 text-[#284625]">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#284625]"></span>
                        Convertis
                    </span>
                    <span class="inline-flex items-center gap-2 text-blue-600">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                        Actifs
                    </span>
                    <span class="inline-flex items-center gap-2 text-rose-600">
                        <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                        Perdus
                    </span>
                </div>

                <div class="flex h-5 overflow-hidden rounded-full bg-gray-100">
                    <div class="bg-[#284625]" @style(['width: ' . $conversionRate . '%'])></div>
                    <div class="bg-blue-500" @style(['width: ' . $activeRate . '%'])></div>
                    <div class="bg-rose-500" @style(['width: ' . $lostRate . '%'])></div>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-[#284625]/5 p-5">
                    <p class="text-sm font-semibold text-[#284625]">Convertis</p>
                    <p class="mt-3 text-3xl font-bold text-gray-900">{{ $convertedProspectsCount }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $conversionRate }}% du pipeline</p>
                </div>

                <div class="rounded-2xl bg-blue-50 p-5">
                    <p class="text-sm font-semibold text-blue-700">En cours</p>
                    <p class="mt-3 text-3xl font-bold text-gray-900">{{ $activeProspectsCount }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $activeRate }}% du pipeline</p>
                </div>

                <div class="rounded-2xl bg-rose-50 p-5">
                    <p class="text-sm font-semibold text-rose-700">Perdus</p>
                    <p class="mt-3 text-3xl font-bold text-gray-900">{{ $lostProspectsCount }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $lostRate }}% du pipeline</p>
                </div>
            </div>

            {{-- Visual bars --}}
            <div class="mt-6 rounded-2xl border border-gray-100 bg-gray-50 p-5">
                <h3 class="text-sm font-bold text-gray-900">Comparaison opérationnelle</h3>

                <div class="mt-5 space-y-5">
                    <div>
                        <div class="mb-2 flex justify-between text-sm">
                            <span class="font-medium text-gray-700">Prospects</span>
                            <span class="font-bold text-gray-900">{{ $prospectsCount }}</span>
                        </div>
                        <div class="h-3 rounded-full bg-white">
                            <div class="h-3 rounded-full bg-emerald-500" @style(['width: 100%'])></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex justify-between text-sm">
                            <span class="font-medium text-gray-700">Clients</span>
                            <span class="font-bold text-gray-900">{{ $clientsCount }}</span>
                        </div>
                        <div class="h-3 rounded-full bg-white">
                            <div class="h-3 rounded-full bg-blue-500" @style(['width: ' . $clientsVisualRate . '%'])></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex justify-between text-sm">
                            <span class="font-medium text-gray-700">Commerciaux</span>
                            <span class="font-bold text-gray-900">{{ $commercialsCount }}</span>
                        </div>
                        <div class="h-3 rounded-full bg-white">
                            <div class="h-3 rounded-full bg-violet-500" @style(['width: ' . $commercialsVisualRate . '%'])></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex justify-between text-sm">
                            <span class="font-medium text-gray-700">Réservations</span>
                            <span class="font-bold text-gray-900">{{ $reservationsCount }}</span>
                        </div>
                        <div class="h-3 rounded-full bg-white">
                            <div class="h-3 rounded-full bg-amber-500" @style(['width: ' . ($reservationsCount > 0 ? 55 : 0) . '%'])></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Right column --}}
        <aside class="space-y-6">

            {{-- Quick actions --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Actions rapides</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Accès direct aux tâches fréquentes.
                </p>

                <div class="mt-5 space-y-3">
                    <a href="{{ route('admin.prospects.create') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 transition hover:border-[#284625]/40 hover:bg-[#284625]/5">
                        <span class="text-sm font-bold text-gray-800">Ajouter prospect</span>
                        <span class="text-sm font-bold text-[#284625]">→</span>
                    </a>

                    <a href="{{ route('admin.clients.create') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 transition hover:border-blue-200 hover:bg-blue-50">
                        <span class="text-sm font-bold text-gray-800">Ajouter client</span>
                        <span class="text-sm font-bold text-blue-600">→</span>
                    </a>

                    <a href="{{ route('admin.commercials.create') }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 transition hover:border-violet-200 hover:bg-violet-50">
                        <span class="text-sm font-bold text-gray-800">Ajouter commercial</span>
                        <span class="text-sm font-bold text-violet-600">→</span>
                    </a>

                    <a href="{{ Route::has('admin.interactive-map.index') ? route('admin.interactive-map.index') : '#' }}"
                       class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 transition hover:border-emerald-200 hover:bg-emerald-50">
                        <span class="text-sm font-bold text-gray-800">Carte interactive</span>
                        <span class="text-sm font-bold text-emerald-600">→</span>
                    </a>
                </div>
            </section>

            {{-- Modules --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Modules clés</h2>
                <p class="mt-1 text-sm text-gray-500">
                    État fonctionnel des modules principaux.
                </p>

                <div class="mt-5 space-y-4">
                    <a href="{{ Route::has('admin.interactive-map.index') ? route('admin.interactive-map.index') : '#' }}"
                       class="block rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="font-bold text-emerald-800">Carte interactive</p>
                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-emerald-700">
                                Actif
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-emerald-700">
                            Visualisation des espaces par site et étage.
                        </p>
                    </a>

                    <a href="{{ Route::has('admin.contracts.index') ? route('admin.contracts.index') : '#' }}"
                       class="block rounded-2xl border border-indigo-100 bg-indigo-50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="font-bold text-indigo-800">Contrats</p>
                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-indigo-700">
                                Suivi
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-indigo-700">
                            Documents et contrats liés aux réservations.
                        </p>
                    </a>

                    <a href="{{ Route::has('admin.payments.index') ? route('admin.payments.index') : '#' }}"
                       class="block rounded-2xl border border-orange-100 bg-orange-50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="font-bold text-orange-800">Paiements</p>
                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-orange-700">
                                Alertes
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-orange-700">
                            Échéances, paiements et relances.
                        </p>
                    </a>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection