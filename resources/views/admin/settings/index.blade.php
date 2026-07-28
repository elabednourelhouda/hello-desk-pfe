@extends('layouts.app')

@section('title', 'Configuration - Administration')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-7xl px-6 py-8">

        <div class="mb-8">
            <p class="text-sm font-medium text-[#284625]">Administration</p>
            <h1 class="mt-1 text-3xl font-bold text-gray-900">Configuration</h1>
            <p class="mt-2 text-sm text-gray-500">
                Gérez ici les listes utilisées dans les formulaires de l’application
                (types d’espaces, sites, etc.). Ajouter, modifier ou désactiver une valeur
                ne nécessite plus de changement de code.
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">

            <a href="{{ route('admin.settings.space-types.index') }}"
                class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#284625]/40 hover:shadow-md">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#284625]/10 text-xl font-bold text-[#284625]">
                    ⬛
                </div>

                <h2 class="mt-4 text-lg font-bold text-gray-900">
                    Types d’espaces
                </h2>

                <p class="mt-2 text-sm leading-6 text-gray-500">
                    Bureau, Co-working, Salle de formation... Gérez la liste des types
                    d’espaces disponibles pour les espaces et les prospects.
                </p>

                <span class="mt-4 inline-flex items-center gap-1 text-sm font-bold text-[#284625]">
                    Gérer
                    <span class="transition group-hover:translate-x-1">→</span>
                </span>
            </a>

            <a href="{{ route('admin.settings.sites.index') }}"
                class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#284625]/40 hover:shadow-md">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#284625]/10 text-xl font-bold text-[#284625]">
                    📍
                </div>

                <h2 class="mt-4 text-lg font-bold text-gray-900">
                    Sites
                </h2>

                <p class="mt-2 text-sm leading-6 text-gray-500">
                    Centre Ville (A), Sidi Maarouf (B)... Gérez la liste des sites
                    disponibles pour les espaces, prospects et clients.
                </p>

                <span class="mt-4 inline-flex items-center gap-1 text-sm font-bold text-[#284625]">
                    Gérer
                    <span class="transition group-hover:translate-x-1">→</span>
                </span>
            </a>

            <a href="{{ route('admin.settings.prospect-sources.index') }}"
                class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#284625]/40 hover:shadow-md">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#284625]/10 text-xl font-bold text-[#284625]">
                    📣
                </div>

                <h2 class="mt-4 text-lg font-bold text-gray-900">
                    Sources de prospects
                </h2>

                <p class="mt-2 text-sm leading-6 text-gray-500">
                    Site web, WhatsApp, Recommandation... Gérez la liste des sources
                    utilisées dans le formulaire de création d’un prospect.
                </p>

                <span class="mt-4 inline-flex items-center gap-1 text-sm font-bold text-[#284625]">
                    Gérer
                    <span class="transition group-hover:translate-x-1">→</span>
                </span>
            </a>

            <a href="{{ route('admin.settings.activity-sectors.index') }}"
                class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#284625]/40 hover:shadow-md">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#284625]/10 text-xl font-bold text-[#284625]">
                    🏷️
                </div>
                <h2 class="mt-4 text-lg font-bold text-gray-900">
                    Secteurs d’activité
                </h2>
                <p class="mt-2 text-sm leading-6 text-gray-500">
                    Technologie, Finance, Commerce... Gérez la liste des secteurs
                    d’activité utilisés dans le formulaire de création d’un prospect.
                </p>
                <span class="mt-4 inline-flex items-center gap-1 text-sm font-bold text-[#284625]">
                    Gérer
                    <span class="transition group-hover:translate-x-1">→</span>
                </span>
            </a>

            <a href="{{ route('admin.settings.contact-types.index') }}"
                class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#284625]/40 hover:shadow-md">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#284625]/10 text-xl font-bold text-[#284625]">
                    ☎️
                </div>
                <h2 class="mt-4 text-lg font-bold text-gray-900">
                    Types de contact
                </h2>
                <p class="mt-2 text-sm leading-6 text-gray-500">
                    Appel, WhatsApp, Email... Gérez la liste des types de contact
                    utilisés dans le formulaire « Ajouter un suivi » du CRM.
                </p>
                <span class="mt-4 inline-flex items-center gap-1 text-sm font-bold text-[#284625]">
                    Gérer
                    <span class="transition group-hover:translate-x-1">→</span>
                </span>
            </a>

            <a href="{{ route('admin.settings.space-statuses.index') }}"
                class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#284625]/40 hover:shadow-md">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#284625]/10 text-xl font-bold text-[#284625]">
                    🚦
                </div>
                <h2 class="mt-4 text-lg font-bold text-gray-900">
                    Statuts d’espace
                </h2>
                <p class="mt-2 text-sm leading-6 text-gray-500">
                    Disponible, Occupé, Indisponible, Maintenance... Gérez les statuts
                    assignables manuellement à un espace. « Réservé » reste automatique.
                </p>
                <span class="mt-4 inline-flex items-center gap-1 text-sm font-bold text-[#284625]">
                    Gérer
                    <span class="transition group-hover:translate-x-1">→</span>
                </span>
            </a>

            <a href="{{ route('admin.settings.reservation-duration-types.index') }}"
                class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#284625]/40 hover:shadow-md">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#284625]/10 text-xl font-bold text-[#284625]">
                    ⏱️
                </div>
                <h2 class="mt-4 text-lg font-bold text-gray-900">
                    Types de durée de réservation
                </h2>
                <p class="mt-2 text-sm leading-6 text-gray-500">
                    À l’heure, à la journée, au mois, personnalisé... Gérez la liste des types de durée
                    utilisés dans le formulaire de réservation et le champ « Période souhaitée » des prospects.
                </p>
                <span class="mt-4 inline-flex items-center gap-1 text-sm font-bold text-[#284625]">
                    Gérer
                    <span class="transition group-hover:translate-x-1">→</span>
                </span>
            </a>

            {{--
                FUTURE CONFIGURATION CARDS

                - Listes déroulantes génériques : origine du prospect,
                  secteur d'activité... (admin.settings.dropdown-options.index)
            --}}

        </div>
    </div>
</div>
@endsection