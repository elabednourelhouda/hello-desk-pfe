@extends('layouts.app')

@section('title', 'Tableau de bord commercial - Hello Desk')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-10">

    <div class="flex flex-col justify-between gap-5 md:flex-row md:items-center">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-[#284625]">
                Espace commercial
            </p>

            <h1 class="mt-2 text-3xl font-bold text-gray-900">
                Tableau de bord commercial
            </h1>

            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600">
                Suivez vos prospects, vos clients, vos réservations et votre périmètre affecté.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('commercial.prospects.create') }}"
            class="rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#20391f]">
                Ajouter un prospect
            </a>

            <a href="{{ route('commercial.prospects.index') }}"
            class="rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Voir mes prospects
            </a>

            <a href="#"
            class="cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-5 py-3 text-sm font-semibold text-gray-400">
                Créer une réservation
            </a>
        </div>
    </div>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('commercial.prospects.index') }}"
        class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-sm font-medium text-gray-500">Prospects</p>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['prospects'] }}</p>
            <p class="mt-2 text-xs text-gray-500">Prospects liés à votre activité</p>
        </a>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Clients</p>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['clients'] }}</p>
            <p class="mt-2 text-xs text-gray-500">Clients de votre périmètre</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Réservations</p>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['reservations'] }}</p>
            <p class="mt-2 text-xs text-gray-500">Réservations suivies</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Espaces disponibles</p>
            <p class="mt-3 text-3xl font-bold text-[#284625]">{{ $stats['available_spaces'] }}</p>
            <p class="mt-2 text-xs text-gray-500">Espaces pouvant être réservés</p>
        </div>
    </div>

    <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">
                    Visites prévues aujourd’hui
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Liste des prospects qui doivent visiter Hello Desk aujourd’hui.
                </p>
            </div>

            <span class="w-fit rounded-full bg-[#284625]/10 px-3 py-1 text-xs font-semibold text-[#284625]">
                {{ $todayVisits->count() }} visite(s)
            </span>
        </div>

        <div class="mt-5 space-y-3">
            @forelse($todayVisits as $visit)
                <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-semibold text-gray-900">
                                {{ $visit->visit_time ? \Illuminate\Support\Str::limit($visit->visit_time, 5, '') : 'Heure non précisée' }}
                            </p>

                            <span class="rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-semibold text-yellow-800">
                                Planifiée
                            </span>
                        </div>

                        <p class="mt-2 text-sm font-semibold text-gray-800">
                            {{ $visit->prospect?->full_name ?? 'Prospect supprimé' }}
                        </p>

                        <p class="mt-1 text-sm text-gray-600">
                            {{ $visit->campus?->name ?? 'Campus non précisé' }}
                            —
                            {{ $visit->spaceType?->name ?? 'Type non précisé' }}
                        </p>

                        @if($visit->notes)
                            <p class="mt-2 text-sm leading-6 text-gray-500">
                                {{ $visit->notes }}
                            </p>
                        @endif
                    </div>

                    @if($visit->prospect)
                        <a href="{{ route('commercial.prospects.show', $visit->prospect) }}"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                            Voir dossier
                        </a>
                    @endif
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5">
                    <p class="font-semibold text-gray-800">
                        Aucune visite prévue aujourd’hui
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        Les visites planifiées pour aujourd’hui apparaîtront ici.
                    </p>
                </div>
            @endforelse
        </div>
    </section>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">
                    Mon périmètre
                </h2>

                <span class="rounded-full bg-[#284625]/10 px-3 py-1 text-xs font-semibold text-[#284625]">
                    Affectation
                </span>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($assignments as $assignment)
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $assignment['campus'] }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $assignment['floor'] }}
                        </p>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4">
                        <p class="text-sm font-semibold text-gray-800">
                            Aucune affectation trouvée
                        </p>
                        <p class="mt-1 text-sm leading-6 text-gray-500">
                            L’administrateur doit affecter ce commercial à un campus ou à un étage.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">
                    Prospects récents
                </h2>

                <a href="{{ route('commercial.prospects.index') }}"
                   class="text-sm font-semibold text-[#284625] hover:underline">
                    Voir tout
                </a>
            </div>

            <div class="mt-5 overflow-hidden rounded-xl border border-gray-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Prospect</th>
                            <th class="px-4 py-3">Entreprise</th>
                            <th class="px-4 py-3">Statut</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($recentProspects as $prospect)
                            <tr>
                                <td class="px-4 py-4 font-semibold text-gray-900">
                                    {{ $prospect['name'] }}
                                </td>

                                <td class="px-4 py-4 text-gray-600">
                                    {{ $prospect['company'] ?? '—' }}
                                </td>

                                <td class="px-4 py-4">
                                    <span class="rounded-full bg-[#284625]/10 px-3 py-1 text-xs font-semibold text-[#284625]">
                                        {{ $prospect['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">
                                    Aucun prospect récent pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">
                    Réservations à venir
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Les prochaines réservations liées à votre activité commerciale.
                </p>
            </div>

            <a href="#"
            class="hidden cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-400 sm:inline-flex">
                Voir les réservations
            </a>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($upcomingReservations as $reservation)
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ $reservation['client'] }}
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ $reservation['space'] }}
                            </p>
                        </div>

                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-600 ring-1 ring-gray-200">
                            {{ $reservation['status'] }}
                        </span>
                    </div>

                    <p class="mt-4 text-sm text-gray-500">
                        Date :
                        <span class="font-semibold text-gray-800">
                            {{ $reservation['date'] ? \Illuminate\Support\Carbon::parse($reservation['date'])->format('d/m/Y') : 'Non définie' }}
                        </span>
                    </p>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5 md:col-span-2 lg:col-span-3">
                    <p class="font-semibold text-gray-800">
                        Aucune réservation à venir
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        Les prochaines réservations apparaîtront ici.
                    </p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection