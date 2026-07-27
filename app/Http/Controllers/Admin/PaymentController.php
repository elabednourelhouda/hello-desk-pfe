<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Payment;
use App\Notifications\PaymentDueCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $contractsQuery = Contract::with(['client', 'reservation.space', 'payments'])
            ->whereHas('payments')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('payments', function ($paymentQuery) use ($search) {
                        $paymentQuery->where('receipt_number', 'like', "%{$search}%")
                            ->orWhere('invoice_number', 'like', "%{$search}%")
                            ->orWhere('reference', 'like', "%{$search}%");
                    });
                });
            })
            ->latest('id');

        // Aggregate first (one entry per contract), then filter on the
        // computed overall status, since "late"/"paid"/"due" only exist
        // once the installments have been summarized — they're not raw
        // columns we can filter with a plain ->where().
        $rows = $contractsQuery->get()->map(function (Contract $contract) {
            return array_merge(['contract' => $contract], $contract->paymentSummary());
        });

        if ($request->filled('status') && $request->status !== 'all') {
            $rows = $rows->filter(fn ($row) => $row['overall_status'] === $request->status);
        }

        $rows = $rows->values();

        $perPage = 10;
        $page = $request->integer('page', 1);

        $contracts = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.payments.index', compact('contracts'));
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
        $data = $this->validatedPaymentData($request);

        $contract = Contract::with(['client', 'reservation'])->findOrFail($data['contract_id']);

        if ($data['status'] === 'paid') {
            if ($blocker = $this->earlierUnpaidPayment($contract->id, $data['due_date'])) {
                return back()
                    ->withInput()
                    ->withErrors(['status' => $this->earlierUnpaidPaymentErrorMessage($blocker)]);
            }
        }

        $amounts = $this->calculateAmounts($data);

        $amountPaid = $data['amount_paid'] ?? 0;

        if ($data['status'] === 'paid') {
            $amountPaid = $amounts['amount_ttc'];
        }

        $payment = Payment::create([
            'client_id' => $contract->client_id,
            'contract_id' => $contract->id,
            'reservation_id' => $contract->reservation_id,

            'due_date' => $data['due_date'],
            'duration_label' => $data['duration_label'] ?? null,

            'amount_ht' => $amounts['amount_ht'],
            'tax_rate' => $amounts['tax_rate'],
            'tax_amount' => $amounts['tax_amount'],
            'amount_ttc' => $amounts['amount_ttc'],

            // Keep amount_due as TTC so old pages and stats continue to work.
            'amount_due' => $amounts['amount_ttc'],
            'amount_paid' => $amountPaid,

            'paid_at' => $data['status'] === 'paid' ? now() : null,
            'status' => $data['status'],
            'payment_method' => $data['payment_method'] ?? null,
            'reference' => $data['reference'] ?? null,

            'cheque_number' => $data['cheque_number'] ?? null,
            'cheque_bank' => $data['cheque_bank'] ?? null,
            'cheque_date' => $data['cheque_date'] ?? null,

            'bank_transfer_reference' => $data['bank_transfer_reference'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,

            'tpe_transaction_reference' => $data['tpe_transaction_reference'] ?? null,

            'receipt_number' => $data['receipt_number'] ?? null,
            'receipt_file' => $this->storeUploadedFile($request, 'receipt_file'),

            'invoice_number' => $data['invoice_number'] ?? null,
            'invoice_file' => $this->storeUploadedFile($request, 'invoice_file'),

            'recorded_by' => Auth::id(),
            'notes' => $data['notes'] ?? null,
        ]);

        $payment->load('client.user');

        if ($payment->status === 'paid') {
            $this->ensureReceiptNumber($payment);
        }

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

    /**
     * The full month-by-month échéancier for a single contract. This is
     * what the grouped index() links into so staff don't have to scroll
     * through every other contract's installments to find one client's
     * 12 monthly rows.
     */
    public function schedule(Contract $contract)
    {
        $contract->load([
            'client',
            'reservation.space',
            'payments' => fn ($query) => $query->orderBy('due_date'),
        ]);

        $summary = $contract->paymentSummary();

        return view('admin.payments.schedule', compact('contract', 'summary'));
    }

    /**
     * Printable receipt for a settled payment. Mirrors the existing
     * Admin\ContractController::document() pattern (a print-styled
     * Blade view rather than a generated binary PDF) — this project
     * has no PDF-rendering library installed, and adding one is a
     * bigger decision (new Composer dependency) than this fix
     * warrants; "Fichier -> Imprimer -> Enregistrer en PDF" from the
     * browser produces an equivalent result. If a real one-click PDF
     * download is wanted later, installing barryvdh/laravel-dompdf
     * and swapping this view's render call is a small, contained
     * follow-up.
     */
    public function receipt(Payment $payment)
    {
        if ($payment->status !== 'paid') {
            return back()->with('error', 'Le reçu n’est disponible qu’une fois l’échéance payée.');
        }

        $payment->load(['client', 'contract.reservation.space', 'reservation']);

        $this->ensureReceiptNumber($payment);

        return view('admin.payments.receipt', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $payment->load(['client', 'contract.reservation.space']);

        return view('admin.payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $this->validatedPaymentData($request, false);

        if ($data['status'] === 'paid' && $payment->status !== 'paid') {
            if ($blocker = $this->earlierUnpaidPayment($payment->contract_id, $data['due_date'], $payment->id)) {
                return back()
                    ->withInput()
                    ->withErrors(['status' => $this->earlierUnpaidPaymentErrorMessage($blocker)]);
            }
        }

        $amounts = $this->calculateAmounts($data);

        $amountPaid = $data['amount_paid'] ?? 0;

        if ($data['status'] === 'paid') {
            $amountPaid = $amounts['amount_ttc'];
        }

        $updateData = [
            'due_date' => $data['due_date'],
            'duration_label' => $data['duration_label'] ?? null,

            'amount_ht' => $amounts['amount_ht'],
            'tax_rate' => $amounts['tax_rate'],
            'tax_amount' => $amounts['tax_amount'],
            'amount_ttc' => $amounts['amount_ttc'],

            'amount_due' => $amounts['amount_ttc'],
            'amount_paid' => $amountPaid,

            'status' => $data['status'],
            'payment_method' => $data['payment_method'] ?? null,
            'reference' => $data['reference'] ?? null,

            'cheque_number' => $data['cheque_number'] ?? null,
            'cheque_bank' => $data['cheque_bank'] ?? null,
            'cheque_date' => $data['cheque_date'] ?? null,

            'bank_transfer_reference' => $data['bank_transfer_reference'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,

            'tpe_transaction_reference' => $data['tpe_transaction_reference'] ?? null,

            'receipt_number' => $data['receipt_number'] ?? $payment->receipt_number,
            'invoice_number' => $data['invoice_number'] ?? null,

            'recorded_by' => Auth::id(),
            'notes' => $data['notes'] ?? null,
        ];

        if ($data['status'] === 'paid' && !$payment->paid_at) {
            $updateData['paid_at'] = now();
        }

        if ($data['status'] !== 'paid') {
            $updateData['paid_at'] = null;
        }

        if ($request->hasFile('receipt_file')) {
            $this->deleteStoredFile($payment->receipt_file);
            $updateData['receipt_file'] = $this->storeUploadedFile($request, 'receipt_file');
        }

        if ($request->hasFile('invoice_file')) {
            $this->deleteStoredFile($payment->invoice_file);
            $updateData['invoice_file'] = $this->storeUploadedFile($request, 'invoice_file');
        }

        $payment->update($updateData);

        if ($payment->status === 'paid') {
            $this->ensureReceiptNumber($payment);
        }

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Paiement mis à jour avec succès.');
    }

    public function markAsPaid(Payment $payment)
    {
        if ($blocker = $this->earlierUnpaidPayment($payment->contract_id, $payment->due_date, $payment->id)) {
            return back()->withErrors(['status' => $this->earlierUnpaidPaymentErrorMessage($blocker)]);
        }

        $payment->update([
            'amount_paid' => $payment->amount_ttc_value,
            'status' => 'paid',
            'paid_at' => now(),
            'recorded_by' => Auth::id(),
        ]);

        $this->ensureReceiptNumber($payment);

        return back()->with('success', 'Échéance marquée comme payée avec succès.');
    }

    /**
     * The owner's rule: a client pays one installment at a time, in
     * due-date order — they can't settle month 5 while months 3 and 4
     * are still outstanding. Returns the earliest still-unpaid payment
     * (due strictly before $referenceDueDate on the given contract,
     * optionally excluding one payment id) that must be settled first,
     * or null if the given date is clear to be marked paid.
     */
    private function earlierUnpaidPayment(?int $contractId, string|\Carbon\Carbon $referenceDueDate, ?int $excludePaymentId = null): ?Payment
    {
        if (! $contractId) {
            return null;
        }

        return Payment::where('contract_id', $contractId)
            ->whereIn('status', ['due', 'late'])
            ->when($excludePaymentId, fn ($query) => $query->where('id', '!=', $excludePaymentId))
            ->whereDate('due_date', '<', $referenceDueDate)
            ->orderBy('due_date')
            ->first();
    }

    private function earlierUnpaidPaymentErrorMessage(Payment $blocker): string
    {
        return 'Impossible de marquer cette échéance comme payée : l’échéance du '
            . $blocker->due_date->format('d/m/Y')
            . ' (' . ($blocker->duration_label ?: 'échéance précédente') . ') n’est pas encore réglée. '
            . 'Le propriétaire n’accepte le paiement que d’un mois à la fois, dans l’ordre.';
    }

    /**
     * Fills receipt_number with a stable, human-readable reference the
     * first time a payment is settled — e.g. "REC-2026-000042" — if
     * staff didn't already type one in manually. Idempotent: does
     * nothing if a receipt_number is already present.
     */
    private function ensureReceiptNumber(Payment $payment): void
    {
        if ($payment->receipt_number) {
            return;
        }

        $payment->forceFill([
            'receipt_number' => sprintf(
                'REC-%s-%06d',
                ($payment->paid_at ?? now())->format('Y'),
                $payment->id
            ),
        ])->save();
    }

    private function validatedPaymentData(Request $request, bool $creating = true): array
    {
        return $request->validate([
            'contract_id' => [$creating ? 'required' : 'nullable', 'exists:contracts,id'],

            'due_date' => ['required', 'date'],
            'duration_label' => ['nullable', 'string', 'max:255'],

            'amount_ht' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],

            'status' => ['required', 'in:due,paid,late,cancelled'],
            'payment_method' => ['nullable', 'in:cash,cheque,bank_transfer,tpe,other'],
            'reference' => ['nullable', 'string', 'max:255'],

            'cheque_number' => ['nullable', 'string', 'max:255'],
            'cheque_bank' => ['nullable', 'string', 'max:255'],
            'cheque_date' => ['nullable', 'date'],

            'bank_transfer_reference' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],

            'tpe_transaction_reference' => ['nullable', 'string', 'max:255'],

            'receipt_number' => ['nullable', 'string', 'max:255'],
            'receipt_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],

            'invoice_number' => ['nullable', 'string', 'max:255'],
            'invoice_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],

            'notes' => ['nullable', 'string'],
            'redirect_to_contract' => ['nullable', 'boolean'],
        ]);
    }

    private function calculateAmounts(array $data): array
    {
        $amountHt = round((float) $data['amount_ht'], 2);
        $taxRate = round((float) ($data['tax_rate'] ?? 20), 2);
        $taxAmount = round($amountHt * ($taxRate / 100), 2);
        $amountTtc = round($amountHt + $taxAmount, 2);

        return [
            'amount_ht' => $amountHt,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'amount_ttc' => $amountTtc,
        ];
    }

    private function storeUploadedFile(Request $request, string $field): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        return $request->file($field)->store('payment-documents', 'public');
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}