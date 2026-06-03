@extends('layouts.app')

@section('title', 'Contrats - Hello Desk')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-8">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Contrats</h1>
            <p class="mt-1 text-sm text-gray-500">
                Gérez les contrats liés aux réservations Hello Desk.
            </p>
        </div>

        <a href="{{ route('admin.reservations.index') }}"
           class="rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
            Voir les réservations
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET"
          action="{{ route('admin.contracts.index') }}"
          class="mb-6 grid gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-3">
        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">Recherche</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Client, email, titre..."
                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">Statut</label>
            <select name="status"
                    class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                <option value="all" @selected(request('status', 'all') === 'all')>Tous</option>
                <option value="draft" @selected(request('status') === 'draft')>Brouillon</option>
                <option value="active" @selected(request('status') === 'active')>Actif</option>
                <option value="expired" @selected(request('status') === 'expired')>Expiré</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Annulé</option>
            </select>
        </div>

        <div class="flex items-end gap-3">
            <button type="submit"
                    class="h-12 flex-1 rounded-xl bg-[#284625] px-5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                Filtrer
            </button>

            <a href="{{ route('admin.contracts.index') }}"
               class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-300 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-4">Contrat</th>
                    <th class="px-5 py-4">Client</th>
                    <th class="px-5 py-4">Espace</th>
                    <th class="px-5 py-4">Période</th>
                    <th class="px-5 py-4">Statut</th>
                    <th class="px-5 py-4">PDF</th>
                    <th class="px-5 py-4 text-right">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($contracts as $contract)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4 font-semibold text-gray-900">
                            {{ $contract->title }}
                        </td>

                        <td class="px-5 py-4 text-gray-700">
                            {{ $contract->client?->full_name ?? 'Client supprimé' }}
                        </td>

                        <td class="px-5 py-4 text-gray-700">
                            {{ $contract->reservation?->space?->name ?? 'Non précisé' }}
                        </td>

                        <td class="px-5 py-4 text-gray-600">
                            {{ $contract->start_date?->format('d/m/Y') }}
                            →
                            {{ $contract->end_date?->format('d/m/Y') }}
                        </td>

                        <td class="px-5 py-4">
                            @php
                                $statusClasses = [
                                    'draft' => 'bg-yellow-50 text-yellow-700',
                                    'active' => 'bg-green-50 text-green-700',
                                    'expired' => 'bg-gray-100 text-gray-700',
                                    'cancelled' => 'bg-red-50 text-red-700',
                                ];

                                $statusLabels = [
                                    'draft' => 'Brouillon',
                                    'active' => 'Actif',
                                    'expired' => 'Expiré',
                                    'cancelled' => 'Annulé',
                                ];
                            @endphp

                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$contract->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$contract->status] ?? $contract->status }}
                            </span>
                        </td>

                        <td class="px-5 py-4">
                            @if($contract->pdf_path)
                                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                    Importé
                                </span>
                            @else
                                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                                    Manquant
                                </span>
                            @endif
                        </td>

                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.contracts.show', $contract) }}"
                               class="font-semibold text-[#284625] hover:underline">
                                Voir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                            Aucun contrat trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $contracts->links() }}
    </div>
</div>
@endsection