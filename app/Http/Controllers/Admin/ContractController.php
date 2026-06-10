<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Payment;
use App\Notifications\PaymentDueCreatedNotification;
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
        $contract->load([
            'client',
            'reservation.space',
            'reservation.campus',
            'reservation.floor',
            'payments' => function ($query) {
                $query->orderBy('due_date');
            },
        ]);

        return view('admin.contracts.show', compact('contract'));
    }

    public function document(Contract $contract)
    {
        $contract->load([
            'client',
            'reservation.space.spaceType',
            'reservation.campus',
            'reservation.floor',
            'reservation.creator',
            'payments' => function ($query) {
                $query->orderBy('due_date');
            },
        ]);

        return view('admin.contracts.document', compact('contract'));
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

        if ($data['status'] === 'active') {
            if (! $contract->client?->hasCompleteLegalFile()) {
                return back()
                    ->withInput()
                    ->with('error', 'Impossible d’activer le contrat : le dossier juridique du client est incomplet.');
            }

            $hasSignedPdf = $request->hasFile('pdf_file') || filled($contract->pdf_path);

            if (! $hasSignedPdf) {
                return back()
                    ->withInput()
                    ->with('error', 'Impossible d’activer le contrat : veuillez importer le PDF signé.');
            }
        }

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
                    'status' => 'reserved',
                ]);
            }

            $alreadyHasPayments = Payment::where('contract_id', $contract->id)->exists();

            if (! $alreadyHasPayments) {
                $payment = Payment::create([
                    'client_id' => $contract->client_id,
                    'contract_id' => $contract->id,
                    'reservation_id' => $contract->reservation_id,
                    'due_date' => $contract->start_date,
                    'amount_due' => $contract->reservation->negotiated_price ?? 0,
                    'amount_paid' => 0,
                    'status' => 'due',
                    'recorded_by' => Auth::id(),
                    'notes' => 'Échéance créée automatiquement lors de l’activation du contrat.',
                ]);

                $payment->load('client.user');

                if ($payment->client && $payment->client->user) {
                    $payment->client->user->notify(new PaymentDueCreatedNotification($payment));
                }
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