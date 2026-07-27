@php
    /*
    |--------------------------------------------------------------------------
    | Centre Ville - 2ème étage map
    |--------------------------------------------------------------------------
    | This specific floor map places spaces by their code.
    */

    $spaceByCode = $spaces->keyBy('code');

    $tiles = [
        [
            'code' => 'CV-2-B1',
            'label' => 'Bureau B1',
            'position' => 'left-[4%] top-[6%] w-[18%] h-[18%]',
        ],
        [
            'code' => 'CV-2-B2',
            'label' => 'Bureau B2',
            'position' => 'left-[24%] top-[6%] w-[18%] h-[18%]',
        ],
        [
            'code' => 'CV-2-SR1',
            'label' => 'Salle réunion',
            'position' => 'left-[45%] top-[6%] w-[27%] h-[18%]',
        ],
        [
            'code' => 'CV-2-B3',
            'label' => 'Bureau B3',
            'position' => 'left-[75%] top-[6%] w-[20%] h-[18%]',
        ],
        [
            'code' => 'CV-2-P1',
            'label' => 'Position P1',
            'position' => 'left-[4%] top-[39%] w-[14%] h-[15%]',
        ],
        [
            'code' => 'CV-2-P2',
            'label' => 'Position P2',
            'position' => 'left-[20%] top-[39%] w-[14%] h-[15%]',
        ],
        [
            'code' => 'CV-2-P3',
            'label' => 'Position P3',
            'position' => 'left-[36%] top-[39%] w-[14%] h-[15%]',
        ],
        [
            'code' => 'CV-2-P4',
            'label' => 'Position P4',
            'position' => 'left-[52%] top-[39%] w-[14%] h-[15%]',
        ],
        [
            'code' => 'CV-2-P5',
            'label' => 'Position P5',
            'position' => 'left-[68%] top-[39%] w-[14%] h-[15%]',
        ],
        [
            'code' => 'CV-2-P6',
            'label' => 'Position P6',
            'position' => 'left-[84%] top-[39%] w-[12%] h-[15%]',
        ],
        [
            'code' => 'CV-2-B4',
            'label' => 'Bureau B4',
            'position' => 'left-[4%] top-[72%] w-[22%] h-[20%]',
        ],
        [
            'code' => 'CV-2-B5',
            'label' => 'Bureau B5',
            'position' => 'left-[29%] top-[72%] w-[22%] h-[20%]',
        ],
        [
            'code' => 'CV-2-B6',
            'label' => 'Bureau B6',
            'position' => 'left-[54%] top-[72%] w-[20%] h-[20%]',
        ],
        [
            'code' => 'CV-2-B7',
            'label' => 'Bureau B7',
            'position' => 'left-[77%] top-[72%] w-[19%] h-[20%]',
        ],
    ];

    $plannedCodes = collect($tiles)->pluck('code')->toArray();

    $unplacedSpaces = $spaces->filter(function ($space) use ($plannedCodes) {
        return ! in_array($space->code, $plannedCodes);
    });

    // Colors are now admin-configurable (Configuration -> Statuts
    // d'espace), so they can no longer be a fixed Tailwind class
    // match() — Tailwind's build-time scanner can't know an arbitrary
    // hex value ahead of time. The color itself is resolved once per
    // space in InteractiveMapController@index (display_status_color);
    // here we only turn it into an inline style string.
    $statusStyle = function ($space) {
        $color = $space->display_status_color ?? '#e5e7eb';

        return 'background-color: ' . $color . '1a; border-color: ' . $color . ';';
    };

    $canReserveSpace = function ($space) {
        $displayStatus = $space->display_status ?? 'Disponible';

        return in_array(mb_strtolower($displayStatus), ['disponible', 'available'], true);
    };
@endphp

<div class="space-y-5">
    <div class="relative min-h-[590px] overflow-hidden rounded-2xl border-2 border-slate-300 bg-slate-100 p-4 shadow-inner">
        <div class="absolute inset-4 rounded-xl border-4 border-slate-400 bg-white"></div>

        <div class="absolute left-[4%] top-[29%] h-[7%] w-[92%] rounded-md border border-slate-200 bg-slate-50">
            <div class="flex h-full items-center justify-center text-xs font-semibold uppercase tracking-wide text-slate-400">
                Couloir principal
            </div>
        </div>

        <div class="absolute left-[3%] top-[36%] h-[23%] w-[94%] rounded-xl border border-dashed border-slate-300 bg-slate-50/70">
            <div class="absolute left-4 top-2 text-xs font-bold uppercase tracking-wide text-slate-400">
                Zone open space
            </div>
        </div>

        <div class="absolute left-[33%] top-[61%] h-[8%] w-[34%] rounded-xl border border-slate-200 bg-slate-50">
            <div class="flex h-full items-center justify-center text-xs font-semibold text-slate-500">
                Accueil / espace attente
            </div>
        </div>

        <div class="absolute left-[4%] top-[66%] h-[4%] w-[92%] rounded-md bg-slate-100"></div>

        <div class="absolute bottom-2 left-1/2 z-20 -translate-x-1/2 rounded-t-xl border-x-2 border-t-2 border-slate-400 bg-white px-8 py-2 text-xs font-bold uppercase tracking-wide text-slate-500">
            Entrée
        </div>

        <div class="absolute right-[4%] top-[61%] h-[8%] w-[14%] rounded-xl border border-slate-300 bg-slate-100">
            <div class="flex h-full items-center justify-center text-xs font-semibold text-slate-500">
                Escalier
            </div>
        </div>

        @foreach($tiles as $tile)
            @php
                $space = $spaceByCode->get($tile['code']);
            @endphp

            @if($space)
                @php
                    $displayStatus = $space->display_status ?? 'Disponible';
                    $statusStyleAttr = $statusStyle($space);
                    $canReserve = $canReserveSpace($space);
                @endphp

                <button type="button"
                        onclick="selectSpace(this)"
                        data-id="{{ $space->id }}"
                        data-name="{{ $space->name }}"
                        data-code="{{ $space->code }}"
                        data-status="{{ $displayStatus }}"
                        data-capacity="{{ $space->capacity ?? 'Non précisée' }}"
                        data-price-hour="{{ $space->price_per_hour ?? '' }}"
                        data-price-day="{{ $space->price_per_day ?? '' }}"
                        data-price-month="{{ $space->price_per_month ?? '' }}"
                        data-reserve-url="{{ $canReserve ? route('admin.reservations.create', ['space_id' => $space->id]) : '' }}"
                        style="{{ $statusStyleAttr }}"
                        class="space-tile absolute z-10 flex flex-col justify-between rounded-lg border-2 p-3 text-left text-xs shadow-sm transition text-gray-800 {{ $tile['position'] }}">
                    <div>
                        <p class="truncate font-bold">
                            {{ $space->name }}
                        </p>

                        <p class="mt-1 truncate opacity-75">
                            {{ $space->spaceType?->name ?? 'Espace' }}
                        </p>
                    </div>

                    <div class="mt-2">
                        <span class="inline-flex max-w-full rounded-full bg-white/75 px-2 py-1 text-[11px] font-bold">
                            {{ $displayStatus }}
                        </span>
                    </div>
                </button>
            @else
                <div class="absolute z-10 flex flex-col justify-between rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 p-3 text-left text-xs text-slate-400 {{ $tile['position'] }}">
                    <div>
                        <p class="font-bold">
                            {{ $tile['label'] }}
                        </p>

                        <p class="mt-1">
                            {{ $tile['code'] }}
                        </p>
                    </div>

                    <span class="mt-2 rounded-full bg-white px-2 py-1 text-[11px] font-semibold">
                        À créer
                    </span>
                </div>
            @endif
        @endforeach
    </div>

    @if($unplacedSpaces->count())
        <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4">
            <p class="text-sm font-bold text-yellow-800">
                Espaces non placés sur cette carte
            </p>

            <p class="mt-1 text-sm text-yellow-700">
                Ces espaces existent dans la base de données, mais leurs codes ne sont pas encore ajoutés dans le plan du 2ème étage.
            </p>

            <div class="mt-4 grid gap-3 md:grid-cols-3">
                @foreach($unplacedSpaces as $space)
                    @php
                        $displayStatus = $space->display_status ?? 'Disponible';
                        $statusStyleAttr = $statusStyle($space);
                        $canReserve = $canReserveSpace($space);
                    @endphp

                    <button type="button"
                            onclick="selectSpace(this)"
                            data-id="{{ $space->id }}"
                            data-name="{{ $space->name }}"
                            data-code="{{ $space->code }}"
                            data-status="{{ $displayStatus }}"
                            data-capacity="{{ $space->capacity ?? 'Non précisée' }}"
                            data-price-hour="{{ $space->price_per_hour ?? '' }}"
                            data-price-day="{{ $space->price_per_day ?? '' }}"
                            data-price-month="{{ $space->price_per_month ?? '' }}"
                            data-reserve-url="{{ $canReserve ? route('admin.reservations.create', ['space_id' => $space->id]) : '' }}"
                            style="{{ $statusStyleAttr }}"
                            class="space-tile rounded-xl border-2 p-4 text-left text-sm shadow-sm transition text-gray-800">
                        <p class="font-bold">{{ $space->name }}</p>
                        <p class="mt-1 text-xs opacity-75">{{ $space->code }}</p>

                        <span class="mt-3 inline-flex rounded-full bg-white/75 px-2 py-1 text-xs font-bold">
                            {{ $displayStatus }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>