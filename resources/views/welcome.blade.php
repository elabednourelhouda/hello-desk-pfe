@extends('layouts.app')

@section('title', 'Accueil - Hello Desk')

@section('content')
    <section id="presentation" class="relative overflow-hidden bg-white">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(40,70,37,0.12),transparent_34%),radial-gradient(circle_at_top_right,rgba(40,70,37,0.08),transparent_30%)]"></div>

    <div class="absolute left-1/2 top-10 h-72 w-72 -translate-x-1/2 rounded-full bg-hd-green/5 blur-3xl"></div>
    <div class="absolute -left-24 top-24 h-56 w-56 rounded-full bg-hd-green/10 blur-3xl"></div>
    <div class="absolute -right-24 bottom-10 h-56 w-56 rounded-full bg-hd-green/10 blur-3xl"></div>

    <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-hd-green/25 to-transparent"></div>

    <div class="hd-container relative py-16 sm:py-20">
        <div class="mx-auto max-w-5xl text-center">
            <span class="hd-badge bg-white text-hd-green shadow-sm ring-1 ring-green-100">
                Application interne de gestion coworking
            </span>

            <h1 class="mt-7 text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                Une gestion plus claire pour les espaces
                <span class="relative inline-block text-hd-green">
                    Hello Desk.
                    <span class="absolute -bottom-2 left-0 h-2 w-full rounded-full bg-hd-green/15"></span>
                </span>
            </h1>

            <p class="mx-auto mt-7 max-w-3xl text-lg leading-8 text-gray-600">
                Cette plateforme permet de centraliser la gestion des campus, des étages,
                des bureaux, des salles de réunion, des positions open space, des clients,
                des réservations, des contrats et des réclamations.
            </p>

            <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('login') }}" class="hd-btn-primary">
                    Se connecter
                </a>

                <a href="#modules" class="hd-btn-secondary">
                    Découvrir les modules
                </a>
            </div>
        </div>
    </div>
</section>

    <section id="objectif" class="py-14">
        <div class="hd-container">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="hd-card border-t-4 border-t-hd-green">
                    <p class="text-sm font-semibold text-hd-green">01</p>
                    <h2 class="mt-3 text-xl font-bold text-gray-900">
                        Centraliser les informations
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Regrouper les espaces, les clients, les réservations et les contrats
                        dans une seule application au lieu de travailler avec des fichiers séparés.
                    </p>
                </div>

                <div class="hd-card border-t-4 border-t-hd-green">
                    <p class="text-sm font-semibold text-hd-green">02</p>
                    <h2 class="mt-3 text-xl font-bold text-gray-900">
                        Améliorer le suivi
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Aider le personnel à suivre les disponibilités, les prospects,
                        les échéances, les réclamations et les actions importantes.
                    </p>
                </div>

                <div class="hd-card border-t-4 border-t-hd-green">
                    <p class="text-sm font-semibold text-hd-green">03</p>
                    <h2 class="mt-3 text-xl font-bold text-gray-900">
                        Adapter l’accès à chaque rôle
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Donner à chaque utilisateur un espace adapté à son rôle :
                        administrateur, commercial ou client.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="modules" class="bg-white py-16">
        <div class="hd-container">
            <div class="mb-10 max-w-2xl">
                <span class="hd-badge bg-hd-green-soft text-hd-green">
                    Modules principaux
                </span>

                <h2 class="mt-4 text-3xl font-bold text-gray-900">
                    Une application construite autour des besoins réels de Hello Desk.
                </h2>

                <p class="mt-3 text-gray-600">
                    Chaque module répond à une partie importante du travail quotidien :
                    gestion des espaces, relation client, réservations, contrats et suivi.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div class="hd-card transition hover:-translate-y-1 hover:border-hd-green/40 hover:shadow-md">
                    <h3 class="text-lg font-bold text-gray-900">Campus et étages</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Organiser les deux campus Hello Desk, leurs étages et la répartition des espaces.
                    </p>
                </div>

                <div class="hd-card transition hover:-translate-y-1 hover:border-hd-green/40 hover:shadow-md">
                    <h3 class="text-lg font-bold text-gray-900">Espaces coworking</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Gérer les bureaux privés, les salles de réunion et les positions open space.
                    </p>
                </div>

                <div class="hd-card transition hover:-translate-y-1 hover:border-hd-green/40 hover:shadow-md">
                    <h3 class="text-lg font-bold text-gray-900">Réservations</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Créer, consulter et suivre les réservations selon les disponibilités des espaces.
                    </p>
                </div>

                <div class="hd-card transition hover:-translate-y-1 hover:border-hd-green/40 hover:shadow-md">
                    <h3 class="text-lg font-bold text-gray-900">Prospects et clients</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Suivre les prospects, les visites, les demandes et la conversion en clients.
                    </p>
                </div>

                <div class="hd-card transition hover:-translate-y-1 hover:border-hd-green/40 hover:shadow-md">
                    <h3 class="text-lg font-bold text-gray-900">Contrats et paiements</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Associer les réservations aux contrats et suivre les échéances importantes.
                    </p>
                </div>

                <div class="hd-card transition hover:-translate-y-1 hover:border-hd-green/40 hover:shadow-md">
                    <h3 class="text-lg font-bold text-gray-900">Réclamations</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Permettre aux clients de déposer une réclamation et de suivre son état.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection