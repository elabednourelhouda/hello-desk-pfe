@extends('layouts.auth')

@section('title', 'Connexion - Hello Desk')

@section('content')
<a
    href="{{ route('home') }}"
    class="fixed left-6 top-6 inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:border-[#284625]/30 hover:text-[#284625]"
>
    <span>←</span>
    <span>Retour à l’accueil</span>
</a>
<section class="min-h-[70vh] bg-[#f7f8f5] py-14">
    <div class="mx-auto flex max-w-6xl items-center justify-center px-4">
        <div class="w-full max-w-md rounded-3xl border border-gray-200 bg-white p-8 shadow-sm sm:p-10">

            {{-- Logo --}}
            <div class="mb-8 text-center">
                <img
                    src="{{ asset('images/hello-desk-logo.png') }}"
                    alt="Hello Desk"
                    class="mx-auto h-20 w-auto"
                >

                <h1 class="mt-6 text-2xl font-semibold text-gray-900">
                    Connexion
                </h1>

                <p class="mt-2 text-sm leading-6 text-gray-500">
                    Accédez à votre espace Hello Desk.
                </p>
            </div>

            {{-- Success message --}}
            @if (session('success'))
                <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700">
                        Adresse email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="exemple@email.com"
                        required
                        autofocus
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-[#284625] focus:ring-4 focus:ring-[#284625]/10"
                    >

                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-gray-700">
                        Mot de passe
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Votre mot de passe"
                        required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-[#284625] focus:ring-4 focus:ring-[#284625]/10"
                    >

                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input
                            type="checkbox"
                            name="remember"
                            class="rounded border-gray-300 text-[#284625] focus:ring-[#284625]"
                        >
                        Se souvenir de moi
                    </label>

                    <span class="text-sm text-gray-400 cursor-not-allowed">
                        Mot de passe oublié ?
                    </span>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#20391f] focus:outline-none focus:ring-4 focus:ring-[#284625]/20"
                >
                    Se connecter
                </button>
            </form>

            {{-- Small note --}}
            <p class="mt-7 text-center text-xs leading-5 text-gray-500">
                Les comptes clients sont créés par l’équipe Hello Desk après validation.
            </p>
        </div>
    </div>
</section>
@endsection