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

            {{-- LEFT: follow-ups and requests --}}
            <div class="space-y-6">

                {{-- Archive des appels et messages --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[#284625]">Suivi CRM</p>
                            <h2 class="mt-1 text-xl font-bold text-gray-900">
                                Archive des appels et messages
                            </h2>
                            <p class="mt-2 text-sm leading-6 text-gray-500">
                                Ici, vous pouvez garder l’historique des appels, messages, emails, relances et résumés de conversation avec ce prospect.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                {{ $prospect->visits->count() }} suivi(s)
                            </span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <h3 class="text-base font-bold text-gray-900">Ajouter un suivi</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Exemple : appel téléphonique, message WhatsApp, email, relance ou résumé de conversation.
                        </p>

                        <form method="POST"
                            action="{{ route('admin.prospects.visits.store', $prospect) }}"
                            class="mt-5 space-y-4 rounded-xl bg-white p-4">
                            @csrf

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                                        Date de suivi <span class="text-red-500">*</span>
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

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Date de prochaine relance
                                </label>
                                <input type="date"
                                    name="next_followup_at"
                                    value="{{ old('next_followup_at') }}"
                                    class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                                <p class="mt-1 text-xs text-gray-500">
                                    Laissez vide si aucune relance n’est nécessaire pour l’instant.
                                </p>
                                @error('next_followup_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Type de contact <span class="text-red-500">*</span>
                                </label>

                                <select name="contact_type"
                                    required
                                    class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                                    <option value="">Choisir le type de contact</option>
                                    <option value="appel_telephonique" @selected(old('contact_type')==='appel_telephonique' )>
                                        Appel téléphonique
                                    </option>
                                    <option value="whatsapp" @selected(old('contact_type')==='whatsapp' )>
                                        Message WhatsApp
                                    </option>
                                    <option value="email" @selected(old('contact_type')==='email' )>
                                        Email
                                    </option>
                                    <option value="message_recu" @selected(old('contact_type')==='message_recu' )>
                                        Message reçu
                                    </option>
                                    <option value="note_interne" @selected(old('contact_type')==='note_interne' )>
                                        Note interne
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Résumé de l’échange <span class="text-red-500">*</span>
                                </label>

                                <textarea name="summary"
                                    rows="4"
                                    required
                                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                                    placeholder="Exemple : le prospect a demandé les tarifs, son besoin a été confirmé, une proposition doit être envoyée.">{{ old('summary') }}</textarea>
                            </div>

                            <button type="submit"
                                class="inline-flex h-11 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f351d]">
                                Ajouter à l’archive
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
                                        </div>

                                        @if($visit->notes)
                                        <p class="mt-2 text-sm leading-6 text-gray-700">
                                            {!! nl2br(e($visit->notes)) !!}
                                        </p>
                                        @endif

                                        @if($visit->next_followup_at)
                                        <p class="mt-2 text-xs font-semibold text-[#284625]">
                                            → Prochaine relance prévue le {{ \Carbon\Carbon::parse($visit->next_followup_at)->format('d/m/Y') }}
                                        </p>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <form method="POST"
                                            action="{{ route('admin.prospects.visits.destroy', $visit) }}"
                                            onsubmit="return confirm('Supprimer ce suivi ?')">
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
                                Aucun suivi ajouté pour ce prospect.
                            </p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            {{-- RIGHT: CRM summary --}}
            <aside class="space-y-6">
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Résumé CRM</h2>

                    <dl class="mt-5 space-y-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Statut de relance</dt>
                            <dd class="mt-1 flex items-center gap-2 font-semibold text-gray-900">
                                @if($prospect->crm_followup_color)
                                    <span class="h-3 w-3 rounded-full
                                        {{ match($prospect->crm_followup_color) {
                                            'green' => 'bg-green-500',
                                            'yellow' => 'bg-amber-400',
                                            'red' => 'bg-red-500',
                                            default => 'bg-gray-300',
                                        } }}"></span>
                                    {{ $prospect->crm_followup_label }}
                                @else
                                    <span class="text-gray-400">Non applicable</span>
                                @endif
                            </dd>
                        </div>

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