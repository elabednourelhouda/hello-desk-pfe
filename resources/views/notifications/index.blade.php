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

    $roleLabels = [
        'admin' => 'Espace administrateur',
        'commercial' => 'Espace commercial',
        'client' => 'Espace client',
    ];

    $backRoute = $backRoutes[$user->role] ?? 'home';
    $readRouteName = $readRouteNames[$user->role] ?? null;
    $readAllRouteName = $readAllRouteNames[$user->role] ?? null;

    $typeLabels = [
        'success' => 'Information validée',
        'warning' => 'Attention',
        'danger' => 'Important',
        'info' => 'Information',
    ];

    $typeClasses = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'danger' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'info' => 'bg-blue-50 text-blue-700 ring-blue-200',
    ];
@endphp

<div class="mx-auto max-w-6xl px-6 py-8">

    <div class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-[1.4fr_0.7fr]">
            <div class="bg-gradient-to-br from-[#284625] via-[#2f6130] to-[#3f7a3b] p-8 text-white">
                <p class="text-sm font-bold uppercase tracking-wide text-white/70">
                    {{ $roleLabels[$user->role] ?? 'Hello Desk' }}
                </p>

                <h1 class="mt-3 text-3xl font-black">
                    Notifications
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                    Consultez les alertes importantes liées aux réservations, contrats, paiements et réclamations.
                </p>
            </div>

            <div class="bg-white p-8">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                    Notifications non lues
                </p>

                <p class="mt-2 text-4xl font-black text-[#284625]">
                    {{ $unreadCount }}
                </p>

                @if($unreadCount > 0 && $readAllRouteName)
                    <form method="POST" action="{{ route($readAllRouteName) }}" class="mt-5">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                                class="inline-flex h-10 items-center justify-center rounded-xl bg-[#284625] px-4 text-sm font-bold text-white shadow-sm transition hover:bg-[#20391f]">
                            Tout marquer comme lu
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route($backRoute) }}"
           class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
            ← Retour au tableau de bord
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-black text-slate-900">
                Liste des notifications
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Les notifications les plus récentes apparaissent en premier.
            </p>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data ?? [];

                    $title = $data['title'] ?? 'Notification';
                    $message = $data['message'] ?? 'Nouvelle notification.';
                    $url = $data['url'] ?? null;
                    $type = $data['type'] ?? 'info';

                    $isUnread = is_null($notification->read_at);

                    $typeLabel = $typeLabels[$type] ?? 'Information';
                    $typeClass = $typeClasses[$type] ?? $typeClasses['info'];
                @endphp

                <div class="p-5 transition {{ $isUnread ? 'bg-[#284625]/5' : 'bg-white hover:bg-slate-50' }}">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="flex gap-4">
                            <span class="mt-2 h-2.5 w-2.5 rounded-full {{ $isUnread ? 'bg-[#284625]' : 'bg-slate-300' }}"></span>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-black text-slate-900">
                                        {{ $title }}
                                    </h3>

                                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $typeClass }}">
                                        {{ $typeLabel }}
                                    </span>

                                    @if($isUnread)
                                        <span class="rounded-full bg-[#284625]/10 px-3 py-1 text-xs font-bold text-[#284625]">
                                            Non lue
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                                    {{ $message }}
                                </p>

                                <p class="mt-2 text-xs font-semibold text-slate-400">
                                    {{ $notification->created_at?->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>

                        @if($readRouteName)
                            <form method="POST" action="{{ route($readRouteName, $notification->id) }}" class="shrink-0">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                        class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:border-[#284625]/30 hover:bg-[#284625]/5 hover:text-[#284625]">
                                    {{ $url ? 'Ouvrir' : ($isUnread ? 'Marquer comme lue' : 'Voir') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <h2 class="text-lg font-black text-slate-900">
                        Aucune notification
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Les alertes importantes apparaîtront ici.
                    </p>
                </div>
            @endforelse
        </div>

        @if(method_exists($notifications, 'links'))
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection