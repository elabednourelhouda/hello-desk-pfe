<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = Contract::with(['client', 'reservation.space'])
            ->when($request->filled('status') && $request->status !== 'all', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($clientQuery) use ($search) {
                            $clientQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.contracts.index', compact('contracts'));
    }

    public function show(Contract $contract)
    {
        $contract->load(['client', 'reservation.space', 'reservation.campus', 'reservation.floor', 'uploader']);

        return view('admin.contracts.show', compact('contract'));
    }

    public function edit(Contract $contract)
    {
        $contract->load(['client', 'reservation.space']);

        return view('admin.contracts.edit', compact('contract'));
    }

    public function update(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:draft,active,expired,cancelled'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('pdf_file')) {
            if ($contract->pdf_path && Storage::disk('public')->exists($contract->pdf_path)) {
                Storage::disk('public')->delete($contract->pdf_path);
            }

            $data['pdf_path'] = $request->file('pdf_file')->store('contracts', 'public');
            $data['uploaded_by'] = Auth::id();
        }

        unset($data['pdf_file']);

        $contract->update($data);

        if ($contract->status === 'active' && $contract->reservation) {
            $contract->reservation->update([
                'status' => 'confirmed',
                'approved_by' => Auth::id(),
            ]);

            if ($contract->reservation->space) {
                $contract->reservation->space->update([
                    'status' => 'Réservé',
                ]);
            }
        }

        if ($contract->status === 'cancelled' && $contract->reservation) {
            $contract->reservation->update([
                'status' => 'cancelled',
            ]);
        }

        return redirect()
            ->route('admin.contracts.show', $contract)
            ->with('success', 'Contrat mis à jour avec succès.');
    }
}