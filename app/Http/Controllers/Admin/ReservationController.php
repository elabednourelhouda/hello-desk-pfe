<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $reservationStatuses = $this->reservationStatuses();

        $contractFilters = [
            'all' => 'All contracts',
            'created' => 'With contract',
            'missing' => 'Without contract',
        ];

        $query = Reservation::query()
            ->with(['client', 'space', 'contract'])
            ->latest('starts_at');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('client', function ($clientQuery) use ($search) {
                    $clientQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhereHas('space', function ($spaceQuery) use ($search) {
                    $spaceQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                })
                ->orWhereHas('contract', function ($contractQuery) use ($search) {
                    $contractQuery->where('title', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('contract') && $request->contract !== 'all') {
            if ($request->contract === 'created') {
                $query->whereHas('contract');
            }

            if ($request->contract === 'missing') {
                $query->whereDoesntHave('contract');
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('starts_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('starts_at', '<=', $request->to);
        }

        $reservations = $query->paginate(10)->withQueryString();

        return view('admin.reservations.index', compact(
            'reservations',
            'reservationStatuses',
            'contractFilters'
        ));
    }

    public function create(Request $request)
    {
        $durationTypes = $this->durationTypes();

        $clients = Client::orderBy('full_name')->get();

        $spaces = Space::with(['campus', 'floor', 'spaceType'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedSpace = null;

        if ($request->filled('space_id')) {
            $selectedSpace = Space::with(['campus', 'floor', 'spaceType'])
                ->where('is_active', true)
                ->findOrFail($request->space_id);
        }

        return view('admin.reservations.create', compact(
            'clients',
            'spaces',
            'selectedSpace',
            'durationTypes'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'space_id' => ['required', 'exists:spaces,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'duration_type' => ['required', 'in:hourly,daily,monthly,custom'],
            'negotiated_price' => ['nullable', 'numeric', 'min:0'],
            'contract_title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $space = Space::findOrFail($data['space_id']);

        $normalizedStatus = mb_strtolower($space->status ?? 'available');

        $notReservableStatuses = [
            'occupied',
            'unavailable',
            'maintenance',
            'in maintenance',
            'occupé',
            'occupe',
            'indisponible',
            'en maintenance',
        ];

        if (in_array($normalizedStatus, $notReservableStatuses)) {
            return back()
                ->withInput()
                ->with('error', 'This space cannot be reserved at the moment.');
        }

        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        $hasOverlap = Reservation::where('space_id', $space->id)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->where(function ($query) use ($startsAt, $endsAt) {
                $query->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt);
            })
            ->exists();

        if ($hasOverlap) {
            return back()
                ->withInput()
                ->with('error', 'This space is already reserved during this period.');
        }

        $reservation = DB::transaction(function () use ($data, $space, $startsAt, $endsAt) {
            $reservation = Reservation::create([
                'client_id' => $data['client_id'],
                'space_id' => $space->id,
                'campus_id' => $space->campus_id,
                'floor_id' => $space->floor_id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'duration_type' => $data['duration_type'],
                'negotiated_price' => $data['negotiated_price'] ?? 0,
                'status' => 'pending',
                'created_by' => Auth::id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $client = Client::findOrFail($data['client_id']);

            Contract::create([
                'client_id' => $client->id,
                'reservation_id' => $reservation->id,
                'title' => $data['contract_title'] ?: 'Contract - ' . $client->full_name,
                'start_date' => $startsAt->toDateString(),
                'end_date' => $endsAt->toDateString(),
                'status' => 'draft',
                'uploaded_by' => Auth::id(),
            ]);

            return $reservation;
        });

        return redirect()
            ->route('admin.reservations.show', $reservation)
            ->with('success', 'Reservation created successfully. A draft contract was created automatically.');
    }

    public function show(Reservation $reservation)
    {
        $reservation->load([
            'client',
            'space',
            'campus',
            'floor',
            'contract',
            'creator',
        ]);

        $reservationStatuses = $this->reservationStatuses();

        return view('admin.reservations.show', compact(
            'reservation',
            'reservationStatuses'
        ));
    }

    private function reservationStatuses(): array
    {
        return [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'in_progress' => 'In progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
        ];
    }

    private function durationTypes(): array
    {
        return [
            'hourly' => 'Hourly',
            'daily' => 'Daily',
            'monthly' => 'Monthly',
            'custom' => 'Custom',
        ];
    }
}