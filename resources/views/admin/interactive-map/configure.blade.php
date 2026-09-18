@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Administration</p>
            <h1 class="mt-1 text-2xl font-black text-slate-900">Configurer la disposition</h1>
            <p class="mt-1 text-sm text-slate-500">Déplacez ou redimensionnez les espaces par cellules dans la grille 9 × 5.</p>
        </div>
        <a href="{{ route('admin.interactive-map.index', ['campus_id' => $selectedCampusId, 'floor_id' => $selectedFloorId]) }}"
            class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">
            Retour à Vue Espace
        </a>
    </div>

    <form method="GET" action="{{ route('admin.interactive-map.configure') }}" class="mb-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-3">
        <label class="text-sm font-bold text-slate-700">
            Site
            <select name="campus_id" onchange="this.form.submit()" class="mt-2 w-full rounded-xl border-slate-300">
                @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}" @selected($campus->id == $selectedCampusId)>{{ $campus->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm font-bold text-slate-700">
            Étage
            <select name="floor_id" onchange="this.form.submit()" class="mt-2 w-full rounded-xl border-slate-300">
                @foreach($floors as $floor)
                    <option value="{{ $floor->id }}" @selected($floor->id == $selectedFloorId)>{{ $floor->name }}</option>
                @endforeach
            </select>
        </label>
        <div class="flex items-end">
            <div class="w-full">
                <button id="save-layout" type="button" class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-black text-white hover:bg-indigo-500">
                Enregistrer la disposition
                </button>
                <p id="layout-save-feedback" class="mt-2 hidden text-center text-xs font-bold"></p>
            </div>
        </div>
    </form>

    @php
        $statusColors = $statuses->keyBy(function ($status) {
            return mb_strtolower($status->code);
        });
    @endphp

    <div id="layout-editor"
        data-save-url="{{ route('admin.interactive-map.layout.batch') }}"
        class="interactive-map-grid grid gap-2 rounded-2xl border border-slate-300 bg-slate-100 p-2">
        @foreach(range(1, 5) as $row)
            @foreach(range(1, 9) as $column)
                <div aria-hidden="true" class="pointer-events-none rounded-lg border border-slate-200 bg-white/70"
                    style="grid-column: {{ $column }}; grid-row: {{ $row }};"></div>
            @endforeach
        @endforeach
        @foreach($spaces as $space)
            @php
                $status = $statusColors->get(mb_strtolower($space->status ?? 'available'));
                $background = $status?->toRgba(0.12) ?: 'rgba(100, 116, 139, 0.12)';
                $border = $status?->toRgba(1) ?: 'rgb(100, 116, 139)';
            @endphp
            <div class="layout-tile relative z-10 flex min-h-[80px] cursor-move flex-col justify-between rounded-xl border-2 p-2 text-xs text-slate-800 shadow-sm"
                data-id="{{ $space->id }}" data-grid-column="{{ $space->grid_column }}" data-grid-row="{{ $space->grid_row }}"
                data-grid-width="{{ $space->grid_width }}" data-grid-height="{{ $space->grid_height }}"
                style="grid-column: {{ $space->grid_column }} / span {{ $space->grid_width }}; grid-row: {{ $space->grid_row }} / span {{ $space->grid_height }}; background-color: {{ $background }}; border-color: {{ $border }};">
                <div class="font-bold">{{ $space->name }}</div>
                <select class="layout-status mt-1 w-full rounded border-slate-300 bg-white/80 px-1 py-0.5 text-[10px]" aria-label="Statut de {{ $space->name }}">
                    @foreach($statuses as $option)
                        <option value="{{ $option->code }}" @selected($option->code === $space->status)>{{ $option->name }}</option>
                    @endforeach
                </select>
                <span class="resize-handle absolute bottom-1 right-1 h-3 w-3 cursor-se-resize rounded-sm bg-black/25" aria-label="Redimensionner"></span>
            </div>
        @endforeach
    </div>
</div>

<script>
(() => {
    const grid = document.getElementById('layout-editor');
    const saveButton = document.getElementById('save-layout');
    if (!grid || !saveButton) return;
    const values = tile => ({
        column: Number(tile.dataset.gridColumn), row: Number(tile.dataset.gridRow),
        width: Number(tile.dataset.gridWidth), height: Number(tile.dataset.gridHeight)
    });
    const setValues = (tile, next) => {
        tile.dataset.gridColumn = next.column; tile.dataset.gridRow = next.row;
        tile.dataset.gridWidth = next.width; tile.dataset.gridHeight = next.height;
        tile.style.gridColumn = `${next.column} / span ${next.width}`;
        tile.style.gridRow = `${next.row} / span ${next.height}`;
    };
    const overlaps = (a, b) => !(a.column + a.width <= b.column || b.column + b.width <= a.column || a.row + a.height <= b.row || b.row + b.height <= a.row);
    const valid = (tile, next) => next.column >= 1 && next.row >= 1 && next.width >= 1 && next.height >= 1
        && next.column + next.width - 1 <= 9 && next.row + next.height - 1 <= 5
        && [...grid.querySelectorAll('.layout-tile')].filter(other => other !== tile).every(other => !overlaps(next, values(other)));
    const cellAt = event => {
        const rect = grid.getBoundingClientRect(), style = getComputedStyle(grid);
        const gapX = parseFloat(style.columnGap) || 0, gapY = parseFloat(style.rowGap) || 0;
        const borderLeft = parseFloat(style.borderLeftWidth) || 0, borderTop = parseFloat(style.borderTopWidth) || 0;
        const contentLeft = borderLeft + parseFloat(style.paddingLeft);
        const contentTop = borderTop + parseFloat(style.paddingTop);
        const contentWidth = grid.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
        const contentHeight = grid.clientHeight - parseFloat(style.paddingTop) - parseFloat(style.paddingBottom);
        const cellWidth = (contentWidth - gapX * 8) / 9;
        const cellHeight = (contentHeight - gapY * 4) / 5;
        return {
            column: Math.min(9, Math.max(1, Math.floor((event.clientX - rect.left - contentLeft) / (cellWidth + gapX)) + 1)),
            row: Math.min(5, Math.max(1, Math.floor((event.clientY - rect.top - contentTop) / (cellHeight + gapY)) + 1))
        };
    };
    grid.querySelectorAll('.layout-tile').forEach(tile => {
        let interaction = null;
        tile.addEventListener('pointerdown', event => {
            if (event.button !== 0 || event.target.closest('select')) return;
            interaction = { resize: Boolean(event.target.closest('.resize-handle')), point: { clientX: event.clientX, clientY: event.clientY }, previous: values(tile) };
            tile.setPointerCapture(event.pointerId);
            event.preventDefault();
        });
        tile.addEventListener('pointermove', event => {
            if (!interaction) return;
            const start = cellAt(interaction.point), current = cellAt(event), next = { ...interaction.previous };
            if (interaction.resize) {
                next.width = current.column - next.column + 1; next.height = current.row - next.row + 1;
            } else {
                next.column += current.column - start.column; next.row += current.row - start.row;
            }
            if (valid(tile, next)) setValues(tile, next);
        });
        const finishPointer = event => {
            if (!interaction) return;
            if (tile.hasPointerCapture(event.pointerId)) tile.releasePointerCapture(event.pointerId);
            interaction = null;
        };
        tile.addEventListener('pointerup', finishPointer);
        tile.addEventListener('pointercancel', finishPointer);
    });
    saveButton.addEventListener('click', async () => {
        saveButton.disabled = true;
        const feedback = document.getElementById('layout-save-feedback');
        feedback.classList.add('hidden');
        try {
            const response = await fetch(grid.dataset.saveUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    spaces: [...grid.querySelectorAll('.layout-tile')].map(tile => ({
                        id: Number(tile.dataset.id),
                        grid_column: values(tile).column,
                        grid_row: values(tile).row,
                        grid_width: values(tile).width,
                        grid_height: values(tile).height,
                        status: tile.querySelector('.layout-status').value
                    }))
                })
            });
            if (!response.ok) {
                const payload = await response.json().catch(() => ({}));
                throw new Error(payload.message || 'La disposition n’a pas pu être enregistrée.');
            }
            feedback.textContent = '✓ Disposition enregistrée';
            feedback.className = 'mt-2 text-center text-xs font-bold text-emerald-700';
            window.setTimeout(() => {
                window.location.href = @json(route('admin.interactive-map.index', ['campus_id' => $selectedCampusId, 'floor_id' => $selectedFloorId]));
            }, 2000);
        } catch (error) {
            feedback.textContent = '✕ Impossible d’enregistrer la disposition.';
            feedback.className = 'mt-2 text-center text-xs font-bold text-rose-700';
            saveButton.disabled = false;
        }
    });
})();
</script>
@endsection
