<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Reservation;
use App\Notifications\PaymentDueCreatedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $scope = $this->commercialScope();
        $hasCommercialScope = $this->hasScope($scope);

        $query = Payment::query()
            ->with(['client', 'contract.reservation.space']);

        $this->applyScopeToPaymentQuery($query, $scope);

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'late') {
                $query->where(function (Builder $lateQuery) {
                    $lateQuery->where('status', 'late')
                        ->orWhere(function (Builder $subQuery) {
                            $subQuery->where('status', 'due')
                                ->whereDate('due_date', '<', today());
                        });
                });
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->whereHas('client', function (Builder $clientQuery) use ($search) {
                    $clientQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('contract', function (Builder $contractQuery) use ($search) {
                    $contractQuery->where('title', 'like', "%{$search}%");
                })
                ->orWhere('receipt_number', 'like', "%{$search}%")
                ->orWhere('invoice_number', 'like', "%{$search}%")
                ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $payments = $query
            ->orderBy('due_date')
            ->paginate(10)
            ->withQueryString();

        return view('commercial.payments.index', compact(
            'payments',
            'hasCommercialScope'
        ));
    }

    public function create(Request $request)
    {
        $scope = $this->commercialScope();

        if (! $this->hasScope($scope)) {
            return redirect()
                ->route('commercial.payments.index')
                ->with('error', 'Vous n’avez aucune affectation active. Impossible de créer une échéance.');
        }

        $contractsQuery = Contract::with(['client', 'reservation.space'])
            ->latest();

        $this->applyScopeToContractQuery($contractsQuery, $scope);

        $contracts = $contractsQuery->get();

        $selectedContract = null;

        if ($request->filled('contract_id')) {
            $selectedContractQuery = Contract::with(['client', 'reservation.space']);

            $this->applyScopeToContractQuery($selectedContractQuery, $scope);

            $selectedContract = $selectedContractQuery->findOrFail($request->contract_id);
        }

        return view('commercial.payments.create', compact('contracts', 'selectedContract'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedPaymentData($request);

        $scope = $this->commercialScope();

        if (! $this->hasScope($scope)) {
            return back()
                ->withInput()
                ->with('error', 'Vous n’avez aucune affectation active. Impossible de créer une échéance.');
        }

        $contractQuery = Contract::with(['client', 'reservation.space']);

        $this->applyScopeToContractQuery($contractQuery, $scope);

        $contract = $contractQuery->findOrFail($data['contract_id']);

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

        if ($payment->client && $payment->client->user) {
            $payment->client->user->notify(new PaymentDueCreatedNotification($payment));
        }

        if ($request->boolean('redirect_to_contract')) {
            return redirect()
                ->route('commercial.contracts.show', $contract)
                ->with('success', 'Échéance de paiement créée avec succès.');
        }

        return redirect()
            ->route('commercial.payments.index')
            ->with('success', 'Échéance de paiement créée avec succès.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['client', 'contract.reservation.space', 'reservation', 'recorder']);

        if (! $this->canManagePayment($payment)) {
            abort(403, 'Cette échéance ne fait pas partie de votre périmètre commercial.');
        }

        return view('commercial.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $payment->load(['client', 'contract.reservation.space']);

        if (! $this->canManagePayment($payment)) {
            abort(403, 'Cette échéance ne fait pas partie de votre périmètre commercial.');
        }

        return view('commercial.payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        $payment->load(['client', 'contract.reservation.space']);

        if (! $this->canManagePayment($payment)) {
            abort(403, 'Cette échéance ne fait pas partie de votre périmètre commercial.');
        }

        $data = $this->validatedPaymentData($request, false);

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

            'receipt_number' => $data['receipt_number'] ?? null,
            'invoice_number' => $data['invoice_number'] ?? null,

            'recorded_by' => Auth::id(),
            'notes' => $data['notes'] ?? null,
        ];

        if ($data['status'] === 'paid' && ! $payment->paid_at) {
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

        return redirect()
            ->route('commercial.payments.show', $payment)
            ->with('success', 'Paiement mis à jour avec succès.');
    }

    public function markAsPaid(Payment $payment)
    {
        $payment->load(['contract.reservation.space', 'reservation']);

        if (! $this->canManagePayment($payment)) {
            abort(403, 'Cette échéance ne fait pas partie de votre périmètre commercial.');
        }

        $payment->update([
            'amount_paid' => $payment->amount_ttc_value,
            'status' => 'paid',
            'paid_at' => now(),
            'recorded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Échéance marquée comme payée avec succès.');
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

    private function applyScopeToPaymentQuery(Builder $query, array $scope): void
    {
        if (! $this->hasScope($scope)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function (Builder $q) use ($scope) {
            $q->whereHas('contract', function (Builder $contractQuery) use ($scope) {
                $this->applyScopeToContractQuery($contractQuery, $scope);
            })
            ->orWhereHas('reservation', function (Builder $reservationQuery) use ($scope) {
                $this->applySpaceScopeToReservationQuery($reservationQuery, $scope);
            });
        });
    }

    private function applyScopeToContractQuery(Builder $query, array $scope): void
    {
        if (! $this->hasScope($scope)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereHas('reservation', function (Builder $reservationQuery) use ($scope) {
            $this->applySpaceScopeToReservationQuery($reservationQuery, $scope);
        });
    }

    private function applySpaceScopeToReservationQuery(Builder $query, array $scope): void
    {
        if (! $this->hasScope($scope)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function (Builder $q) use ($scope) {
            if (! empty($scope['campus_ids'])) {
                $q->orWhereIn('campus_id', $scope['campus_ids']);
            }

            if (! empty($scope['floor_ids'])) {
                $q->orWhereIn('floor_id', $scope['floor_ids']);
            }

            $q->orWhereHas('space', function (Builder $spaceQuery) use ($scope) {
                $this->applySpaceScopeToSpaceQuery($spaceQuery, $scope);
            });
        });
    }

    private function applySpaceScopeToSpaceQuery(Builder $query, array $scope): void
    {
        if (! $this->hasScope($scope)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function (Builder $q) use ($scope) {
            if (! empty($scope['campus_ids'])) {
                $q->orWhereIn('campus_id', $scope['campus_ids']);
            }

            if (! empty($scope['floor_ids'])) {
                $q->orWhereIn('floor_id', $scope['floor_ids']);
            }
        });
    }

    private function canManagePayment(Payment $payment): bool
    {
        $scope = $this->commercialScope();

        if (! $this->hasScope($scope)) {
            return false;
        }

        if ($payment->contract?->reservation) {
            return $this->canManageReservation($payment->contract->reservation, $scope);
        }

        if ($payment->reservation) {
            return $this->canManageReservation($payment->reservation, $scope);
        }

        return false;
    }

    private function canManageReservation(Reservation $reservation, array $scope): bool
    {
        $campusId = $reservation->campus_id ?? $reservation->space?->campus_id;
        $floorId = $reservation->floor_id ?? $reservation->space?->floor_id;

        return in_array((int) $campusId, $scope['campus_ids'], true)
            || in_array((int) $floorId, $scope['floor_ids'], true);
    }

    private function commercialScope(): array
    {
        if (! Schema::hasTable('staff_assignments')) {
            return [
                'campus_ids' => [],
                'floor_ids' => [],
                'client_campus_ids' => [],
            ];
        }

        $userColumn = collect(['user_id', 'commercial_id', 'staff_id'])
            ->first(fn ($column) => Schema::hasColumn('staff_assignments', $column));

        if (! $userColumn) {
            return [
                'campus_ids' => [],
                'floor_ids' => [],
                'client_campus_ids' => [],
            ];
        }

        $query = DB::table('staff_assignments')
            ->where($userColumn, Auth::id());

        if (Schema::hasColumn('staff_assignments', 'active')) {
            $query->where('active', true);
        }

        if (Schema::hasColumn('staff_assignments', 'is_active')) {
            $query->where('is_active', true);
        }

        if (Schema::hasColumn('staff_assignments', 'status')) {
            $query->whereIn('status', ['active', 'actif']);
        }

        $assignments = $query->get();

        $campusIds = $assignments
            ->filter(fn ($assignment) => filled($assignment->campus_id ?? null) && blank($assignment->floor_id ?? null))
            ->pluck('campus_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $floorIds = $assignments
            ->filter(fn ($assignment) => filled($assignment->floor_id ?? null))
            ->pluck('floor_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $floorCampusIds = [];

        if (! empty($floorIds) && Schema::hasTable('floors')) {
            $floorCampusIds = DB::table('floors')
                ->whereIn('id', $floorIds)
                ->pluck('campus_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        return [
            'campus_ids' => $campusIds,
            'floor_ids' => $floorIds,
            'client_campus_ids' => collect($campusIds)
                ->merge($floorCampusIds)
                ->unique()
                ->values()
                ->all(),
        ];
    }

    private function hasScope(array $scope): bool
    {
        return ! empty($scope['campus_ids']) || ! empty($scope['floor_ids']);
    }

    private function storeUploadedFile(Request $request, string $field): ?string
    {
        if (! $request->hasFile($field)) {
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