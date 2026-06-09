@extends('layouts.app')

@section('title', 'Suivi CRM - ' . $prospect->full_name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-7xl px-6 py-8">

        {{-- Back --}}
        <div class="mb-6">
            <a href="{{ request('return_url', route('admin.prospects.index', ['view' => 'active'])) }}"
               class="text-sm font-semibold text-[#284625] hover:underline">
                ← Retour aux prospects
            </a>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">Veuillez corriger les erreurs suivantes :</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <section class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[#284625]">Suivi CRM</p>

                    <h1 class="mt-1 text-3xl font-bold text-gray-900">
                        {{ $prospect->full_name }}
                    </h1>

                    <p class="mt-2 text-sm text-gray-500">
                        {{ $prospect->company_name ?? 'Sans entreprise' }}
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('admin.prospects.show', ['prospect' => $prospect, 'return_url' => request('return_url')]) }}"
                       class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                        Dossier
                    </a>

                    <a href="{{ route('admin.prospects.edit', $prospect) }}"
                       class="inline-flex h-10 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-bold text-blue-700 transition hover:bg-blue-100">
                        Modifier
                    </a>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[1fr_380px]">

            {{-- LEFT: visits and requests --}}
            <div class="space-y-6">

                {{-- Visites --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[#284625]">Suivi CRM</p>
                            <h2 class="mt-1 text-xl font-bold text-gray-900">
                                Visites du prospect
                            </h2>
                            <p class="mt-2 text-sm leading-6 text-gray-500">
                                Ici, vous pouvez garder l’historique des visites planifiées, effectuées ou annulées pour ce prospect.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                {{ $prospect->visits->count() }} visite(s)
                            </span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <h3 class="text-base font-bold text-gray-900">Ajouter une visite</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Exemple : visite planifiée, visite effectuée ou visite annulée.
                        </p>

                        <form method="POST"
                            action="{{ route('admin.prospects.visits.store', $prospect) }}"
                            class="mt-5 space-y-4 rounded-xl bg-white p-4">
                            @csrf

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                                        Date de visite <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date"
                                        name="visit_date"
                                        value="{{ old('visit_date', now()->toDateString()) }}"
                                        required
                                        class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                                        Heure
                                    </label>
                                    <input type="time"
                                        name="visit_time"
                                        value="{{ old('visit_time') }}"
                                        class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                                        Campus
                                    </label>
                                    <select name="campus_id"
                                            class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                                        <option value="">Non précisé</option>
                                        @foreach($campuses as $campus)
                                            <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>
                                                {{ $campus->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                                        Type d’espace
                                    </label>
                                    <select name="space_type_id"
                                            class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                                        <option value="">Non précisé</option>
                                        @foreach($spaceTypes as $type)
                                            <option value="{{ $type->id }}" @selected(old('space_type_id') == $type->id)>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                                        Statut
                                    </label>
                                    <select name="status"
                                            class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                                        <option value="planned" @selected(old('status') === 'planned')>Planifiée</option>
                                        <option value="done" @selected(old('status') === 'done')>Effectuée</option>
                                        <option value="cancelled" @selected(old('status') === 'cancelled')>Annulée</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Notes
                                </label>
                                <textarea name="notes"
                                        rows="3"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                                        placeholder="Exemple : le prospect a visité le bureau B12.">{{ old('notes') }}</textarea>
                            </div>

                            <button type="submit"
                                    class="inline-flex h-11 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                                Ajouter la visite
                            </button>
                        </form>

                        <div class="mt-5 space-y-3">
                            @forelse($prospect->visits->sortByDesc('visit_date') as $visit)
                                <div class="rounded-xl border border-gray-200 bg-white p-4">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-semibold text-gray-900">
                                                    @if($visit->visit_date)
                                                        {{ \Carbon\Carbon::parse($visit->visit_date)->format('d/m/Y') }}
                                                    @else
                                                        Date non précisée
                                                    @endif

                                                    @if($visit->visit_time)
                                                        à {{ substr($visit->visit_time, 0, 5) }}
                                                    @endif
                                                </p>

                                                <span class="rounded-full bg-[#284625]/10 px-2.5 py-1 text-xs font-semibold text-[#284625]">
                                                    @switch($visit->status)
                                                        @case('planned') Planifiée @break
                                                        @case('done') Effectuée @break
                                                        @case('cancelled') Annulée @break
                                                        @default {{ $visit->status }}
                                                    @endswitch
                                                </span>
                                            </div>

                                            <p class="mt-2 text-sm text-gray-600">
                                                {{ $visit->campus?->name ?? 'Campus non précisé' }}
                                                —
                                                {{ $visit->spaceType?->name ?? 'Type non précisé' }}
                                            </p>

                                            @if($visit->notes)
                                                <p class="mt-2 text-sm leading-6 text-gray-700">
                                                    {{ $visit->notes }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            @if($visit->status === 'planned')
                                                <form method="POST" action="{{ route('admin.prospects.visits.done', $visit) }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit"
                                                            class="inline-flex h-9 items-center justify-center rounded-lg border border-green-200 bg-green-50 px-4 text-xs font-bold text-green-700 transition hover:bg-green-100">
                                                        Marquer effectuée
                                                    </button>
                                                </form>

                                                <form method="POST"
                                                    action="{{ route('admin.prospects.visits.cancel', $visit) }}"
                                                    onsubmit="return confirm('Annuler cette visite ?')">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit"
                                                            class="inline-flex h-9 items-center justify-center rounded-lg border border-amber-200 bg-amber-50 px-4 text-xs font-bold text-amber-700 transition hover:bg-amber-100">
                                                        Annuler
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST"
                                                action="{{ route('admin.prospects.visits.destroy', $visit) }}"
                                                onsubmit="return confirm('Supprimer cette visite ?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="inline-flex h-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 text-xs font-bold text-red-700 transition hover:bg-red-100">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="rounded-xl border border-dashed border-gray-300 bg-white p-4 text-sm text-gray-500">
                                    Aucune visite ajoutée pour ce prospect.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </section>

                {{-- Requests --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Demandes du prospect</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Gardez l’historique des besoins exprimés par le prospect.
                    </p>

                    <form method="POST"
                          action="{{ route('admin.prospects.requests.store', $prospect) }}"
                          class="mt-5 rounded-xl border border-gray-100 bg-gray-50 p-4">
                        @csrf

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Type de demande
                                </label>
                                <input type="text"
                                       name="request_type"
                                       value="{{ old('request_type') }}"
                                       placeholder="Ex: Bureau privé, salle de réunion..."
                                       class="h-11 w-full rounded-xl border border-gray-300 px-3 text-sm focus:border-[#284625] focus:ring-[#284625]">
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Priorité
                                </label>
                                <select name="priority"
                                        class="h-11 w-full rounded-xl border border-gray-300 px-3 text-sm focus:border-[#284625] focus:ring-[#284625]">
                                    <option value="normal">Normale</option>
                                    <option value="high">Élevée</option>
                                    <option value="low">Faible</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                Description
                            </label>
                            <textarea name="description"
                                      rows="3"
                                      placeholder="Ex: Le prospect cherche un bureau pour 2 personnes à partir du mois prochain..."
                                      class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-[#284625] focus:ring-[#284625]">{{ old('description') }}</textarea>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button type="submit"
                                    class="inline-flex h-10 items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white transition hover:bg-[#1f351d]">
                                Ajouter la demande
                            </button>
                        </div>
                    </form>

                    <div class="mt-5 space-y-3">
                        @forelse($prospect->requests ?? [] as $requestItem)
                            <div class="rounded-xl border border-gray-100 bg-white p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-gray-900">
                                            {{ $requestItem->request_type ?? 'Demande' }}
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $requestItem->description ?? 'Aucune description.' }}
                                        </p>
                                    </div>

                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        {{ $requestItem->priority ?? 'normal' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                                Aucune demande enregistrée pour ce prospect.
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- RIGHT: CRM summary --}}
            <aside class="space-y-6">
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Résumé CRM</h2>

                    <dl class="mt-5 space-y-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Statut actuel</dt>
                            <dd class="mt-1 font-semibold text-gray-900">
                                {{ $statuses[$prospect->crm_status] ?? $prospect->crm_status }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">Commercial responsable</dt>
                            <dd class="mt-1 font-semibold text-gray-900">
                                {{ $prospect->assignedCommercial->name ?? 'Non affecté' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">Besoin initial</dt>
                            <dd class="mt-1 font-semibold text-gray-900">
                                {{ $prospect->need ?? 'Non renseigné' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">Date d’entrée CRM</dt>
                            <dd class="mt-1 font-semibold text-gray-900">
                                {{ optional($prospect->registered_at)->format('d/m/Y') ?? 'Non renseignée' }}
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Notes commerciales</h2>

                    <p class="mt-3 whitespace-pre-line text-sm text-gray-600">
                        {{ $prospect->notes ?: 'Aucune note commerciale pour le moment.' }}
                    </p>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection