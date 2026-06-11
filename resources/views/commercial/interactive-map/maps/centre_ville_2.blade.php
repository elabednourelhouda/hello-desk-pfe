@php
    $findSpace = function (array $keywords) use ($spaces) {
        return $spaces->first(function ($space) use ($keywords) {
            $haystack = mb_strtolower(
                ($space->name ?? '') . ' ' .
                ($space->code ?? '') . ' ' .
                ($space->internal_code ?? '')
            );

            foreach ($keywords as $keyword) {
                if (str_contains($haystack, mb_strtolower($keyword))) {
                    return true;
                }
            }

            return false;
        });
    };

    $statusKey = function ($space) {
        if (! $space) {
            return 'empty';
        }

        $displayStatus = $space->display_status ?? $space->status ?? 'Disponible';
        $status = mb_strtolower($displayStatus);

        return match ($status) {
            'disponible', 'available' => 'available',
            'réservé', 'reserve', 'reserved' => 'reserved',
            'occupé', 'occupe', 'occupied' => 'occupied',
            'indisponible', 'unavailable' => 'unavailable',
            'maintenance', 'en maintenance' => 'maintenance',
            default => 'empty',
        };
    };

    $statusLabel = fn ($space) => $space?->display_status ?? $space?->status ?? 'Non configuré';

    $canReserveSpace = function ($space) use ($statusKey) {
        return $space && $statusKey($space) === 'available';
    };

    $b1 = $findSpace(['Bureau B1', 'B1', 'CV-2-B1']);
    $b2 = $findSpace(['Bureau B2', 'B2', 'CV-2-B2']);
    $b3 = $findSpace(['Bureau B3', 'B3', 'CV-2-B3']);
    $b4 = $findSpace(['Bureau B4', 'B4', 'CV-2-B4']);
    $b5 = $findSpace(['Bureau B5', 'B5', 'CV-2-B5']);
    $b6 = $findSpace(['Bureau B6', 'B6', 'CV-2-B6']);
    $b7 = $findSpace(['Bureau B7', 'B7', 'CV-2-B7']);

    $p1 = $findSpace(['Position P1', 'P1', 'CV-2-P1']);
    $p2 = $findSpace(['Position P2', 'P2', 'CV-2-P2']);
    $p3 = $findSpace(['Position P3', 'P3', 'CV-2-P3']);
    $p4 = $findSpace(['Position P4', 'P4', 'CV-2-P4']);
    $p5 = $findSpace(['Position P5', 'P5', 'CV-2-P5']);
    $p6 = $findSpace(['Position P6', 'P6', 'CV-2-P6']);

    $meeting = $findSpace(['Salle réunion', 'Salle de réunion', 'SR1', 'CV-2-SR1']);

    $rooms = [
        ['label' => 'Bureau B1', 'space' => $b1],
        ['label' => 'Bureau B2', 'space' => $b2],
        ['label' => 'Bureau B3', 'space' => $b3],
        ['label' => 'Bureau B4', 'space' => $b4],
        ['label' => 'Bureau B5', 'space' => $b5],
        ['label' => 'Bureau B6', 'space' => $b6],
        ['label' => 'Bureau B7', 'space' => $b7],
        ['label' => 'Salle réunion', 'space' => $meeting],
    ];

    $positions = [
        ['label' => 'Position P1', 'space' => $p1],
        ['label' => 'Position P2', 'space' => $p2],
        ['label' => 'Position P3', 'space' => $p3],
        ['label' => 'Position P4', 'space' => $p4],
        ['label' => 'Position P5', 'space' => $p5],
        ['label' => 'Position P6', 'space' => $p6],
    ];
@endphp

<style>
    .hd-plan {
        border: 1px solid #cbd5e1;
        border-radius: 28px;
        background: #f1f5f9;
        padding: 18px;
    }

    .hd-common-grid {
        display: grid;
        grid-template-columns: 1.2fr 1.6fr 1.4fr 1.2fr 1.2fr;
        gap: 12px;
    }

    .hd-zone {
        min-height: 68px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        text-align: center;
        font-size: 12px;
        font-weight: 900;
    }

    .hd-reception {
        background: #f5f3ff;
        border-color: #ddd6fe;
        color: #5b21b6;
    }

    .hd-entrance {
        border-style: dashed;
        color: #475569;
    }

    .hd-kitchen {
        background: #fffbeb;
        border-color: #fde68a;
        color: #92400e;
    }

    .hd-bathroom {
        background: #ecfeff;
        border-color: #a5f3fc;
        color: #155e75;
    }

    .hd-technical {
        background: #f8fafc;
        color: #64748b;
    }

    .hd-hallway {
        margin: 14px 0;
        min-height: 42px;
        border-style: dashed;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .18em;
        font-size: 10px;
    }

    .hd-main-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.8fr) minmax(280px, .9fr);
        gap: 16px;
        align-items: stretch;
    }

    .hd-office-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .hd-open-space {
        border: 2px solid #a7f3d0;
        border-radius: 24px;
        background: #ffffff;
        padding: 14px;
    }

    .hd-open-title {
        border-radius: 16px;
        background: #ecfdf5;
        padding: 12px;
        color: #065f46;
    }

    .hd-open-title strong {
        display: block;
        font-size: 14px;
        font-weight: 900;
    }

    .hd-open-title span {
        display: block;
        margin-top: 2px;
        font-size: 11px;
        font-weight: 800;
        color: #047857;
    }

    .hd-position-grid {
        margin-top: 12px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .hd-space-tile {
        min-height: 108px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border-radius: 18px;
        border: 1px solid #d1d5db;
        padding: 12px;
        text-align: left;
        font-size: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
        transition: .15s ease;
    }

    .hd-space-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .hd-space-name {
        font-size: 13px;
        font-weight: 900;
        line-height: 1.2;
    }

    .hd-space-code {
        margin-top: 5px;
        font-size: 11px;
        opacity: .75;
    }

    .hd-status-pill {
        width: fit-content;
        border-radius: 999px;
        background: rgba(255, 255, 255, .85);
        padding: 5px 9px;
        font-size: 11px;
        font-weight: 900;
    }

    .hd-status-available {
        background: #ecfdf5;
        border-color: #86efac;
        color: #065f46;
    }

    .hd-status-reserved {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #075985;
    }

    .hd-status-occupied {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
    }

    .hd-status-unavailable {
        background: #fff1f2;
        border-color: #fda4af;
        color: #be123c;
    }

    .hd-status-maintenance {
        background: #fff7ed;
        border-color: #fdba74;
        color: #c2410c;
    }

    .hd-status-empty {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #94a3b8;
    }

    @media (max-width: 900px) {
        .hd-common-grid,
        .hd-main-layout,
        .hd-office-grid {
            grid-template-columns: 1fr;
        }

        .hd-position-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $renderTile = function ($space, string $fallbackLabel) use ($statusKey, $statusLabel, $canReserveSpace) {
        $key = $statusKey($space);
        $label = $statusLabel($space);
        $canReserve = $canReserveSpace($space);
@endphp

        <button type="button"
                @if($space) onclick="selectSpace(this)" @endif
                data-id="{{ $space?->id }}"
                data-name="{{ $space?->name ?? $fallbackLabel }}"
                data-code="{{ $space?->code ?? $space?->internal_code ?? 'Non créé' }}"
                data-status="{{ $label }}"
                data-capacity="{{ $space?->capacity ?? 'Non précisée' }}"
                data-price-hour="{{ $space?->price_per_hour ?? '' }}"
                data-price-day="{{ $space?->price_per_day ?? '' }}"
                data-price-month="{{ $space?->price_per_month ?? '' }}"
                data-reserve-url="{{ $space && $canReserve ? route('commercial.reservations.create', ['space_id' => $space->id]) : '' }}"
                class="space-tile hd-space-tile hd-status-{{ $key }}">
            <div>
                <p class="hd-space-name">{{ $space?->name ?? $fallbackLabel }}</p>
                <p class="hd-space-code">{{ $space?->code ?? $space?->internal_code ?? 'Non créé' }}</p>
            </div>

            <span class="hd-status-pill">{{ $label }}</span>
        </button>

@php
    };
@endphp

<div class="hd-plan">
    <div class="hd-common-grid">
        <div class="hd-zone hd-entrance">Entrée</div>
        <div class="hd-zone hd-reception">Réception</div>
        <div class="hd-zone hd-kitchen">Kitchenette</div>
        <div class="hd-zone hd-bathroom">Sanitaires</div>
        <div class="hd-zone hd-technical">Local technique</div>
    </div>

    <div class="hd-zone hd-hallway">Couloir principal</div>

    <div class="hd-main-layout">
        <div>
            <div class="hd-office-grid">
                @foreach($rooms as $room)
                    {{ $renderTile($room['space'], $room['label']) }}
                @endforeach
            </div>

            <div class="hd-zone hd-hallway">Circulation secondaire</div>
        </div>

        <div class="hd-open-space">
            <div class="hd-open-title">
                <strong>Open space</strong>
                <span>Positions coworking</span>
            </div>

            <div class="hd-position-grid">
                @foreach($positions as $position)
                    {{ $renderTile($position['space'], $position['label']) }}
                @endforeach
            </div>
        </div>
    </div>
</div>