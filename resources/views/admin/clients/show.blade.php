@extends('layouts.app')

@section('title', 'Détail client - Administration')

@section('content')

@php
$legalFileComplete = $client->hasCompleteLegalFile();

$clientTypeLabel = match($client->client_type) {
'physique' => 'Personne physique',
'morale' => 'Personne morale',
default => 'Non renseigné',
};
@endphp

<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-6xl px-6 py-8">

        <div class="mb-6">
            <a href="{{ route('admin.clients.index') }}"
                class="text-sm font-semibold text-[#284625] hover:underline">
                ← Retour aux clients
            </a>
        </div>

        @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
        @endif

        @if(session('temporary_password'))
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
            <p class="font-semibold">Identifiants de connexion du client</p>

            <div class="mt-3 space-y-2">
                <p>
                    Email :
                    <span class="rounded bg-white px-2 py-1 font-mono font-bold">
                        {{ $client->email }}
                    </span>
                </p>

                <p>
                    Mot de passe temporaire :
                    <span class="rounded bg-white px-2 py-1 font-mono font-bold">
                        {{ session('temporary_password') }}
                    </span>
                </p>
            </div>

            <p class="mt-3 text-xs leading-5">
                Copiez ces informations et envoyez-les au client manuellement. Le mot de passe ne sera affiché qu’une seule fois.
            </p>
        </div>
        @endif

        @if(!$legalFileComplete)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="font-bold">Dossier juridique incomplet</p>
                    <p class="mt-1">
                        Veuillez compléter les informations légales du client avant la création du contrat.
                    </p>
                </div>

                <a href="{{ route('admin.clients.edit', $client) }}"
                    class="inline-flex h-10 items-center justify-center rounded-xl bg-amber-600 px-4 text-sm font-bold text-white hover:bg-amber-700">
                    Compléter le dossier
                </a>
            </div>
        </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[#284625]">Dossier client</p>
                            <h1 class="mt-1 text-3xl font-bold text-slate-900">
                                {{ $client->full_name }}
                            </h1>
                            <p class="mt-2 text-sm text-slate-500">
                                {{ $client->company_name ?? 'Sans entreprise' }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            @if($client->status === 'active')
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                                Actif
                            </span>
                            @else
                            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700 ring-1 ring-red-200">
                                Inactif
                            </span>
                            @endif

                            <a href="{{ route('admin.clients.edit', $client) }}"
                                class="inline-flex h-10 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-bold text-blue-700 transition hover:bg-blue-100">
                                Modifier
                            </a>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-5 text-lg font-bold text-slate-900">Informations client</h2>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Email</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $client->email }}</p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Téléphone</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $client->phone ?? '-' }}</p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Date d’entrée client</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ $client->registered_at ? $client->registered_at->format('d/m/Y') : $client->created_at->format('d/m/Y') }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Campus principal</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $client->mainCampus->name ?? '-' }}</p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Origine</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ $client->prospect ? 'Converti depuis un prospect' : 'Créé directement' }}
                            </p>
                        </div>
                    </div>
                </section>

                @if($client->prospect)
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                Prospect d’origine
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Historique du premier contact commercial avant la conversion en client.
                            </p>
                        </div>

                        <a href="{{ route('admin.prospects.show', $client->prospect) }}"
                            class="inline-flex h-10 items-center justify-center rounded-xl border border-[#284625]/20 bg-[#284625]/5 px-4 text-sm font-bold text-[#284625] hover:bg-[#284625]/10">
                            Voir le prospect
                        </a>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Nom du prospect</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ $client->prospect->full_name ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Premier commercial</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ $client->prospect->assignedCommercial?->name ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Campus préféré initial</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ $client->prospect->preferredCampus?->name ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Type d’espace recherché</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ $client->prospect->preferredSpaceType?->name ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Date d’entrée CRM</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ $client->prospect->registered_at?->format('d/m/Y') ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Statut CRM</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ ucfirst(str_replace('_', ' ', $client->prospect->crm_status ?? '-')) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4 md:col-span-2">
                            <p class="text-xs font-bold uppercase text-slate-400">Besoin initial</p>
                            <p class="mt-1 text-sm leading-6 text-slate-800">
                                {{ $client->prospect->need ?? 'Non renseigné' }}
                            </p>
                        </div>
                    </div>
                </section>
                @endif

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Informations juridiques</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Résumé du dossier juridique du client.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if($legalFileComplete)
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                                Dossier complet
                            </span>
                            @else
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">
                                À compléter
                            </span>
                            @endif

                            <button type="button"
                                id="toggleLegalInfo"
                                class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 hover:bg-slate-50">
                                Afficher
                            </button>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Type de client</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ $clientTypeLabel }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Identifiant principal</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                @if($client->client_type === 'physique')
                                {{ strtoupper(str_replace('_', ' ', $client->identity_document_type ?? '')) ?: '-' }}
                                @if($client->identity_document_number)
                                — {{ $client->identity_document_number }}
                                @endif
                                @elseif($client->client_type === 'morale')
                                ICE — {{ $client->ice_number ?? '-' }}
                                @else
                                -
                                @endif
                            </p>
                        </div>
                    </div>

                    <div id="legalExtraInfo" class="mt-5 hidden border-t border-slate-100 pt-5">
                        @if($client->client_type === 'physique')
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Nom et prénom</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')) ?: '-' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Pièce d’identité</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ strtoupper(str_replace('_', ' ', $client->identity_document_type ?? '-')) }}
                                    —
                                    {{ $client->identity_document_number ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Nationalité</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $client->nationality ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Ville</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $client->city ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4 md:col-span-2">
                                <p class="text-xs font-bold uppercase text-slate-400">Adresse</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $client->address ?? '-' }}
                                </p>
                            </div>
                        </div>
                        @elseif($client->client_type === 'morale')
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Raison sociale</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $client->company_name ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Forme juridique</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ strtoupper(str_replace('_', ' ', $client->legal_form ?? '-')) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">ICE</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $client->ice_number ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">IF</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $client->if_number ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">RC</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $client->rc_number ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Patente / TP</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $client->patente_number ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">CNSS</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $client->cnss_number ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Représentant légal</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $client->legal_representative_full_name ?? '-' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ strtoupper(str_replace('_', ' ', $client->legal_representative_identity_document_type ?? '-')) }}
                                    —
                                    {{ $client->legal_representative_identity_document_number ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4 md:col-span-2">
                                <p class="text-xs font-bold uppercase text-slate-400">Adresse du siège social</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $client->headquarters_address ?? '-' }}
                                </p>
                            </div>
                        </div>
                        @else
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            Le type de client n’est pas encore renseigné.
                        </div>
                        @endif
                    </div>
                </section>

                @include('shared.clients._attachments', ['client' => $client, 'mode' => 'view'])

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold text-slate-900">Notes</h2>
                    <p class="text-sm leading-7 text-slate-700">
                        {{ $client->notes ?? 'Aucune note.' }}
                    </p>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">Compte de connexion</h2>

                    <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm">
                        <p class="text-xs font-bold uppercase text-slate-400">Email de connexion</p>
                        <p class="mt-1 font-medium text-slate-800">{{ $client->user->email }}</p>
                    </div>

                    <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm">
                        <p class="text-xs font-bold uppercase text-slate-400">Rôle</p>
                        <p class="mt-1 font-medium text-slate-800">{{ $client->user->role }}</p>
                    </div>

                    @if($client->user->must_change_password)
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        Le client utilise encore un mot de passe temporaire.
                    </div>
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">Actions</h2>

                    <form method="POST"
                        action="{{ route('admin.clients.resetPassword', $client) }}"
                        class="mt-4"
                        onsubmit="return confirm('Réinitialiser le mot de passe de ce client ?');">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 text-sm font-bold text-amber-700 hover:bg-amber-100">
                            Réinitialiser le mot de passe
                        </button>
                    </form>

                    @if($client->status === 'active')
                    <form method="POST"
                        action="{{ route('admin.clients.deactivate', $client) }}"
                        class="mt-4"
                        onsubmit="return confirm('Désactiver ce compte client ?');">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-bold text-red-700 hover:bg-red-100">
                            Désactiver le compte
                        </button>
                    </form>
                    @else
                    <form method="POST"
                        action="{{ route('admin.clients.reactivate', $client) }}"
                        class="mt-4">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-bold text-emerald-700 hover:bg-emerald-100">
                            Réactiver le compte
                        </button>
                    </form>
                    @endif
                </section>
            </aside>
        </div>
    </div>
</div>
<script>
    const toggleLegalInfoButton = document.getElementById('toggleLegalInfo');
    const legalExtraInfo = document.getElementById('legalExtraInfo');

    if (toggleLegalInfoButton && legalExtraInfo) {
        toggleLegalInfoButton.addEventListener('click', () => {
            legalExtraInfo.classList.toggle('hidden');

            toggleLegalInfoButton.textContent = legalExtraInfo.classList.contains('hidden') ?
                'Afficher' :
                'Masquer';
        });
    }
</script>
@endsection