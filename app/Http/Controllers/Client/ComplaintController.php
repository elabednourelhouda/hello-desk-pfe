<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Complaint;
use App\Models\Contract;
use App\Models\Reservation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    private function currentClient(): ?Client
    {
        return Client::where('user_id', Auth::id())->first();
    }

    public function index(): View
    {
        $client = $this->currentClient();

        abort_if(!$client, 403);

        $complaints = Complaint::with(['reservation.space', 'contract'])
            ->where('client_id', $client->id)
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => Complaint::where('client_id', $client->id)->count(),
            'new' => Complaint::where('client_id', $client->id)->where('status', 'new')->count(),
            'in_progress' => Complaint::where('client_id', $client->id)->where('status', 'in_progress')->count(),
            'resolved' => Complaint::where('client_id', $client->id)->where('status', 'resolved')->count(),
        ];

        return view('client.complaints.index', compact('client', 'complaints', 'stats'));
    }

    public function create(): View
    {
        $client = $this->currentClient();

        abort_if(!$client, 403);

        $reservations = Reservation::with('space')
            ->where('client_id', $client->id)
            ->latest()
            ->get();

        $contracts = Contract::where('client_id', $client->id)
            ->latest()
            ->get();

        return view('client.complaints.create', compact('client', 'reservations', 'contracts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $client = $this->currentClient();

        abort_if(!$client, 403);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:material_issue,internet_issue,air_conditioning,equipment_request,reservation_issue,other'],
            'priority' => ['required', 'string', 'in:low,normal,high,urgent'],
            'description' => ['nullable', 'string', 'max:2000'],
            'reservation_id' => ['nullable', 'exists:reservations,id'],
            'contract_id' => ['nullable', 'exists:contracts,id'],
        ]);

        if (!empty($validated['reservation_id'])) {
            $reservationBelongsToClient = Reservation::where('id', $validated['reservation_id'])
                ->where('client_id', $client->id)
                ->exists();

            abort_if(!$reservationBelongsToClient, 403);
        }

        if (!empty($validated['contract_id'])) {
            $contractBelongsToClient = Contract::where('id', $validated['contract_id'])
                ->where('client_id', $client->id)
                ->exists();

            abort_if(!$contractBelongsToClient, 403);
        }

        $complaint = Complaint::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'reservation_id' => $validated['reservation_id'] ?? null,
            'contract_id' => $validated['contract_id'] ?? null,
            'subject' => $validated['subject'],
            'type' => $validated['type'],
            'priority' => $validated['priority'],
            'description' => $validated['description'] ?? null,
            'status' => 'new',
        ]);

        return redirect()
            ->route('client.complaints.show', $complaint)
            ->with('success', 'Votre réclamation a été envoyée avec succès.');
    }

    public function show(Complaint $complaint): View
    {
        $client = $this->currentClient();

        abort_if(!$client || $complaint->client_id !== $client->id, 403);

        $complaint->load(['reservation.space', 'contract']);

        return view('client.complaints.show', compact('client', 'complaint'));
    }
}