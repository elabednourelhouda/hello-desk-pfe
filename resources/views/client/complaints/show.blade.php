@extends('layouts.app')

@section('title', 'Détail réclamation - Hello Desk')

@section('content')
@php
    $statusClasses = [
        'new' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'in_progress' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'waiting' => 'bg-purple-50 text-purple-700 ring-purple-200',
        'resolved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'closed' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'rejected' => 'bg-red-50 text-red-700 ring-red-200',
    ];

    $priorityClasses = [
        'low' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'normal' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'high' => 'bg-orange-50 text-orange-700 ring-orange-200',
        'urgent' => 'bg-red-50 text-red-700 ring-red-200',
    ];
@endphp

<div class="mx-auto max-w-5xl px-6 py-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500">Réclamation #{{ $complaint->id }}</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">
                {{ $complaint->subject }}
            </h1>
        </div>

        <a href="{{ route('client.complaints.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Retour
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap gap-3">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClasses[$complaint->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                        {{ $complaint->status_label }}
                    </span>

                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $priorityClasses[$complaint->priority] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                        Priorité : {{ $complaint->priority_label }}
                    </span>
                </div>

                @if($complaint->description)
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">
                        {{ $complaint->description }}
                    </p>
                @else
                    <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                        Aucune description supplémentaire n’a été ajoutée.
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Réponse de l’équipe Hello Desk</h2>

                @if($complaint->admin_response)
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">
                        {{ $complaint->admin_response }}
                    </p>
                @else
                    <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                        Aucune réponse n’a encore été ajoutée. Votre réclamation est en attente de traitement.
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Informations</h2>

                <div class="mt-5 space-y-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Type</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $complaint->type_label }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-500">Date d’envoi</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $complaint->created_at?->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-500">Dernière mise à jour</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $complaint->updated_at?->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    @if($complaint->resolved_at)
                        <div>
                            <p class="text-sm font-medium text-slate-500">Résolue le</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">
                                {{ $complaint->resolved_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Éléments liés</h2>

                <div class="mt-5 space-y-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Réservation</p>

                        @if($complaint->reservation)
                            <a href="{{ route('client.reservations.show', $complaint->reservation) }}"
                               class="mt-1 inline-block text-sm font-semibold text-[#284625] hover:underline">
                                Réservation #{{ $complaint->reservation->id }}
                                - {{ $complaint->reservation->space->name ?? 'Espace' }}
                            </a>
                        @else
                            <p class="mt-1 text-sm text-slate-600">Aucune</p>
                        @endif
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-500">Contrat</p>

                        @if($complaint->contract)
                            <a href="{{ route('client.contracts.show', $complaint->contract) }}"
                               class="mt-1 inline-block text-sm font-semibold text-[#284625] hover:underline">
                                Contrat #{{ $complaint->contract->id }}
                            </a>
                        @else
                            <p class="mt-1 text-sm text-slate-600">Aucun</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection