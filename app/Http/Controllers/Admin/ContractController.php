<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Payment;
use App\Notifications\PaymentDueCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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

            // Note: this used to also run
            // $contract->reservation->space->update(['status' => 'reserved'])
            // here — but 'reserved'/'Réservé' was deliberately removed
            // from space_statuses (see the 2026_07_25_171048 migration):
            // it's a computed, time-bound display status derived from
            // active reservations, never a value stored on
            // spaces.status. Confirming the reservation above is
            // exactly what makes InteractiveMapController@index resolve
            // this space to "Réservé" on its own — writing 'reserved'
            // here just left an orphan code that matches no row in
            // space_statuses. The space's actual status
            // (available/maintenance/etc.) is intentionally left
            // untouched.

            $alreadyHasPayments = Payment::where('contract_id', $contract->id)->exists();

            if (! $alreadyHasPayments) {
                $this->generatePaymentSchedule($contract);
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

    /**
     * Builds the full set of Payment rows for a freshly-activated
     * contract, based on its reservation's engagement.
     *
     * `Reservation::negotiated_price` is a PER-PERIOD rate (e.g. "3000
     * DH"), never pre-multiplied by `engagement_duration_value` — see
     * the price-prefill JS in reservations/create.blade.php, which
     * fills this field from the space's per-unit rate and never
     * multiplies it by the engagement length. So a reservation with
     * engagement_duration_value = 12 and engagement_duration_unit =
     * 'month' means "3000 DH per month, for 12 months" — 12 separate
     * monthly installments, not one lump sum.
     *
     * Billing rule (matches the business requirement: a client renting
     * for a year with monthly billing gets a payment reminder every
     * month, pays one month at a time, and the contract naturally winds
     * down as the last installment is paid):
     *   - Only engagement_duration_unit === 'month' with more than one
     *     month produces a recurring schedule. Hourly/half-day/daily/
     *     custom engagements — and a single-month engagement — keep the
     *     original behavior: exactly one payment, due at contract start,
     *     for the full negotiated_price.
     *   - Installment #1 is due immediately at `start_date` (the
     *     existing "pay before you move in" behavior — unchanged).
     *   - Installments #2 through #N are due on the 1st of each
     *     following calendar month, regardless of what day of the month
     *     `start_date` falls on (e.g. a June 15 start still bills
     *     July 1, August 1, etc.) — this was an explicit choice: an
     *     anniversary-of-start-date due date would be less predictable
     *     for clients and staff than a fixed "always the 1st".
     *
     * Only the first payment gets a PaymentDueCreatedNotification here
     * — it's the only one already due. The rest are picked up by the
     * existing `payments:check-deadlines` scheduled command as their
     * own due dates approach, exactly like any other payment.
     */
    private function generatePaymentSchedule(Contract $contract): void
    {
        $reservation = $contract->reservation;

        if (! $reservation) {
            return;
        }

        $ratePerPeriod = (float) ($reservation->negotiated_price ?? 0);
        $isMonthlyRecurring = $reservation->engagement_duration_unit === 'month'
            && (int) $reservation->engagement_duration_value > 1;

        $installmentCount = $isMonthlyRecurring ? (int) $reservation->engagement_duration_value : 1;

        $startDate = Carbon::parse($contract->start_date);

        // 1st of the calendar month right after start_date's month —
        // the anchor every installment after the first snaps to.
        $firstRecurringDueDate = $startDate->copy()->startOfMonth()->addMonthNoOverflow();

        $firstPayment = null;

        DB::transaction(function () use (
            $contract,
            $reservation,
            $ratePerPeriod,
            $installmentCount,
            $isMonthlyRecurring,
            $startDate,
            $firstRecurringDueDate,
            &$firstPayment
        ) {
            for ($installment = 1; $installment <= $installmentCount; $installment++) {
                $dueDate = $installment === 1
                    ? $startDate
                    : $firstRecurringDueDate->copy()->addMonthsNoOverflow($installment - 2);

                $payment = Payment::create([
                    'client_id' => $contract->client_id,
                    'contract_id' => $contract->id,
                    'reservation_id' => $contract->reservation_id,
                    'due_date' => $dueDate,
                    'duration_label' => $isMonthlyRecurring ? "Mois {$installment}/{$installmentCount}" : null,
                    'amount_due' => $ratePerPeriod,
                    'amount_paid' => 0,
                    'status' => 'due',
                    'recorded_by' => Auth::id(),
                    'notes' => $installment === 1
                        ? 'Échéance créée automatiquement lors de l’activation du contrat.'
                        : "Échéance {$installment}/{$installmentCount} générée automatiquement (facturation mensuelle).",
                ]);

                if ($installment === 1) {
                    $firstPayment = $payment;
                }
            }
        });

        if ($firstPayment) {
            $firstPayment->load('client.user');

            if ($firstPayment->client && $firstPayment->client->user) {
                $firstPayment->client->user->notify(new PaymentDueCreatedNotification($firstPayment));
            }
        }
    }
}