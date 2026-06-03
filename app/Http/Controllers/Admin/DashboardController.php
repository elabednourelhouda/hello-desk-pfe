<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $prospectsCount = Prospect::count();
        $clientsCount = Client::count();
        $commercialsCount = User::where('role', 'commercial')->count();

        $convertedProspectsCount = Prospect::where('crm_status', 'converted')->count();
        $lostProspectsCount = Prospect::where('crm_status', 'lost')->count();

        // We will connect this later when the reservations module exists.
        $reservationsCount = 0;

        $conversionRate = $prospectsCount > 0
            ? round(($convertedProspectsCount / $prospectsCount) * 100)
            : 0;

        $lostRate = $prospectsCount > 0
            ? round(($lostProspectsCount / $prospectsCount) * 100)
            : 0;

        $clientsVisualRate = $prospectsCount > 0
            ? min(100, round(($clientsCount / $prospectsCount) * 100))
            : 0;

        $commercialsVisualRate = min(100, $commercialsCount * 20);

        $reservationsVisualRate = 0;

        $recentProspects = Prospect::latest()
            ->take(5)
            ->get();

        $recentClients = Client::latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'prospectsCount',
            'clientsCount',
            'commercialsCount',
            'reservationsCount',
            'convertedProspectsCount',
            'lostProspectsCount',
            'conversionRate',
            'lostRate',
            'clientsVisualRate',
            'commercialsVisualRate',
            'reservationsVisualRate',
            'recentProspects',
            'recentClients'
        ));
    }
}