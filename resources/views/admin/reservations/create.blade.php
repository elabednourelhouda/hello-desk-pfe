@extends('layouts.app')

@section('title', 'Create Reservation - Hello Desk')

@section('content')
<div class="mx-auto max-w-5xl px-6 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.interactive-map.index') }}"
           class="text-sm font-semibold text-[#284625] hover:underline">
            ← Back to interactive map
        </a>

        <h1 class="mt-4 text-2xl font-bold text-gray-900">
            Create Reservation
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Create a reservation for a client and prepare its draft contract automatically.
        </p>
    </div>

    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Please correct the following errors:</p>

            <ul class="mt-2 list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($selectedSpace)
        <section class="mb-6 rounded-2xl border border-[#284625]/20 bg-[#284625]/5 p-6 shadow-sm">
            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-[#284625]">
                        Selected space from map
                    </p>

                    <h2 class="mt-2 text-xl font-bold text-gray-900">
                        {{ $selectedSpace->name }}
                    </h2>

                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                        <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                            Code: {{ $selectedSpace->code }}
                        </span>

                        <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                            Type: {{ $selectedSpace->spaceType?->name ?? 'Not specified' }}
                        </span>

                        <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                            Campus: {{ $selectedSpace->campus?->name ?? 'Not specified' }}
                        </span>

                        <span class="rounded-full bg-white px-3 py-1 text-gray-700">
                            Floor: {{ $selectedSpace->floor?->name ?? 'Not specified' }}
                        </span>

                        <span class="rounded-full bg-green-50 px-3 py-1 text-green-700">
                            Status: {{ ucfirst($selectedSpace->status) }}
                        </span>
                    </div>

                    <div class="mt-4 grid gap-3 text-sm text-gray-700 sm:grid-cols-3">
                        <div class="rounded-xl bg-white px-4 py-3">
                            <p class="text-xs font-semibold uppercase text-gray-400">Hourly price</p>
                            <p class="mt-1 font-bold">
                                {{ $selectedSpace->price_hourly ?? 0 }} MAD
                            </p>
                        </div>

                        <div class="rounded-xl bg-white px-4 py-3">
                            <p class="text-xs font-semibold uppercase text-gray-400">Daily price</p>
                            <p class="mt-1 font-bold">
                                {{ $selectedSpace->price_daily ?? 0 }} MAD
                            </p>
                        </div>

                        <div class="rounded-xl bg-white px-4 py-3">
                            <p class="text-xs font-semibold uppercase text-gray-400">Monthly price</p>
                            <p class="mt-1 font-bold">
                                {{ $selectedSpace->price_monthly ?? 0 }} MAD
                            </p>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.interactive-map.index', [
                    'campus_id' => $selectedSpace->campus_id,
                    'floor_id' => $selectedSpace->floor_id,
                ]) }}"
                   class="rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Change space
                </a>
            </div>
        </section>
    @endif

    <form method="POST"
          action="{{ route('admin.reservations.store') }}"
          class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Client
                </label>

                <select name="client_id"
                        required
                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <option value="">Select a client</option>

                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>
                            {{ $client->full_name }} — {{ $client->email }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Space
                </label>

                @if($selectedSpace)
                    <input type="hidden"
                           name="space_id"
                           value="{{ old('space_id', $selectedSpace->id) }}">

                    <div class="flex h-12 items-center rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-gray-700">
                        {{ $selectedSpace->name }} — {{ $selectedSpace->code }}
                    </div>
                @else
                    <select name="space_id"
                            required
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                        <option value="">Select a space</option>

                        @foreach($spaces as $space)
                            @php
                                $spaceStatus = mb_strtolower($space->status ?? 'available');

                                $isNotReservable = in_array($spaceStatus, [
                                    'occupied',
                                    'unavailable',
                                    'maintenance',
                                    'in maintenance',
                                    'occupé',
                                    'occupe',
                                    'indisponible',
                                    'en maintenance',
                                ]);
                            @endphp

                            <option value="{{ $space->id }}"
                                    @selected(old('space_id') == $space->id)
                                    @disabled($isNotReservable)>
                                {{ $space->name }}
                                —
                                {{ $space->code }}
                                —
                                {{ ucfirst($space->status) }}
                                @if($isNotReservable)
                                    (not reservable)
                                @endif
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Start date and time
                </label>

                <input type="datetime-local"
                       name="starts_at"
                       value="{{ old('starts_at') }}"
                       required
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    End date and time
                </label>

                <input type="datetime-local"
                       name="ends_at"
                       value="{{ old('ends_at') }}"
                       required
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Duration type
                </label>

                <select name="duration_type"
                        required
                        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    @foreach($durationTypes ?? [
                        'hourly' => 'Hourly',
                        'daily' => 'Daily',
                        'monthly' => 'Monthly',
                        'custom' => 'Custom',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('duration_type', 'custom') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">
                    Negotiated price
                </label>

                <input type="number"
                       step="0.01"
                       min="0"
                       name="negotiated_price"
                       value="{{ old('negotiated_price', 0) }}"
                       class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

                <p class="mt-2 text-xs text-gray-500">
                    This price can be different from the official space price.
                </p>
            </div>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Contract title
            </label>

            <input type="text"
                   name="contract_title"
                   value="{{ old('contract_title') }}"
                   placeholder="Example: Private office contract - June 2026"
                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

            <p class="mt-2 text-xs text-gray-500">
                A draft contract will be created automatically with this reservation.
                The signed PDF can be uploaded later from the contract page.
            </p>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Internal notes
            </label>

            <textarea name="notes"
                      rows="4"
                      class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                      placeholder="Commercial notes, special conditions, internal details...">{{ old('notes') }}</textarea>
        </div>

        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <p class="font-semibold">Important business rule</p>
            <p class="mt-1">
                Every reservation must be linked to a contract. For this reason, the system will automatically create a draft contract after saving the reservation.
            </p>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ $selectedSpace
                    ? route('admin.interactive-map.index', ['campus_id' => $selectedSpace->campus_id, 'floor_id' => $selectedSpace->floor_id])
                    : route('admin.reservations.index') }}"
               class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-300 px-5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Cancel
            </a>

            <button type="submit"
                    class="inline-flex h-12 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                Create reservation
            </button>
        </div>
    </form>
</div>
@endsection