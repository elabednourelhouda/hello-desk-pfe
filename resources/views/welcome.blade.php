@extends('layouts.app')

@section('title', 'Accueil - Hello Desk')

@section('content')
@php
$objectifs = [
[
'number' => '01',
'title' => 'Centraliser les informations',
'text' => 'Regrouper les espaces, les clients, les réservations et les contrats dans une seule application au lieu de travailler avec des fichiers séparés.',
'accent' => 'from-sky-500 to-cyan-400',
'soft' => 'bg-sky-50 text-sky-700 ring-sky-100',
],
[
'number' => '02',
'title' => 'Améliorer le suivi',
'text' => 'Aider le personnel à suivre les disponibilités, les prospects, les échéances, les réclamations et les actions importantes.',
'accent' => 'from-rose-500 to-pink-400',
'soft' => 'bg-rose-50 text-rose-700 ring-rose-100',
],
[
'number' => '03',
'title' => 'Adapter l’accès à chaque rôle',
'text' => 'Donner à chaque utilisateur un espace adapté à son rôle : administrateur, commercial ou client.',
'accent' => 'from-violet-500 to-fuchsia-400',
'soft' => 'bg-violet-50 text-violet-700 ring-violet-100',
],
];

$modules = [
[
'title' => 'Campus et étages',
'text' => 'Organiser les deux campus Hello Desk, leurs étages et la répartition des espaces.',
'color' => 'bg-sky-50 text-sky-700 ring-sky-100',
'line' => 'from-sky-400 to-cyan-300',
],
[
'title' => 'Espaces coworking',
'text' => 'Gérer les bureaux privés, les salles de réunion et les positions open space.',
'color' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
'line' => 'from-emerald-400 to-teal-300',
],
[
'title' => 'Réservations',
'text' => 'Créer, consulter et suivre les réservations selon les disponibilités des espaces.',
'color' => 'bg-rose-50 text-rose-700 ring-rose-100',
'line' => 'from-rose-400 to-pink-300',
],
[
'title' => 'Prospects et clients',
'text' => 'Suivre les prospects, les visites, les demandes et la conversion en clients.',
'color' => 'bg-violet-50 text-violet-700 ring-violet-100',
'line' => 'from-violet-400 to-fuchsia-300',
],
[
'title' => 'Contrats et paiements',
'text' => 'Associer les réservations aux contrats et suivre les échéances importantes.',
'color' => 'bg-amber-50 text-amber-700 ring-amber-100',
'line' => 'from-amber-400 to-orange-300',
],
[
'title' => 'Réclamations',
'text' => 'Permettre aux clients de déposer une réclamation et de suivre son état.',
'color' => 'bg-slate-100 text-slate-700 ring-slate-200',
'line' => 'from-slate-400 to-slate-300',
],
];
@endphp

<section id="presentation" class="relative overflow-hidden bg-slate-50">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(14,165,233,0.18),transparent_32%),radial-gradient(circle_at_top_right,rgba(244,114,182,0.16),transparent_30%),radial-gradient(circle_at_bottom,rgba(40,70,37,0.10),transparent_36%)]"></div>

    <div class="absolute left-12 top-28 h-52 w-52 rounded-full bg-sky-200/40 blur-3xl"></div>
    <div class="absolute right-8 top-20 h-60 w-60 rounded-full bg-rose-200/40 blur-3xl"></div>
    <div class="absolute bottom-0 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-hd-green/10 blur-3xl"></div>

    <div class="hd-container relative pt-8 pb-12 sm:pt-10 sm:pb-14">
        <div class="grid items-center gap-10 lg:grid-cols-[1.08fr_0.92fr]">
            <div>
                <span class="inline-flex items-center rounded-full bg-white px-4 py-2 text-sm font-bold text-hd-green shadow-sm ring-1 ring-slate-200">
                    Application interne de gestion coworking
                </span>

                <h1 class="mt-5 text-4xl font-black tracking-tight text-slate-950 sm:text-6xl">
                    Une gestion plus claire pour les espaces
                    <span class="relative inline-block">
                        <span class="bg-gradient-to-r from-sky-600 via-rose-500 to-hd-green bg-clip-text text-transparent">
                            Hello Desk.
                        </span>
                        <span class="absolute -bottom-2 left-0 h-2 w-full rounded-full bg-gradient-to-r from-sky-200 via-rose-200 to-green-200"></span>
                    </span>
                </h1>

                <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">
                    Cette plateforme centralise la gestion des campus, des étages, des bureaux,
                    des salles de réunion, des positions open space, des clients, des réservations,
                    des contrats et des réclamations.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-hd-green px-6 py-3 text-sm font-bold text-white shadow-lg shadow-green-900/15 transition hover:-translate-y-0.5 hover:bg-[#20391e]">
                        Se connecter
                    </a>

                    <a href="#modules"
                        class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-3 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:text-hd-green hover:shadow-md">
                        Découvrir les modules
                    </a>
                </div>

                <div class="mt-5 flex flex-wrap gap-3 text-sm font-semibold">
                    <span class="rounded-full bg-sky-50 px-4 py-2 text-sky-700 ring-1 ring-sky-100">
                        Admin
                    </span>
                    <span class="rounded-full bg-rose-50 px-4 py-2 text-rose-700 ring-1 ring-rose-100">
                        Commercial
                    </span>
                    <span class="rounded-full bg-violet-50 px-4 py-2 text-violet-700 ring-1 ring-violet-100">
                        Client
                    </span>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-4 rounded-[2rem] bg-gradient-to-br from-sky-200/50 via-rose-200/50 to-green-200/50 blur-2xl"></div>

                <div class="relative overflow-hidden rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-2xl shadow-slate-300/60 backdrop-blur">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold text-slate-400">Vue globale</p>
                            <h2 class="text-xl font-black text-slate-900">Hello Desk</h2>
                        </div>

                        <div class="rounded-2xl bg-hd-green/10 px-4 py-2 text-sm font-black text-hd-green">
                            Live
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl bg-sky-50 p-5 ring-1 ring-sky-100">
                            <p class="text-sm font-bold text-sky-700">Espaces</p>
                            <p class="mt-2 text-3xl font-black text-slate-950">36</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Bureaux, salles, positions</p>
                        </div>

                        <div class="rounded-3xl bg-rose-50 p-5 ring-1 ring-rose-100">
                            <p class="text-sm font-bold text-rose-700">Réservations</p>
                            <p class="mt-2 text-3xl font-black text-slate-950">18</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Suivi opérationnel</p>
                        </div>

                        <div class="rounded-3xl bg-violet-50 p-5 ring-1 ring-violet-100">
                            <p class="text-sm font-bold text-violet-700">Prospects</p>
                            <p class="mt-2 text-3xl font-black text-slate-950">24</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Pipeline CRM</p>
                        </div>

                        <div class="rounded-3xl bg-emerald-50 p-5 ring-1 ring-emerald-100">
                            <p class="text-sm font-bold text-emerald-700">Contrats</p>
                            <p class="mt-2 text-3xl font-black text-slate-950">12</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Échéances suivies</p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-3xl bg-slate-50 p-4 ring-1 ring-slate-200">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-black text-slate-900">Carte interactive</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    Campus, étages et statuts visibles dans l’espace connecté.
                                </p>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
                                <span class="h-3 w-3 rounded-full bg-sky-400"></span>
                                <span class="h-3 w-3 rounded-full bg-rose-400"></span>
                                <span class="h-3 w-3 rounded-full bg-amber-300"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="objectif" class="bg-white py-14">
    <div class="hd-container">
        <div class="grid gap-6 lg:grid-cols-3">
            @foreach($objectifs as $objectif)
            <div class="group relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/70">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r {{ $objectif['accent'] }}"></div>

                <div class="flex items-center justify-between">
                    <span class="rounded-2xl px-3 py-2 text-sm font-black ring-1 {{ $objectif['soft'] }}">
                        {{ $objectif['number'] }}
                    </span>

                    <span class="h-10 w-10 rounded-2xl bg-gradient-to-br {{ $objectif['accent'] }} opacity-20 transition group-hover:opacity-35"></span>
                </div>

                <h2 class="mt-5 text-xl font-black text-slate-950">
                    {{ $objectif['title'] }}
                </h2>

                <p class="mt-3 text-sm leading-6 text-slate-600">
                    {{ $objectif['text'] }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section id="modules" class="relative overflow-hidden bg-slate-50 py-16">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(14,165,233,0.10),transparent_28%),radial-gradient(circle_at_80%_20%,rgba(244,114,182,0.10),transparent_28%)]"></div>

    <div class="hd-container relative">
        <div class="mb-10 max-w-2xl">
            <span class="inline-flex rounded-full bg-white px-4 py-2 text-sm font-black text-hd-green shadow-sm ring-1 ring-slate-200">
                Modules principaux
            </span>

            <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-950">
                Une application construite autour des besoins réels de Hello Desk.
            </h2>

            <p class="mt-3 leading-7 text-slate-600">
                Chaque module répond à une partie importante du travail quotidien :
                gestion des espaces, relation client, réservations, contrats et suivi.
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($modules as $module)
            <div class="group relative overflow-hidden rounded-[1.75rem] border border-white bg-white p-6 shadow-sm ring-1 ring-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/80">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $module['line'] }}"></div>

                <div class="mb-5 inline-flex rounded-2xl px-3 py-2 text-sm font-black ring-1 {{ $module['color'] }}">
                    Module
                </div>

                <h3 class="text-lg font-black text-slate-950">
                    {{ $module['title'] }}
                </h3>

                <p class="mt-3 text-sm leading-6 text-slate-600">
                    {{ $module['text'] }}
                </p>

                <div class="mt-6 flex items-center gap-2">
                    <span class="h-2 w-8 rounded-full bg-gradient-to-r {{ $module['line'] }}"></span>
                    <span class="text-xs font-bold text-slate-400">Hello Desk</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection