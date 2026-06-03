@extends('layouts.app')

@section('title', 'Espace client - Hello Desk')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-10">
    <div class="mb-8 rounded-3xl bg-[#284625] p-8 text-white shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-wide text-white/70">
            Espace client
        </p>

        <h1 class="mt-2 text-3xl font-bold">
            Bonjour, {{ auth()->user()->name }}
        </h1>

        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/75">
            Consultez vos réservations, contrats, notifications et réclamations Hello Desk.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-gray-500">Notifications non lues</p>
            <p class="mt-3 text-3xl font-bold text-[#284625]">
                {{ $unreadCount }}
            </p>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-gray-500">Mes réservations</p>
            <p class="mt-3 text-sm text-gray-600">
                Bientôt disponible.
            </p>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-gray-500">Mes contrats</p>
            <p class="mt-3 text-sm text-gray-600">
                Bientôt disponible.
            </p>
        </section>
    </div>

    <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Mes notifications</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Dernières alertes liées à vos contrats, paiements et réservations.
                </p>
            </div>

            @if($unreadCount > 0)
                <form method="POST" action="{{ route('client.notifications.readAll') }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Marquer comme lues
                    </button>
                </form>
            @endif
        </div>

        <div class="mt-6 space-y-3">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                @endphp

                <div class="rounded-xl border {{ $notification->read_at ? 'border-gray-200 bg-white' : 'border-green-200 bg-green-50' }} p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ $data['title'] ?? 'Notification' }}
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ $data['message'] ?? 'Nouvelle notification.' }}
                            </p>

                            @if(isset($data['amount_due']) || isset($data['due_date']))
                                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                    @if(isset($data['amount_due']))
                                        <span class="rounded-full bg-white px-3 py-1 font-semibold text-gray-700">
                                            Montant : {{ number_format((float) $data['amount_due'], 2, ',', ' ') }} DH
                                        </span>
                                    @endif

                                    @if(isset($data['due_date']))
                                        <span class="rounded-full bg-white px-3 py-1 font-semibold text-gray-700">
                                            Échéance : {{ $data['due_date'] }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <span class="text-xs text-gray-400">
                            {{ $notification->created_at?->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500">
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