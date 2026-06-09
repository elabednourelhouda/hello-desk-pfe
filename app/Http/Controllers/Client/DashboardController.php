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
        $payments = collect();

        $reservationsCount = 0;
        $contractsCount = 0;
        $duePaymentsCount = 0;

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

            $payments = Payment::with(['contract'])
                ->where('client_id', $clientProfile->id)
                ->orderBy('due_date')
                ->take(8)
                ->get();

            $reservationsCount = Reservation::where('client_id', $clientProfile->id)->count();

            $contractsCount = Contract::where('client_id', $clientProfile->id)->count();

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
            'payments',
            'reservationsCount',
            'contractsCount',
            'duePaymentsCount',
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