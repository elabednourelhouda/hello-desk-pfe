@extends('layouts.app')

@section('title', 'Notifications - Hello Desk')

@section('content')
@php
    $user = auth()->user();

    $backRoutes = [
        'admin' => 'admin.dashboard',
        'commercial' => 'commercial.dashboard',
        'client' => 'client.dashboard',
    ];

    $readRouteNames = [
        'admin' => 'admin.notifications.read',
        'commercial' => 'commercial.notifications.read',
        'client' => 'client.notifications.read',
    ];

    $readAllRouteNames = [
        'admin' => 'admin.notifications.readAll',
        'commercial' => 'commercial.notifications.readAll',
        'client' => 'client.notifications.readAll',
    ];

    $backRoute = $backRoutes[$user->role] ?? 'home';
    $readRouteName = $readRouteNames[$user->role] ?? null;
    $readAllRouteName = $readAllRouteNames[$user->role] ?? null;
@endphp

<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-5xl px-6 py-8">

        <div class="mb-6">
            <a href="{{ route($backRoute) }}"
               class="text-sm font-semibold text-[#284625] hover:underline">
                ← Retour au tableau de bord
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6 flex flex-col justify-between gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm md:flex-row md:items-center">
            <div>
                <h1 class="text-2xl font-black text-gray-900">
                    Notifications
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Consultez les alertes importantes liées aux réservations, contrats, paiements et réclamations.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <span class="rounded-full bg-[#284625]/10 px-4 py-2 text-sm font-bold text-[#284625]">
                    {{ $unreadCount }} non lue(s)
                </span>

                @if($unreadCount > 0 && $readAllRouteName)
                    <form method="POST" action="{{ route($readAllRouteName) }}">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                                class="inline-flex h-10 items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white transition hover:bg-[#1f351d]">
                            Tout marquer comme lu
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data ?? [];

                    $title = $data['title'] ?? 'Notification';
                    $message = $data['message'] ?? 'Nouvelle notification.';
                    $url = $data['url'] ?? null;
                    $type = $data['type'] ?? 'info';

                    $isUnread = is_null($notification->read_at);

                    $typeClasses = [
                        'success' => 'bg-green-50 text-green-700 border-green-200',
                        'warning' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                        'danger' => 'bg-red-50 text-red-700 border-red-200',
                        'info' => 'bg-blue-50 text-blue-700 border-blue-200',
                    ];

                    $typeClass = $typeClasses[$type] ?? $typeClasses['info'];
                @endphp

                <div class="rounded-2xl border {{ $isUnread ? 'border-[#284625]/30 bg-white' : 'border-gray-200 bg-white/80' }} p-5 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                        <div class="flex gap-4">
                            <div class="mt-1 h-3 w-3 rounded-full {{ $isUnread ? 'bg-[#284625]' : 'bg-gray-300' }}"></div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-base font-black text-gray-900">
                                        {{ $title }}
                                    </h2>

                                    <span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $typeClass }}">
                                        {{ ucfirst($type) }}
                                    </span>

                                    @if($isUnread)
                                        <span class="rounded-full bg-[#284625]/10 px-2.5 py-1 text-xs font-bold text-[#284625]">
                                            Non lue
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-2 text-sm leading-6 text-gray-600">
                                    {{ $message }}
                                </p>

                                <p class="mt-2 text-xs font-semibold text-gray-400">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        <div class="flex shrink-0 gap-2">
                            @if($readRouteName)
                                <form method="POST" action="{{ route($readRouteName, $notification->id) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            class="inline-flex h-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-bold text-gray-700 transition hover:border-[#284625]/30 hover:bg-[#284625]/5 hover:text-[#284625]">
                                        {{ $url ? 'Ouvrir' : 'Marquer comme lue' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center">
                    <h2 class="text-lg font-black text-gray-900">
                        Aucune notification
                    </h2>

                    <p class="mt-2 text-sm text-gray-500">
                        Les alertes importantes apparaîtront ici.
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection