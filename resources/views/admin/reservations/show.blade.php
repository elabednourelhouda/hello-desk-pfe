@extends('layouts.app')

@section('title', 'Reservation Details - Hello Desk')

@section('content')
@php
    $durationLabels = [
        'hourly' => 'Hourly',
        'daily' => 'Daily',
        'monthly' => 'Monthly',
        'custom' => 'Custom',
    ];

    $reservationStatusLabels = $reservationStatuses ?? [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
    ];

    $reservationStatusClasses = [
        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
        'in_progress' => 'bg-green-50 text-green-700 border-green-200',
        'completed' => 'bg-gray-100 text-gray-700 border-gray-200',
        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
        'expired' => 'bg-orange-50 text-orange-700 border-orange-200',
    ];

    $contractStatusLabels = [
        'draft' => 'Draft',
        'active' => 'Active',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
    ];

    $contractStatusClasses = [
        'draft' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'active' => 'bg-green-50 text-green-700 border-green-200',
        'expired' => 'bg-orange-50 text-orange-700 border-orange-200',
        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
    ];

    $reservationStatusClass = $reservationStatusClasses[$reservation->status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
    $contractStatus = $reservation->contract?->status;
    $contractStatusClass = $contractStatusClasses[$contractStatus] ?? 'bg-gray-100 text-gray-700 border-gray-200';
@endphp

<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <a href="{{ route('admin.reservations.index') }}"
               class="text-sm font-semibold text-[#284625] hover:underline">
                ← Back to reservations
            </a>

            <h1 class="mt-4 text-2xl font-bold text-gray-900">
                Reservation Details
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                View the reservation, its linked contract, and the next business actions.
            </p>
        </div>

        @if($reservation->space)
            <a href="{{ route('admin.interactive-map.index', [
                    'campus_id' => $reservation->space->campus_id,
                    'floor_id' => $reservation->space->floor_id,
                ]) }}"
               class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                View on map
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Reservation Information
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Main reservation period, client, space, and commercial details.
                        </p>
                    </div>

                    <span class="inline-flex w-fit rounded-full border px-3 py-1 text-xs font-semibold {{ $reservationStatusClass }}">
                        {{ $reservationStatusLabels[$reservation->status] ?? ucfirst($reservation->status) }}
                    </span>
                </div>

                <dl class="mt-6 grid gap-5 md:grid-cols-2">
                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Client</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $reservation->client?->full_name ?? 'Deleted client' }}
                        </dd>

                        @if($reservation->client?->email)
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $reservation->client->email }}
                            </p>
                        @endif
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Space</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $reservation->space?->name ?? 'Deleted space' }}
                        </dd>

                        <p class="mt-1 text-xs text-gray-500">
                            {{ $reservation->space?->code ?? 'No code' }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Campus</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $reservation->campus?->name ?? $reservation->space?->campus?->name ?? 'Not specified' }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Floor</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $reservation->floor?->name ?? $reservation->space?->floor?->name ?? 'Not specified' }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Start</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $reservation->starts_at?->format('d/m/Y H:i') }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">End</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $reservation->ends_at?->format('d/m/Y H:i') }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Duration type</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $durationLabels[$reservation->duration_type] ?? ucfirst($reservation->duration_type) }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Negotiated price</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">
                            {{ number_format($reservation->negotiated_price ?? 0, 2, '.', ' ') }} MAD
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Created by</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $reservation->creator?->name ?? 'Not specified' }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Created at</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            {{ $reservation->created_at?->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                </dl>

                @if($reservation->notes)
                    <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Internal notes
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            {{ $reservation->notes }}
                        </p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">
                    Business Flow
                </h2>

                <div class="mt-5 grid gap-3 md:grid-cols-3">
                    <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                        <p class="text-xs font-semibold uppercase text-green-700">
                            Step 1
                        </p>
                        <p class="mt-1 text-sm font-semibold text-green-900">
                            Reservation created
                        </p>
                    </div>

                    <div class="rounded-xl border {{ $reservation->contract ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-4">
                        <p class="text-xs font-semibold uppercase {{ $reservation->contract ? 'text-green-700' : 'text-red-700' }}">
                            Step 2
                        </p>
                        <p class="mt-1 text-sm font-semibold {{ $reservation->contract ? 'text-green-900' : 'text-red-900' }}">
                            Contract {{ $reservation->contract ? 'created' : 'missing' }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase text-gray-500">
                            Step 3
                        </p>
                        <p class="mt-1 text-sm font-semibold text-gray-800">
                            Payment deadlines next
                        </p>
                    </div>
                </div>

                <p class="mt-4 text-sm text-gray-500">
                    This flow shows that a reservation is not isolated. It is linked to a contract and will later be linked to payment deadlines.
                </p>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Linked Contract
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Every reservation must have a contract.
                        </p>
                    </div>
                </div>

                @if($reservation->contract)
                    <div class="mt-5 space-y-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Title</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $reservation->contract->title }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Period</p>
                            <p class="mt-1 text-sm text-gray-700">
                                {{ $reservation->contract->start_date?->format('d/m/Y') }}
                                →
                                {{ $reservation->contract->end_date?->format('d/m/Y') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Status</p>
                            <span class="mt-1 inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $contractStatusClass }}">
                                {{ $contractStatusLabels[$contractStatus] ?? ucfirst($contractStatus) }}
                            </span>
                        </div>

                        @if($reservation->contract->pdf_path)
                            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                                The contract PDF has been uploaded.
                            </div>

                            <a href="{{ asset('storage/' . $reservation->contract->pdf_path) }}"
                               target="_blank"
                               class="inline-flex w-full justify-center rounded-xl border border-[#284625] bg-white px-4 py-2 text-sm font-semibold text-[#284625] hover:bg-gray-50">
                                View PDF
                            </a>
                        @else
                            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-700">
                                The signed contract PDF has not been uploaded yet.
                            </div>
                        @endif

                        <a href="{{ route('admin.contracts.show', $reservation->contract) }}"
                           class="inline-flex w-full justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                            Manage contract
                        </a>
                    </div>
                @else
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        No contract linked. This should not happen because each reservation must have a contract.
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">
                    Next Action
                </h2>

                @if($reservation->contract && !$reservation->contract->pdf_path)
                    <p class="mt-2 text-sm text-gray-600">
                        Complete the contract by uploading the signed PDF from the contract page.
                    </p>

                    <a href="{{ route('admin.contracts.show', $reservation->contract) }}"
                       class="mt-4 inline-flex w-full justify-center rounded-xl bg-[#284625] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                        Upload contract PDF
                    </a>
                @elseif($reservation->contract && $reservation->contract->pdf_path)
                    <p class="mt-2 text-sm text-gray-600">
                        The contract is ready. The next module to complete is payment deadline tracking.
                    </p>
                @else
                    <p class="mt-2 text-sm text-gray-600">
                        A contract must be created for this reservation.
                    </p>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection