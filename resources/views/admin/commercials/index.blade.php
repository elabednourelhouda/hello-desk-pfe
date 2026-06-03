@extends('layouts.app')

@section('title', 'Gestion des commerciaux - Hello Desk')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-8">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-[#284625]">
                Administration
            </p>

            <h1 class="mt-1 text-3xl font-bold text-gray-900">
                Gestion des commerciaux
            </h1>

            <p class="mt-2 text-sm text-gray-600">
                Créez et consultez les comptes du personnel commercial Hello Desk.
            </p>
        </div>

        <a href="{{ route('admin.commercials.create') }}"
           class="inline-flex items-center justify-center rounded-xl bg-[#284625] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#20391f]">
            Ajouter un commercial
        </a>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Total commerciaux</p>
            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $totalCount }}
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Affectations</p>
            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $assignedCount }}
            </p>
            <p class="mt-2 text-xs text-gray-500">
                Commerciaux ayant au moins une affectation.
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Prochaine étape</p>
            <p class="mt-3 text-lg font-bold text-gray-900">
                Affecter les commerciaux
            </p>
            <p class="mt-2 text-xs text-gray-500">
                Campus ou étage selon le périmètre.
            </p>
        </div>
    </div>

    <section class="mt-8 rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-5">
            <form method="GET" action="{{ route('admin.commercials.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Rechercher par nom ou email..."
                       class="h-12 flex-1 rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

                <button type="submit"
                        class="rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white hover:bg-[#20391f]">
                    Rechercher
                </button>

                @if(request('search'))
                    <a href="{{ route('admin.commercials.index') }}"
                       class="rounded-xl border border-gray-300 px-5 py-3 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Réinitialiser
                    </a>
                @endif
            </form>
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
                            Créé le
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wide text-gray-500">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($commercials as $commercial)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">
                                    {{ $commercial->name }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    Personnel commercial
                                </p>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $commercial->email }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ optional($commercial->created_at)->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.commercials.show', $commercial) }}"
                                   class="text-sm font-semibold text-[#284625] hover:underline">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <p class="text-sm text-gray-500">
                                    Aucun commercial trouvé.
                                </p>

                                <a href="{{ route('admin.commercials.create') }}"
                                   class="mt-4 inline-flex rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white hover:bg-[#20391f]">
                                    Ajouter le premier commercial
                                </a>
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
@endsection