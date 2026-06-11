<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $query = Complaint::with([
            'client',
            'user',
            'reservation.space',
            'contract',
        ]);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('reservation.space', function ($spaceQuery) use ($search) {
                        $spaceQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('internal_code', 'like', "%{$search}%");
                    });
            });
        }

        $complaints = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Complaint::count(),
            'new' => Complaint::where('status', 'new')->count(),
            'in_progress' => Complaint::where('status', 'in_progress')->count(),
            'resolved' => Complaint::where('status', 'resolved')->count(),
            'urgent' => Complaint::where('priority', 'urgent')->count(),
        ];

        return view('admin.complaints.index', compact('complaints', 'stats'));
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load([
            'client',
            'user',
            'reservation.space',
            'reservation.campus',
            'reservation.floor',
            'contract',
        ]);

        return view('admin.complaints.show', compact('complaint'));
    }

    public function update(Request $request, Complaint $complaint): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:new,in_progress,waiting,resolved,closed,rejected'],
            'priority' => ['required', 'string', 'in:low,normal,high,urgent'],
            'admin_response' => ['nullable', 'string', 'max:3000'],
        ]);

        $data['resolved_at'] = in_array($data['status'], ['resolved', 'closed'])
            ? now()
            : null;

        $complaint->update($data);

        return redirect()
            ->route('admin.complaints.show', $complaint)
            ->with('success', 'Réclamation mise à jour avec succès.');
    }
}