@php
    $isAdmin = $admin ?? false;
@endphp

<div id="{{ $isAdmin ? 'admin-coworking-grid-readonly' : 'commercial-coworking-grid' }}"
     class="interactive-map-grid grid gap-2 rounded-2xl border border-slate-300 bg-slate-100 p-2">
    @foreach(range(1, 5) as $row)
        @foreach(range(1, 9) as $column)
            <div
                aria-hidden="true"
                class="pointer-events-none rounded-lg border border-slate-200 bg-white/70"
                style="grid-column: {{ $column }}; grid-row: {{ $row }};"
            ></div>
        @endforeach
    @endforeach

    @foreach($spaces as $space)
        @php
            $displayStatus = $space->display_status ?? 'Disponible';
            $color = $space->display_status_color ?? '#e5e7eb';
            $tileStyle = 'background-color: ' . $color . '1a; border-color: ' . $color . ';';
            $canReserve = $isAdmin
                ? in_array(mb_strtolower($displayStatus), ['disponible', 'available'], true)
                : (bool) ($space->can_reserve ?? false);
        @endphp

        <button type="button"
                onclick="selectSpace(this)"
                data-id="{{ $space->id }}"
                data-name="{{ $space->name }}"
                data-code="{{ $space->code ?? '' }}"
                data-status="{{ $displayStatus }}"
                data-capacity="{{ $space->capacity ?? 'Non précisée' }}"
                data-price-hour="{{ $space->price_per_hour ?? '' }}"
                data-price-day="{{ $space->price_per_day ?? '' }}"
                data-price-month="{{ $space->price_per_month ?? '' }}"
                data-grid-column="{{ $space->grid_column }}"
                data-grid-row="{{ $space->grid_row }}"
                data-grid-width="{{ $space->grid_width }}"
                data-grid-height="{{ $space->grid_height }}"
                data-can-manage="{{ ($space->can_manage ?? false) ? '1' : '0' }}"
                data-reserve-url="{{ $isAdmin ? ($canReserve ? route('admin.reservations.create', ['space_id' => $space->id]) : '') : ($space->reserve_url ?? '') }}"
                data-style="{{ $tileStyle }}"
                style="grid-column: {{ $space->grid_column }} / span {{ $space->grid_width }}; grid-row: {{ $space->grid_row }} / span {{ $space->grid_height }};"
                class="space-tile z-10 flex min-h-[64px] flex-col justify-between rounded-xl border-2 p-3 text-left text-xs shadow-sm transition hover:-translate-y-0.5 hover:shadow-md text-gray-800">
            <div>
                <p class="font-bold">{{ $space->name }}</p>
                <p class="mt-1 text-[10px] opacity-80">{{ $space->code ?? 'Code non défini' }}</p>
            </div>

            <div class="mt-2">
                <span class="rounded-full bg-white/80 px-2 py-0.5 text-[10px] font-bold">{{ $displayStatus }}</span>
                @if(! $isAdmin && ! ($space->can_manage ?? false))
                    <span class="mt-1 inline-flex rounded-full bg-white/80 px-2 py-0.5 text-[10px] font-bold text-gray-600">Lecture seule</span>
                @endif
            </div>

        </button>
    @endforeach
</div>
