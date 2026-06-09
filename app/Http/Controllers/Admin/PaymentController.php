<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Payment;
use App\Notifications\PaymentDueCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['client', 'contract.reservation.space'])
            ->when($request->filled('status') && $request->status !== 'all', function ($query) use ($request) {
                if ($request->status === 'late') {
                    $query->where(function ($lateQuery) {
                        $lateQuery->where('status', 'late')
                            ->orWhere(function ($subQuery) {
                                $subQuery->where('status', 'due')
                                    ->whereDate('due_date', '<', today());
                            });
                    });
                } else {
                    $query->where('status', $request->status);
                }
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('contract', function ($contractQuery) use ($search) {
                        $contractQuery->where('title', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy('due_date')
            ->paginate(10)
            ->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $contracts = Contract::with(['client', 'reservation.space'])
            ->latest()
            ->get();

        $selectedContract = null;

        if ($request->filled('contract_id')) {
            $selectedContract = Contract::with(['client', 'reservation.space'])
                ->findOrFail($request->contract_id);
        }

        return view('admin.payments.create', compact('contracts', 'selectedContract'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contract_id' => ['required', 'exists:contracts,id'],
            'due_date' => ['required', 'date'],
            'amount_due' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:due,paid,late,cancelled'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'redirect_to_contract' => ['nullable', 'boolean'],
        ]);

        $contract = Contract::with(['client', 'reservation'])->findOrFail($data['contract_id']);

        $amountPaid = $data['amount_paid'] ?? 0;

        if ($data['status'] === 'paid') {
            $amountPaid = $data['amount_due'];
        }

        $payment = Payment::create([
            'client_id' => $contract->client_id,
            'contract_id' => $contract->id,
            'reservation_id' => $contract->reservation_id,
            'due_date' => $data['due_date'],
            'amount_due' => $data['amount_due'],
            'amount_paid' => $amountPaid,
            'paid_at' => $data['status'] === 'paid' ? now() : null,
            'status' => $data['status'],
            'payment_method' => $data['payment_method'] ?? null,
            'reference' => $data['reference'] ?? null,
            'recorded_by' => Auth::id(),
            'notes' => $data['notes'] ?? null,
        ]);

        $payment->load('client.user');

        if ($payment->client && $payment->client->user) {
            $payment->client->user->notify(new PaymentDueCreatedNotification($payment));
        }

        if ($request->boolean('redirect_to_contract')) {
            return redirect()
                ->route('admin.contracts.show', $contract)
                ->with('success', 'Échéance de paiement créée avec succès.');
        }

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Échéance de paiement créée avec succès.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['client', 'contract.reservation.space', 'reservation', 'recorder']);

        return view('admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $payment->load(['client', 'contract.reservation.space']);

        return view('admin.payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'due_date' => ['required', 'date'],
            'amount_due' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:due,paid,late,cancelled'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['amount_paid'] = $data['amount_paid'] ?? 0;

        if ($data['status'] === 'paid') {
            $data['amount_paid'] = $data['amount_due'];
        }

        if ($data['status'] === 'paid' && !$payment->paid_at) {
            $data['paid_at'] = now();
        }

        if ($data['status'] !== 'paid') {
            $data['paid_at'] = null;
        }

        $data['recorded_by'] = Auth::id();

        $payment->update($data);

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Paiement mis à jour avec succès.');
    }

    public function markAsPaid(Payment $payment)
    {
        $payment->update([
            'amount_paid' => $payment->amount_due,
            'status' => 'paid',
            'paid_at' => now(),
            'recorded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Échéance marquée comme payée avec succès.');
    }
}