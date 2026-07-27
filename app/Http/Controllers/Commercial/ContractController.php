<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Space;
use App\Notifications\PaymentDueCreatedNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $scope = $this->commercialScope();
        $hasCommercialScope = $this->hasScope($scope);

        $query = Contract::query()
            ->with(['client', 'reservation.space']);

        $this->applyScopeToContractQuery($query, $scope);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('title', 'like', "%{$search}%")
                    ->orWhereHas('client', function (Builder $clientQuery) use ($search) {
                        $clientQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $contracts = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('commercial.contracts.index', compact(
            'contracts',
            'hasCommercialScope'
        ));
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

        if (! $this->canManageContract($contract)) {
            abort(403, 'Ce contrat ne fait pas partie de votre périmètre commercial.');
        }

        return view('commercial.contracts.show', compact('contract'));
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

        if (! $this->canManageContract($contract)) {
            abort(403, 'Ce contrat ne fait pas partie de votre périmètre commercial.');
        }

        return view('commercial.contracts.document', compact('contract'));
    }

    public function edit(Contract $contract)
    {
        $contract->load(['client', 'reservation.space']);

        if (! $this->canManageContract($contract)) {
            abort(403, 'Ce contrat ne fait pas partie de votre périmètre commercial.');
        }

        return view('commercial.contracts.edit', compact('contract'));
    }

    public function update(Request $request, Contract $contract)
    {
        $contract->load(['client', 'reservation.space']);

        if (! $this->canManageContract($contract)) {
            abort(403, 'Ce contrat ne fait pas partie de votre périmètre commercial.');
        }

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
            ->route('commercial.contracts.show', $contract)
            ->with('success', 'Contrat mis à jour avec succès.');
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

    private function canManageContract(Contract $contract): bool
    {
        $scope = $this->commercialScope();

        if (! $this->hasScope($scope)) {
            return false;
        }

        if (! $contract->reservation) {
            return false;
        }

        return $this->canManageReservation($contract->reservation, $scope);
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

    /**
     * Builds the full set of Payment rows for a freshly-activated
     * contract, based on its reservation's engagement. See
     * Admin\ContractController::generatePaymentSchedule() for the full
     * rationale — kept duplicated here rather than shared, same as
     * the rest of this controller's logic relative to its Admin
     * counterpart.
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