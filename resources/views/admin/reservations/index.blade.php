@extends('layouts.app')

@section('title', 'Réservations - Administration')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Réservations</h1>
            <p class="mt-1 text-sm text-gray-500">
                Liste des réservations créées pour les clients Hello Desk.
            </p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.interactive-map.index') }}"
            class="rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                Voir la carte interactive
            </a>

            <a href="{{ route('admin.reservations.create') }}"
            class="rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Créer une réservation
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-4">Client</th>
                    <th class="px-5 py-4">Espace</th>
                    <th class="px-5 py-4">Début</th>
                    <th class="px-5 py-4">Fin</th>
                    <th class="px-5 py-4">Statut</th>
                    <th class="px-5 py-4">Contrat</th>
                    <th class="px-5 py-4 text-right">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($reservations as $reservation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4 font-medium text-gray-900">
                            {{ $reservation->client?->full_name ?? 'Client supprimé' }}
                        </td>

                        <td class="px-5 py-4 text-gray-700">
                            {{ $reservation->space?->name ?? 'Espace supprimé' }}
                        </td>

                        <td class="px-5 py-4 text-gray-600">
                            {{ $reservation->starts_at?->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-5 py-4 text-gray-600">
                            {{ $reservation->ends_at?->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-5 py-4">
                            <span class="rounded-full bg-yellow-50 px-3 py-1 text-xs font-semibold text-yellow-700">
                                {{ $reservation->status }}
                            </span>
                        </td>

                        <td class="px-5 py-4">
                            @if($reservation->contract)
                                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                    Créé
                                </span>
                            @else
                                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                                    Manquant
                                </span>
                            @endif
                        </td>

                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.reservations.show', $reservation) }}"
                               class="font-semibold text-[#284625] hover:underline">
                                Voir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                            Aucune réservation pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $reservations->links() }}
    </div>
</div>
@endsection