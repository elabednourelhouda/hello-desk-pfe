@extends('layouts.app')

@section('title', 'Détail prospect - Commercial')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-7xl px-6 py-8">

        <div class="mb-6">
            <a href="{{ request('return_url', route('commercial.prospects.index', ['view' => 'active'])) }}"
                class="text-sm font-semibold text-[#284625] hover:underline">
                ← Retour aux prospects
            </a>
        </div>

        @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
        @endif

        @if(session('info'))
        <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-700">
            {{ session('info') }}
        </div>
        @endif

        @if(session('temporary_password'))
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
            <p class="font-semibold">Compte client créé.</p>

            <p class="mt-2">
                Email de connexion :
                <span class="rounded bg-white px-2 py-1 font-mono font-bold">
                    {{ session('client_email', $prospect->email) }}
                </span>
            </p>

            <p class="mt-2">
                Mot de passe temporaire :
                <span class="rounded bg-white px-2 py-1 font-mono font-bold">
                    {{ session('temporary_password') }}
                </span>
            </p>

            <p class="mt-2 text-xs">
                Pour l’instant, copiez ces informations. Plus tard, on pourra les envoyer automatiquement par email.
            </p>
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

        {{-- TOP PART: prospect details + sidebar --}}
        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">

            {{-- LEFT SIDE --}}
            <div class="space-y-6">

                {{-- Header --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[#284625]">Dossier prospect</p>

                            <h1 class="mt-1 text-3xl font-bold text-gray-900">
                                {{ $prospect->full_name }}
                            </h1>

                            <p class="mt-2 text-sm text-gray-500">
                                {{ $prospect->company_name ?? 'Sans entreprise' }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <span class="inline-flex w-fit rounded-full bg-[#284625]/10 px-3 py-1 text-xs font-semibold text-[#284625] ring-1 ring-[#284625]/20">
                                {{ $statuses[$prospect->crm_status] ?? $prospect->crm_status }}
                            </span>

                            <a href="{{ route('commercial.prospects.crm', ['prospect' => $prospect, 'return_url' => request('return_url', route('commercial.prospects.index', ['view' => 'active']))]) }}"
                                class="inline-flex h-10 items-center justify-center rounded-xl border border-[#284625]/20 bg-[#284625]/10 px-4 text-sm font-bold text-[#284625] transition hover:bg-[#284625]/15">
                                Suivi CRM
                            </a>

                            <a href="{{ route('commercial.prospects.edit', $prospect) }}"
                                class="inline-flex h-10 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-bold text-blue-700 transition hover:bg-blue-100">
                                Modifier
                            </a>
                        </div>
                    </div>
                </section>

                {{-- Prospect info --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-5 text-lg font-bold text-gray-900">Informations du prospect</h2>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase text-gray-400">Email</p>
                            <p class="mt-1 text-sm font-medium text-gray-800">{{ $prospect->email ?? '-' }}</p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase text-gray-400">Téléphone</p>
                            <p class="mt-1 text-sm font-medium text-gray-800">{{ $prospect->phone ?? '-' }}</p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase text-gray-400">Date d’entrée CRM</p>
                            <p class="mt-1 text-sm font-medium text-gray-800">
                                {{ $prospect->registered_at ? \Carbon\Carbon::parse($prospect->registered_at)->format('d/m/Y') : '-' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase text-gray-400">Commercial responsable</p>
                            <p class="mt-1 text-sm font-medium text-gray-800">
                                {{ $prospect->assignedCommercial->name ?? 'Non affecté' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase text-gray-400">Campus préféré</p>
                            <p class="mt-1 text-sm font-medium text-gray-800">
                                {{ $prospect->preferredCampus->name ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase text-gray-400">Type d’espace recherché</p>
                            <p class="mt-1 text-sm font-medium text-gray-800">
                                {{ $prospect->preferredSpaceType->name ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Source du prospect
                            </p>

                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $sources[$prospect->source] ?? ($prospect->source ?: 'Non renseignée') }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase text-gray-400">Nombre de personnes / postes</p>
                            <p class="mt-1 text-sm font-medium text-gray-800">
                                {{ $prospect->people_count ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase text-gray-400">Budget approximatif</p>
                            <p class="mt-1 text-sm font-medium text-gray-800">
                                {{ $prospect->budget ? number_format($prospect->budget, 2, ',', ' ') . ' DH' : '-' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase text-gray-400">Date de début souhaitée</p>
                            <p class="mt-1 text-sm font-medium text-gray-800">
                                {{ $prospect->desired_start_date ? \Carbon\Carbon::parse($prospect->desired_start_date)->format('d/m/Y') : '-' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase text-gray-400">Durée souhaitée</p>
                            <p class="mt-1 text-sm font-medium text-gray-800">
                                @switch($prospect->desired_rental_period)
                                @case('hourly')
                                À l’heure
                                @break

                                @case('daily')
                                À la journée
                                @break

                                @case('monthly')
                                Au mois
                                @break

                                @case('custom')
                                Personnalisée
                                @break

                                @default
                                -
                                @endswitch
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Need --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold text-gray-900">Besoin recherché</h2>
                    <p class="text-sm leading-7 text-gray-700">
                        {{ $prospect->need ?? 'Aucun besoin précisé.' }}
                    </p>
                </section>

                {{-- Notes --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold text-gray-900">Notes commerciales</h2>
                    <p class="text-sm leading-7 text-gray-700">
                        {{ $prospect->notes ?? 'Aucune note commerciale.' }}
                    </p>
                </section>
            </div>

            {{-- RIGHT SIDE --}}
            <aside class="space-y-6">

                {{-- Conversion client --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Conversion client</h2>

                    @if($prospect->crm_status === 'converted' && $prospect->convertedClient)
                    <div class="mt-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                        <p class="font-semibold">Déjà converti en client</p>
                        <p class="mt-1">{{ $prospect->convertedClient->name }}</p>
                        <p>{{ $prospect->convertedClient->email }}</p>
                    </div>

                    <a href="{{ request('return_url', route('commercial.prospects.index', ['view' => 'active'])) }}"
                        class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                        Retour à la liste
                    </a>

                    @elseif($prospect->crm_status === 'lost')
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <p class="font-semibold">Prospect perdu</p>
                        <p class="mt-1 text-xs">
                            Réactivez ce prospect avant de le convertir en client.
                        </p>
                    </div>

                    @else
                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Convertir ce prospect va créer un dossier client et un compte de connexion avec un mot de passe temporaire.
                    </p>

                    <form method="POST" action="{{ route('commercial.prospects.convert', $prospect) }}" class="mt-5">
                        @csrf

                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Email officiel du client
                        </label>

                        <input type="email"
                            name="client_email"
                            value="{{ old('client_email', $prospect->email) }}"
                            required
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                            placeholder="client@email.com">

                        <button type="submit"
                            class="mt-4 inline-flex h-12 w-full items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                            Convertir en client
                        </button>
                    </form>
                    @endif
                </section>

                {{-- Actions --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Actions du dossier</h2>

                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Gérer le statut CRM de ce prospect.
                    </p>

                    @if($prospect->crm_status === 'lost')
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <p class="font-semibold">Ce prospect est marqué comme perdu.</p>
                        <p class="mt-1 text-xs">
                            Vous pouvez le réactiver si la discussion reprend.
                        </p>

                        @if($prospect->lost_reason)
                        <div class="mt-4 rounded-xl border border-red-100 bg-white/70 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-red-700">
                                Raison de perte
                            </p>
                            <p class="mt-2 text-sm leading-6 text-red-900">
                                {{ $prospect->lost_reason }}
                            </p>
                        </div>
                        @endif
                    </div>

                    <form method="POST"
                        action="{{ route('commercial.prospects.reactivate', $prospect) }}"
                        class="mt-4">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                            class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-bold text-blue-700 transition hover:bg-blue-100">
                            Réactiver le prospect
                        </button>
                    </form>

                    @elseif($prospect->crm_status === 'converted')
                    <div class="mt-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                        <p class="font-semibold">Ce prospect est déjà converti en client.</p>
                        <p class="mt-1 text-xs">
                            Aucune action “Perdu” n’est disponible après conversion.
                        </p>
                    </div>

                    @else
                    <form method="POST"
                        action="{{ route('commercial.prospects.markLost', $prospect) }}"
                        onsubmit="return confirm('Marquer ce prospect comme perdu ?');"
                        class="mt-4">
                        @csrf
                        @method('PATCH')

                        <div class="space-y-4">
                            <div>
                                <label for="lost_reason_key" class="mb-2 block text-sm font-semibold text-gray-700">
                                    Raison de perte <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="lost_reason_key"
                                    name="lost_reason_key"
                                    required
                                    class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                                    <option value="">Sélectionner une raison</option>

                                    @foreach(($lostReasons ?? []) as $key => $label)
                                    <option value="{{ $key }}" @selected(old('lost_reason_key')===$key)>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>

                                @error('lost_reason_key')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="lost_reason_details" class="mb-2 block text-sm font-semibold text-gray-700">
                                    Détails complémentaires
                                </label>

                                <textarea
                                    id="lost_reason_details"
                                    name="lost_reason_details"
                                    rows="3"
                                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                                    placeholder="Exemple : le prospect souhaite un budget inférieur ou a choisi une autre localisation...">{{ old('lost_reason_details') }}</textarea>

                                @error('lost_reason_details')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <button type="submit"
                            class="mt-4 inline-flex h-11 w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-bold text-red-700 transition hover:bg-red-100">
                            Marquer comme perdu
                        </button>
                    </form>
                    @endif
                </section>

                {{-- Next steps --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Prochaines étapes</h2>

                    <div class="mt-4 space-y-3 text-sm text-gray-600">
                        <p>1. Ajouter un suivi si nécessaire.</p>
                        <p>2. Convertir le prospect en client.</p>
                        <p>3. Créer une réservation après conversion.</p>
                        <p>4. Associer un contrat à la réservation.</p>
                    </div>

                    <span class="mt-4 inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                        Réservations bientôt
                    </span>
                </section>

            </aside>
        </div>
    </div>
</div>
@endsection