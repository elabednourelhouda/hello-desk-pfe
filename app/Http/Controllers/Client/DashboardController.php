<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $clientProfile = Client::with('mainCampus')
            ->where('user_id', $user->id)
            ->first();

        $reservations = collect();
        $contracts = collect();
        $paymentContracts = collect();

        $reservationsCount = 0;
        $contractsCount = 0;
        $duePaymentsCount = 0;
        $totalPaymentsCount = 0;
        $paidPaymentsCount = 0;

        if ($clientProfile) {
            $reservations = Reservation::with(['space', 'campus', 'floor', 'contract'])
                ->where('client_id', $clientProfile->id)
                ->latest('starts_at')
                ->take(5)
                ->get();

            $contracts = Contract::with(['reservation.space'])
                ->where('client_id', $clientProfile->id)
                ->latest()
                ->take(5)
                ->get();

            // One row per contract, not one row per monthly installment —
            // otherwise a contract with several months already paid keeps
            // cluttering the dashboard widget instead of collapsing into
            // "8/12 réglées". See Contract::paymentSummary().
            $paymentContracts = Contract::with(['reservation.space', 'payments'])
                ->where('client_id', $clientProfile->id)
                ->whereHas('payments')
                ->latest('id')
                ->take(5)
                ->get()
                ->map(function (Contract $contract) {
                    return array_merge(['contract' => $contract], $contract->paymentSummary());
                });

            $reservationsCount = Reservation::where('client_id', $clientProfile->id)->count();

            $contractsCount = Contract::where('client_id', $clientProfile->id)->count();

            $totalPaymentsCount = Payment::where('client_id', $clientProfile->id)->count();

            $paidPaymentsCount = Payment::where('client_id', $clientProfile->id)
                ->where('status', 'paid')
                ->count();

            $duePaymentsCount = Payment::where('client_id', $clientProfile->id)
                ->where(function ($query) {
                    $query->whereIn('status', ['due', 'late'])
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('status', 'due')
                                ->whereDate('due_date', '<', today());
                        });
                })
                ->count();
        }

        $notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        return view('client.dashboard', compact(
            'clientProfile',
            'reservations',
            'contracts',
            'paymentContracts',
            'reservationsCount',
            'contractsCount',
            'duePaymentsCount',
            'totalPaymentsCount',
            'paidPaymentsCount',
            'notifications',
            'unreadCount'
        ));
    }

    public function markNotificationsAsRead(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user->unreadNotifications()
            ->update(['read_at' => now()]);

        return back()->with('success', 'Notifications marquées comme lues.');
    }
}