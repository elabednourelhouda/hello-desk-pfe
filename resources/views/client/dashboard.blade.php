@extends('layouts.app')

@section('title', 'Espace client - Hello Desk')

@section('content')
@php
    $clientProfile = $clientProfile ?? null;
    $reservations = $reservations ?? collect();
    $contracts = $contracts ?? collect();
    $payments = $payments ?? collect();
    $notifications = $notifications ?? collect();

    $reservationsCount = $reservationsCount ?? $reservations->count();
    $contractsCount = $contractsCount ?? $contracts->count();
    $duePaymentsCount = $duePaymentsCount ?? 0;
    $unreadCount = $unreadCount ?? 0;

    $paidPaymentsCount = $payments->where('status', 'paid')->count();
    $totalPaymentsCount = max($payments->count(), 1);
    $paidRate = round(($paidPaymentsCount / $totalPaymentsCount) * 100);
    $dueRate = min(100, round(($duePaymentsCount / max($payments->count(), 1)) * 100));

    $progressWidthClass = function ($value) {
        if ($value <= 0) {
            return 'w-0';
        }

        if ($value <= 25) {
            return 'w-1/4';
        }

        if ($value <= 50) {
            return 'w-1/2';
        }

        if ($value <= 75) {
            return 'w-3/4';
        }

        return 'w-full';
    };

    $paidRateClass = $progressWidthClass($paidRate);
    $dueRateClass = $progressWidthClass($dueRate);

    $reservationStatusLabels = [
        'pending' => 'En attente',
        'confirmed' => 'Confirmée',
        'in_progress' => 'En cours',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
        'expired' => 'Expirée',
    ];

    $contractStatusLabels = [
        'draft' => 'Brouillon',
        'active' => 'Actif',
        'expired' => 'Expiré',
        'cancelled' => 'Annulé',
    ];

    $paymentStatusClasses = [
        'À payer' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'Payé' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'En retard' => 'bg-red-50 text-red-700 ring-red-200',
        'Annulé' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];
@endphp

<div class="mx-auto max-w-7xl px-6 py-8">

    {{-- Header --}}
    <section class="overflow-hidden rounded-3xl border border-[#284625]/20 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-[1.4fr_0.8fr]">
            <div class="bg-gradient-to-br from-[#284625] via-[#315f32] to-[#4b7f3f] p-8 text-white">
                <p class="text-sm font-semibold uppercase tracking-wide text-white/75">
                    Espace client
                </p>

                <h1 class="mt-3 text-3xl font-bold">
                    Bonjour, {{ auth()->user()->name }}
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                    Consultez vos réservations, contrats, échéances de paiement et notifications liées à votre compte Hello Desk.
                </p>

                @if($clientProfile)
                    <div class="mt-6 flex flex-wrap gap-3 text-xs font-semibold">
                        <span class="rounded-full bg-white/15 px-3 py-1 text-white">
                            {{ $clientProfile->full_name }}
                        </span>

                        <span class="rounded-full bg-white/15 px-3 py-1 text-white">
                            {{ $clientProfile->email }}
                        </span>

                        @if($clientProfile->mainCampus)
                            <span class="rounded-full bg-white/15 px-3 py-1 text-white">
                                Site : {{ $clientProfile->mainCampus->name }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Résumé client --}}
            <div class="bg-white p-8">
                <h2 class="text-lg font-bold text-gray-900">
                    Résumé de mon compte
                </h2>

                <div class="mt-5 space-y-4">
                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-semibold text-gray-600">Paiements réglés</span>
                            <span class="font-bold text-[#284625]">{{ $paidRate }}%</span>
                        </div>

                        <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-[#284625] {{ $paidRateClass }}"></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-semibold text-gray-600">Paiements à suivre</span>
                            <span class="font-bold text-pink-600">{{ $dueRate }}%</span>
                        </div>

                        <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-pink-500 {{ $dueRateClass }}"></div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                            Notifications non lues
                        </p>

                        <p class="mt-2 text-3xl font-bold text-[#284625]">
                            {{ $unreadCount }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(session('success'))
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(!$clientProfile)
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-6 text-sm text-red-700">
            Aucun profil client n’est lié à ce compte. Veuillez contacter l’administration Hello Desk.
        </div>
    @else

        {{-- KPI Cards --}}
        <section id="overview" class="mt-6 grid scroll-mt-24 gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-gray-500">Mes réservations</p>
                <p class="mt-2 text-3xl font-bold text-[#284625]">{{ $reservationsCount }}</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-gray-500">Mes contrats</p>
                <p class="mt-2 text-3xl font-bold text-[#284625]">{{ $contractsCount }}</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-gray-500">Paiements à suivre</p>
                <p class="mt-2 text-3xl font-bold text-pink-600">{{ $duePaymentsCount }}</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-gray-500">Notifications</p>
                <p class="mt-2 text-3xl font-bold text-[#284625]">{{ $unreadCount }}</p>
            </div>
        </section>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">

            {{-- Réservations --}}
            <section id="reservations" class="scroll-mt-24 rounded-3xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h2 class="text-lg font-bold text-gray-900">
                        Mes réservations
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Dernières réservations liées à votre compte.
                    </p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($reservations as $reservation)
                        <div class="p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="font-bold text-gray-900">
                                        {{ $reservation->space?->name ?? 'Espace supprimé' }}
                                    </p>

                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ $reservation->campus?->name ?? 'Site non précisé' }}
                                        —
                                        {{ $reservation->floor?->name ?? 'Étage non précisé' }}
                                    </p>

                                    <p class="mt-2 text-xs font-medium text-gray-500">
                                        Du {{ $reservation->starts_at?->format('d/m/Y H:i') }}
                                        au {{ $reservation->ends_at?->format('d/m/Y H:i') }}
                                    </p>
                                </div>

                                <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700 ring-1 ring-gray-200">
                                    {{ $reservationStatusLabels[$reservation->status] ?? ucfirst($reservation->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center">
                            <p class="font-bold text-gray-800">Aucune réservation</p>
                            <p class="mt-1 text-sm text-gray-500">
                                Vos réservations apparaîtront ici lorsqu’elles seront créées.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Contrats --}}
            <section id="contrats" class="scroll-mt-24 rounded-3xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h2 class="text-lg font-bold text-gray-900">
                        Mes contrats
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Contrats générés ou liés à vos réservations.
                    </p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($contracts as $contract)
                        <div class="p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="font-bold text-gray-900">
                                        {{ $contract->title }}
                                    </p>

                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ $contract->reservation?->space?->name ?? 'Espace non précisé' }}
                                    </p>

                                    <p class="mt-2 text-xs font-medium text-gray-500">
                                        Du {{ $contract->start_date?->format('d/m/Y') }}
                                        au {{ $contract->end_date?->format('d/m/Y') }}
                                    </p>
                                </div>

                                <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700 ring-1 ring-gray-200">
                                    {{ $contractStatusLabels[$contract->status] ?? ucfirst($contract->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center">
                            <p class="font-bold text-gray-800">Aucun contrat</p>
                            <p class="mt-1 text-sm text-gray-500">
                                Vos contrats apparaîtront ici après création d’une réservation.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- Paiements --}}
        <section id="paiements" class="mt-6 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5">
                <h2 class="text-lg font-bold text-gray-900">
                    Mes échéances de paiement
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Suivi des montants à payer, réglés ou en retard.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Contrat</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Échéance</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Montant</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Payé</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Statut</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($payments as $payment)
                            @php
                                $realStatus = $payment->real_status;
                                $paymentClass = $paymentStatusClasses[$realStatus] ?? 'bg-gray-100 text-gray-700 ring-gray-200';
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900">
                                        {{ $payment->contract?->title ?? 'Contrat supprimé' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $payment->due_date?->format('d/m/Y') }}
                                </td>

                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                    {{ number_format($payment->amount_due, 2, ',', ' ') }} DH
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ number_format($payment->amount_paid, 2, ',', ' ') }} DH
                                </td>

                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $paymentClass }}">
                                        {{ $realStatus }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                    Aucune échéance de paiement pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- Notifications --}}
    <section id="notifications" class="mt-6 rounded-3xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">
                    Mes notifications
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Dernières alertes liées à vos contrats, paiements et réservations.
                </p>
            </div>

            @if($unreadCount > 0)
                <form method="POST" action="{{ route('client.notifications.readAll') }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">
                        Marquer comme lues
                    </button>
                </form>
            @endif
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                @endphp

                <div class="{{ $notification->read_at ? 'bg-white' : 'bg-green-50' }} p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-bold text-gray-900">
                                {{ $data['title'] ?? 'Notification' }}
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ $data['message'] ?? 'Nouvelle notification.' }}
                            </p>
                        </div>

                        <span class="text-xs text-gray-400">
                            {{ $notification->created_at?->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-sm text-gray-500">
                    Aucune notification pour le moment.
                </div>
            @endforelse
        </div>
    </section>

    <form action="{{ route('logout') }}" method="POST" class="mt-8">
        @csrf

        <button class="rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white hover:bg-[#20391f]">
            Déconnexion
        </button>
    </form>
</div>
@endsection