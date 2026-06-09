@extends('layouts.app')

@section('title', 'Mes réclamations - Hello Desk')

@section('content')
@php
    use Illuminate\Support\Str;

    $statusClasses = [
        'new' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'in_progress' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'waiting' => 'bg-purple-50 text-purple-700 ring-purple-200',
        'resolved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'closed' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'rejected' => 'bg-red-50 text-red-700 ring-red-200',
    ];
@endphp

<div class="mx-auto max-w-7xl px-6 py-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500">Espace client</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Mes réclamations</h1>
            <p class="mt-2 text-sm text-slate-600">
                Suivez vos demandes et les réponses de l’équipe Hello Desk.
            </p>
        </div>

        <a href="{{ route('client.complaints.create') }}"
           class="inline-flex items-center justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1f351d]">
            Nouvelle réclamation
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['total'] ?? 0 }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Nouvelles</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['new'] ?? 0 }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">En cours</p>
            <p class="mt-2 text-2xl font-bold text-blue-600">{{ $stats['in_progress'] ?? 0 }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Résolues</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $stats['resolved'] ?? 0 }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if($complaints->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Sujet</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Priorité</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach($complaints as $complaint)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $complaint->subject }}</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $complaint->description ? Str::limit($complaint->description, 80) : 'Aucune description ajoutée.' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-700">
                                    {{ $complaint->type_label }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-700">
                                    {{ $complaint->priority_label }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClasses[$complaint->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                                        {{ $complaint->status_label }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ $complaint->created_at?->format('d/m/Y') }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('client.complaints.show', $complaint) }}"
                                       class="text-sm font-semibold text-[#284625] hover:underline">
                                        Voir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-6 py-4">
                {{ $complaints->links() }}
            </div>
        @else
            <div class="p-10 text-center">
                <h2 class="text-lg font-bold text-slate-900">Aucune réclamation</h2>
                <p class="mt-2 text-sm text-slate-500">
                    Vous n’avez pas encore envoyé de réclamation.
                </p>

                <a href="{{ route('client.complaints.create') }}"
                   class="mt-5 inline-flex items-center justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1f351d]">
                    Déposer une réclamation
                </a>
            </div>
        @endif
    </div>
</div>
@endsection