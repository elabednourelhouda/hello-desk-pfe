<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hello Desk')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f6f8f6]">
    <header class="bg-white shadow-sm">
        <div class="bg-hd-green text-white">
            <div class="hd-container">
                <div class="flex flex-col justify-between gap-2 py-3 text-xs sm:flex-row sm:items-center">
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
                        <span class="font-semibold">Écrivez-nous :</span>
                        <a href="mailto:contact@hellodesk.ma" class="hover:underline">
                            contact@hellodesk.ma
                        </a>
                        <a href="mailto:hellodesk.centreville@gmail.com" class="hover:underline">
                            hellodesk.centreville@gmail.com
                        </a>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
                        <span class="font-semibold">Appelez-nous :</span>
                        <a href="tel:+212662598745" class="hover:underline">
                            Centre Ville : +212 662 59 87 45
                        </a>
                        <a href="tel:+212662183173" class="hover:underline">
                            Sidi Maârouf : +212 662 18 31 73
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="hd-container">
            <div class="flex min-h-24 items-center justify-between gap-8 py-4">
                <a href="{{ route('home') }}" class="flex items-center">
                    <img
                        src="{{ asset('images/hello-desk-logo.png') }}"
                        alt="Hello Desk"
                        class="h-20 w-auto">
                </a>

                @if(request()->routeIs('home'))
                <nav class="hidden items-center gap-8 md:flex">
                    <a href="{{ route('home') }}" class="text-sm font-semibold text-gray-700 hover:text-hd-green">
                        Accueil
                    </a>

                    <a href="#presentation" class="text-sm font-semibold text-gray-700 hover:text-hd-green">
                        Présentation
                    </a>

                    <a href="#modules" class="text-sm font-semibold text-gray-700 hover:text-hd-green">
                        Modules
                    </a>

                    <a href="#objectif" class="text-sm font-semibold text-gray-700 hover:text-hd-green">
                        Objectif
                    </a>
                </nav>
                @endif

                @auth
                @php
                $user = auth()->user();

                $roleLabels = [
                'admin' => 'Administrateur',
                'commercial' => 'Commercial',
                'client' => 'Client',
                ];

                $dashboardRoutes = [
                'admin' => 'admin.dashboard',
                'commercial' => 'commercial.dashboard',
                'client' => 'client.dashboard',
                ];

                $dashboardRoute = $dashboardRoutes[$user->role] ?? 'home';

                $initial = strtoupper(substr($user->name, 0, 1));

                $notificationIndexRoute = $user->role . '.notifications.index';
                $notificationReadRoute = $user->role . '.notifications.read';
                $notificationReadAllRoute = $user->role . '.notifications.readAll';

                $recentNotifications = $user->notifications()
                ->latest()
                ->take(5)
                ->get();

                $unreadNotificationsCount = $user->unreadNotifications()->count();
                @endphp

                <div class="flex items-center gap-3">
                    <div class="hidden items-center gap-3 rounded-2xl border border-sky-100 bg-white px-4 py-2 shadow-sm lg:flex">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-indigo-600 text-sm font-bold text-white shadow-sm">
                            {{ $initial }}
                        </div>

                        <div class="leading-tight">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $user->name }}
                                </p>

                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-700">
                                    {{ $roleLabels[$user->role] ?? $user->role }}
                                </span>
                            </div>

                            <p class="mt-0.5 text-xs text-gray-500">
                                {{ $user->email }}
                            </p>
                        </div>
                    </div>

                    @if(Route::has($notificationIndexRoute))
                    <details id="notificationDropdown" class="relative">
                        <summary class="relative flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:border-[#284625]/30 hover:bg-[#284625]/5 hover:text-[#284625]">
                            <img
                                src="{{ asset('images/notif.png') }}"
                                alt="Notifications"
                                class="h-5 w-5 object-contain">

                            @if($unreadNotificationsCount > 0)
                            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-[11px] font-bold text-white">
                                {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                            </span>
                            @endif
                        </summary>

                        <div class="absolute right-0 z-50 mt-3 w-80 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl">
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Notifications</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $unreadNotificationsCount }} non lue(s)
                                    </p>
                                </div>

                                @if($unreadNotificationsCount > 0 && Route::has($notificationReadAllRoute))
                                <form method="POST" action="{{ route($notificationReadAllRoute) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="text-xs font-semibold text-[#284625] hover:underline">
                                        Tout lire
                                    </button>
                                </form>
                                @endif
                            </div>

                            <div class="max-h-80 overflow-y-auto">
                                @forelse($recentNotifications as $notification)
                                @php
                                $notificationTitle = $notification->data['title'] ?? 'Notification';
                                $notificationMessage = $notification->data['message'] ?? '';
                                $isUnread = is_null($notification->read_at);
                                @endphp

                                @if(Route::has($notificationReadRoute))
                                <form method="POST" action="{{ route($notificationReadRoute, $notification->id) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                        class="block w-full border-b border-gray-100 px-4 py-3 text-left transition hover:bg-gray-50 {{ $isUnread ? 'bg-[#284625]/5' : 'bg-white' }}">
                                        <div class="flex items-start gap-3">
                                            <span class="mt-1 h-2.5 w-2.5 rounded-full {{ $isUnread ? 'bg-[#284625]' : 'bg-gray-300' }}"></span>

                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-gray-900">
                                                    {{ $notificationTitle }}
                                                </p>

                                                @if($notificationMessage)
                                                <p class="mt-1 text-xs leading-5 text-gray-600">
                                                    {{ \Illuminate\Support\Str::limit($notificationMessage, 90) }}
                                                </p>
                                                @endif

                                                <p class="mt-1 text-[11px] text-gray-400">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                    </button>
                                </form>
                                @endif
                                @empty
                                <div class="px-4 py-8 text-center">
                                    <p class="text-sm font-semibold text-gray-700">
                                        Aucune notification
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Les alertes importantes apparaîtront ici.
                                    </p>
                                </div>
                                @endforelse
                            </div>

                            <a href="{{ route($notificationIndexRoute) }}"
                                class="block bg-gray-50 px-4 py-3 text-center text-sm font-bold text-[#284625] hover:bg-[#284625]/5">
                                Voir toutes les notifications
                            </a>
                        </div>
                    </details>
                    @endif

                    <a href="{{ route($dashboardRoute) }}"
                        class="inline-flex h-11 items-center justify-center rounded-xl bg-gradient-to-r from-[#07691f] to-[#0eab35] px-5 text-sm font-bold text-white shadow-sm ring-1 ring-cyan-200 transition hover:from-[#0891B2] hover:to-[#0D9488] hover:shadow-md">
                        Mon espace
                    </a>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf

                        <button type="submit"
                            class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-bold text-gray-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                            Déconnexion
                        </button>
                    </form>
                </div>
                @else
                <a href="{{ route('login') }}" class="hd-btn-primary">
                    Se connecter
                </a>
                @endauth
            </div>
        </div>
    </header>

    @auth
    @php
    $user = auth()->user();

    $adminLinks = [
    ['label' => 'Tableau de bord', 'route' => 'admin.dashboard'],
    ['label' => 'Prospects', 'route' => 'admin.prospects.index'],
    ['label' => 'Clients', 'route' => 'admin.clients.index'],

    // Admin only
    ['label' => 'Commerciaux', 'route' => 'admin.commercials.index'],

    ['label' => 'Carte interactive', 'route' => 'admin.interactive-map.index'],
    ['label' => 'Réservations', 'route' => 'admin.reservations.index'],
    ['label' => 'Contrats', 'route' => 'admin.contracts.index'],
    ['label' => 'Paiements', 'route' => 'admin.payments.index'],

    ['label' => 'Réclamations', 'route' => 'admin.complaints.index'],
    ['label' => 'Notifications', 'route' => 'admin.notifications.index'],
    ];

    $commercialLinks = [
    ['label' => 'Tableau de bord', 'route' => 'commercial.dashboard'],
    ['label' => 'Prospects', 'route' => 'commercial.prospects.index'],
    ['label' => 'Clients', 'route' => 'commercial.clients.index'],
    ['label' => 'Carte interactive', 'route' => 'commercial.interactive-map.index'],
    ['label' => 'Réservations', 'route' => 'commercial.reservations.index'],
    ['label' => 'Contrats', 'route' => 'commercial.contracts.index'],
    ['label' => 'Paiements', 'route' => 'commercial.payments.index'],
    ['label' => 'Réclamations', 'route' => 'commercial.complaints.index'],
    ['label' => 'Notifications', 'route' => 'commercial.notifications.index'],
    ];

    $clientLinks = [
    ['label' => 'Tableau de bord', 'route' => 'client.dashboard'],
    ['label' => 'Réservations', 'route' => 'client.reservations.index'],
    ['label' => 'Contrats', 'route' => 'client.contracts.index'],
    ['label' => 'Paiements', 'route' => 'client.payments.index'],
    ['label' => 'Réclamations', 'route' => 'client.complaints.index'],
    ['label' => 'Notifications', 'route' => 'client.notifications.index'],
    ];

    $links = match ($user->role) {
    'admin' => $adminLinks,
    'commercial' => $commercialLinks,
    'client' => $clientLinks,
    default => [],
    };
    @endphp

    @if(count($links))
    <div class="border-b border-gray-200 bg-white">
        <div class="hd-container">
            <nav class="flex justify-start gap-2 overflow-x-auto py-3 md:justify-center">
                @foreach($links as $link)
                @if(Route::has($link['route']))
                @php
                $params = $link['params'] ?? [];
                $fragment = $link['fragment'] ?? null;

                $href = route($link['route'], $params) . ($fragment ? '#' . $fragment : '');

                if ($fragment) {
                $isActive = request()->routeIs($link['route'])
                && $fragment === 'overview';
                } else {
                $isActive = request()->routeIs($link['route'])
                || request()->routeIs(str_replace('.index', '.*', $link['route']));
                }

                if (($link['params']['role'] ?? null) && request('role') === $link['params']['role']) {
                $isActive = true;
                }
                @endphp

                <a href="{{ $href }}"
   class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-bold transition
   {{ $isActive
       ? 'bg-[#FFE4E6] text-[#BE123C] shadow-sm'
       : 'text-gray-600 hover:bg-[#FFF1F2] hover:text-[#BE123C]' }}">
    {{ $link['label'] }}
</a>
                @endif
                @endforeach
            </nav>
        </div>
    </div>
    @endif
    @endauth

    <main>
        @yield('content')
    </main>

    <footer class="mt-16 bg-[#101310] text-white">
        <div class="hd-container py-12">
            <div class="grid gap-10 lg:grid-cols-4">
                <div>
                    <div class="inline-flex rounded-2xl bg-white px-4 py-3">
                        <img
                            src="{{ asset('images/hello-desk-logo.png') }}"
                            alt="Hello Desk"
                            class="h-20 w-auto">
                    </div>

                    <p class="mt-5 text-sm leading-6 text-gray-300">
                        Plateforme de gestion des espaces coworking, des clients,
                        des réservations, des contrats et des réclamations.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-white">
                        Accès rapides
                    </h3>

                    <ul class="mt-5 space-y-3 text-sm text-gray-300">
                        <li><a href="{{ route('home') }}" class="hover:text-white">Accueil</a></li>
                        <li><a href="#presentation" class="hover:text-white">Présentation</a></li>
                        <li><a href="#modules" class="hover:text-white">Modules</a></li>
                        <li><a href="#objectif" class="hover:text-white">Objectif</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-white">
                        Nos espaces
                    </h3>

                    <ul class="mt-5 space-y-3 text-sm text-gray-300">
                        <li>Bureaux privés</li>
                        <li>Positions en coworking</li>
                        <li>Salles de réunion</li>
                        <li>Centre Ville Casablanca</li>
                        <li>Sidi Maârouf - La Colline</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-white">
                        Nous contacter
                    </h3>

                    <div class="mt-5 space-y-4 text-sm leading-6 text-gray-300">
                        <p>
                            <span class="font-semibold text-white">Centre Ville :</span><br>
                            17 rue Oraibi El Jilali - Casablanca<br>
                            <a href="tel:+212662598745" class="hover:text-white">
                                +212 662 59 87 45
                            </a>
                        </p>

                        <p>
                            <span class="font-semibold text-white">Sidi Maârouf - La Colline :</span><br>
                            10 lotissement La Colline, immeuble SIGMA - Casablanca<br>
                            <a href="tel:+212662183173" class="hover:text-white">
                                +212 662 18 31 73
                            </a>
                        </p>

                        <div class="space-y-1">
                            <a href="mailto:contact@hellodesk.ma" class="block text-hd-green-soft hover:text-white">
                                contact@hellodesk.ma
                            </a>
                            <a href="mailto:hellodesk.centreville@gmail.com" class="block text-hd-green-soft hover:text-white">
                                hellodesk.centreville@gmail.com
                            </a>
                            <a href="mailto:hellodesk.sidimaarouf@gmail.com" class="block text-hd-green-soft hover:text-white">
                                hellodesk.sidimaarouf@gmail.com
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 border-t border-white/10 pt-6 text-center text-sm text-gray-400">
                © {{ date('Y') }} Hello Desk. Tous droits réservés.
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationDropdown = document.getElementById('notificationDropdown');

            if (!notificationDropdown) {
                return;
            }

            document.addEventListener('click', function(event) {
                if (!notificationDropdown.contains(event.target)) {
                    notificationDropdown.removeAttribute('open');
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    notificationDropdown.removeAttribute('open');
                }
            });
        });
    </script>

    @stack('scripts')
</body>

</html>