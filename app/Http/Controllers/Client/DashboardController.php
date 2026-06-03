<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
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

        $notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        return view('client.dashboard', compact('notifications', 'unreadCount'));
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