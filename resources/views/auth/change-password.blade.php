@extends('layouts.app')

@section('title', 'Changer le mot de passe - Hello Desk')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto flex min-h-[75vh] max-w-7xl items-center justify-center px-6 py-10">

        <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="mb-6 text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-[#284625]">
                    Sécurité du compte
                </p>

                <h1 class="mt-2 text-3xl font-bold text-slate-900">
                    Changer votre mot de passe
                </h1>

                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Vous utilisez un mot de passe temporaire. Pour protéger votre compte,
                    veuillez choisir un nouveau mot de passe.
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Veuillez vérifier les informations saisies.
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Nouveau mot de passe
                    </label>

                    <input type="password"
                           name="password"
                           required
                           class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                           placeholder="Minimum 8 caractères">

                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Confirmer le mot de passe
                    </label>

                    <input type="password"
                           name="password_confirmation"
                           required
                           class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                           placeholder="Répétez le nouveau mot de passe">
                </div>

                <button type="submit"
                        class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1f351d]">
                    Enregistrer le nouveau mot de passe
                </button>
            </form>
        </div>
    </div>
</div>
@endsection