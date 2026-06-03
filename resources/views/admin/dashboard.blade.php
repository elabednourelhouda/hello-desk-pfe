@extends('layouts.app')

@section('title', 'Tableau de bord administrateur - Hello Desk')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-8">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-[#284625]">
                Administration
            </p>

            <h1 class="mt-1 text-3xl font-bold text-gray-900">
                Tableau de bord
            </h1>

            <p class="mt-2 text-sm text-gray-600">
                Vue globale sur les prospects, clients, commerciaux et réservations Hello Desk.
            </p>
        </div>
    </div>

    {{-- Main navigation buttons --}}
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.prospects.index') }}"
        class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#284625] hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Module</p>
                    <h2 class="mt-2 text-xl font-bold text-gray-900 group-hover:text-[#284625]">
                        Prospects
                    </h2>
                </div>

                <span class="rounded-full bg-[#284625]/10 px-3 py-1 text-sm font-bold text-[#284625]">
                    {{ $prospectsCount }}
                </span>
            </div>

            <p class="mt-4 text-sm text-gray-500">
                Suivi CRM, conversion et gestion des prospects.
            </p>
        </a>

        <a href="{{ route('admin.clients.index') }}"
        class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#284625] hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Module</p>
                    <h2 class="mt-2 text-xl font-bold text-gray-900 group-hover:text-[#284625]">
                        Clients
                    </h2>
                </div>

                <span class="rounded-full bg-[#284625]/10 px-3 py-1 text-sm font-bold text-[#284625]">
                    {{ $clientsCount }}
                </span>
            </div>

            <p class="mt-4 text-sm text-gray-500">
                Comptes clients, accès et informations principales.
            </p>
        </a>

        <a href="{{ route('admin.commercials.index') }}"
        class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#284625] hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Module</p>
                    <h2 class="mt-2 text-xl font-bold text-gray-900 group-hover:text-[#284625]">
                        Commerciaux
                    </h2>
                </div>

                <span class="rounded-full bg-[#284625]/10 px-3 py-1 text-sm font-bold text-[#284625]">
                    {{ $commercialsCount }}
                </span>
            </div>

            <p class="mt-4 text-sm text-gray-500">
                Gestion du personnel et des affectations.
            </p>
        </a>

        <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Module</p>
                    <h2 class="mt-2 text-xl font-bold text-gray-700">
                        Réservations
                    </h2>
                </div>

                <span class="rounded-full bg-gray-200 px-3 py-1 text-sm font-bold text-gray-700">
                    {{ $reservationsCount }}
                </span>
            </div>

            <p class="mt-4 text-sm text-gray-500">
                Réservations des bureaux, salles et positions.
            </p>

            <span class="mt-4 inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                Bientôt
            </span>
        </div>
    </div>

    {{-- Quick access --}}
    <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">
                    Accès rapide
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Actions fréquentes pour gagner du temps.
                </p>
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('admin.prospects.create') }}"
            class="rounded-xl bg-[#284625] px-4 py-3 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-[#20391f]">
                Ajouter prospect
            </a>

            <a href="{{ route('admin.clients.create') }}"
            class="rounded-xl border border-[#284625] bg-white px-4 py-3 text-center text-sm font-semibold text-[#284625] shadow-sm transition hover:bg-[#284625]/5">
                Ajouter client
            </a>

            <a href="{{ route('admin.commercials.create') }}"
            class="rounded-xl border border-[#284625] bg-white px-4 py-3 text-center text-sm font-semibold text-[#284625] shadow-sm transition hover:bg-[#284625]/5">
                Ajouter commercial
            </a>

            <button disabled
                    class="cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-400">
                Créer réservation
            </button>
        </div>
    </section>

    {{-- Stats and visuals --}}
    <div class="mt-8 grid gap-6 lg:grid-cols-3">

        {{-- Stats cards --}}
        <section class="lg:col-span-2 grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Prospects convertis</p>
                <p class="mt-3 text-3xl font-bold text-gray-900">
                    {{ $convertedProspectsCount }}
                </p>

                <div class="mt-5">
                    <div class="mb-2 flex justify-between text-xs font-semibold text-gray-500">
                        <span>Taux de conversion</span>
                        <span>{{ $conversionRate }}%</span>
                    </div>

                    <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-[#284625]"
     @style(['width: ' . $conversionRate . '%'])></div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Prospects perdus</p>
                <p class="mt-3 text-3xl font-bold text-gray-900">
                    {{ $lostProspectsCount }}
                </p>

                <div class="mt-5">
                    <div class="mb-2 flex justify-between text-xs font-semibold text-gray-500">
                        <span>Taux de perte</span>
                        <span>{{ $lostRate }}%</span>
                    </div>

                    <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-red-500"
                            @style(['width: ' . $lostRate . '%'])></div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Clients</p>
                <p class="mt-3 text-3xl font-bold text-gray-900">
                    {{ $clientsCount }}
                </p>
                <p class="mt-2 text-sm text-gray-500">
                    Comptes clients créés dans l’application.
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Commerciaux</p>
                <p class="mt-3 text-3xl font-bold text-gray-900">
                    {{ $commercialsCount }}
                </p>
                <p class="mt-2 text-sm text-gray-500">
                    Personnel commercial enregistré.
                </p>
            </div>
        </section>

        {{-- Visual summary --}}
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">
                Résumé visuel
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Aperçu rapide des données principales.
            </p>

            <div class="mt-6 space-y-5">
                <div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="font-medium text-gray-700">Prospects</span>
                        <span class="font-bold text-gray-900">{{ $prospectsCount }}</span>
                    </div>
                    <div class="h-3 rounded-full bg-gray-100">
                        <div class="h-3 rounded-full bg-[#284625]"
                            @style(['width: 100%'])></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="font-medium text-gray-700">Clients</span>
                        <span class="font-bold text-gray-900">{{ $clientsCount }}</span>
                    </div>
                    <div class="h-3 rounded-full bg-gray-100">
                        <div class="h-3 rounded-full bg-[#284625]/70"
                            @style(['width: ' . $clientsVisualRate . '%'])></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="font-medium text-gray-700">Commerciaux</span>
                        <span class="font-bold text-gray-900">{{ $commercialsCount }}</span>
                    </div>
                    <div class="h-3 rounded-full bg-gray-100">
                        <div class="h-3 rounded-full bg-gray-700"
                            @style(['width: ' . $commercialsVisualRate . '%'])></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="font-medium text-gray-700">Réservations</span>
                        <span class="font-bold text-gray-900">{{ $reservationsCount }}</span>
                    </div>
                    <div class="h-3 rounded-full bg-gray-100">
                        <div class="h-3 rounded-full bg-gray-400"
                            @style(['width: ' . $reservationsVisualRate . '%'])></div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Recent data --}}
    <div class="mt-8 grid gap-6 lg:grid-cols-2">

        {{-- Recent prospects --}}
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        Derniers prospects
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Les derniers prospects ajoutés.
                    </p>
                </div>

                <a href="{{ route('admin.prospects.index') }}"
                   class="text-sm font-semibold text-[#284625] hover:underline">
                    Voir tout
                </a>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($recentProspects as $prospect)
                    <a href="{{ route('admin.prospects.show', $prospect) }}"
                       class="block px-6 py-4 transition hover:bg-gray-50">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ $prospect->full_name ?? $prospect->name ?? 'Prospect' }}
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $prospect->email ?? 'Email non renseigné' }}
                                </p>
                            </div>

                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                {{ $prospect->crm_status ?? 'nouveau' }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-10 text-center text-sm text-gray-500">
                        Aucun prospect pour le moment.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Recent clients --}}
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        Derniers clients
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Les derniers comptes clients créés.
                    </p>
                </div>

                <a href="{{ route('admin.clients.index') }}"
                   class="text-sm font-semibold text-[#284625] hover:underline">
                    Voir tout
                </a>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($recentClients as $client)
                    <a href="{{ route('admin.clients.show', $client) }}"
                       class="block px-6 py-4 transition hover:bg-gray-50">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ $client->full_name ?? $client->name ?? 'Client' }}
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $client->email ?? 'Email non renseigné' }}
                                </p>
                            </div>

                            <span class="text-sm font-semibold text-[#284625]">
                                Voir
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-10 text-center text-sm text-gray-500">
                        Aucun client pour le moment.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection