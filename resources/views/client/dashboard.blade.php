@extends('layouts.app')

@section('title', 'Espace client - Hello Desk')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-10">
    <h1 class="text-3xl font-semibold text-gray-900">
        Espace client
    </h1>

    <p class="mt-2 text-gray-600">
        Ici, le client pourra consulter ses réservations, ses contrats, ses notifications et ses réclamations.
    </p>

    <form action="{{ route('logout') }}" method="POST" class="mt-6">
        @csrf
        <button class="rounded-xl bg-[#284625] px-5 py-3 text-sm font-semibold text-white hover:bg-[#20391f]">
            Déconnexion
        </button>
    </form>
</div>
@endsection