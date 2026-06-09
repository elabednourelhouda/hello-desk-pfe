<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $client = $this->currentClient();

        $contracts = collect();
        $activeCount = 0;
        $draftCount = 0;
        $totalCount = 0;
        $signedPdfCount = 0;
        $exportReadyCount = 0;

        if ($client) {
            $baseQuery = Contract::where('client_id', $client->id);

            $activeCount = (clone $baseQuery)->where('status', 'active')->count();
            $draftCount = (clone $baseQuery)->where('status', 'draft')->count();
            $totalCount = (clone $baseQuery)->count();

            $signedPdfCount = (clone $baseQuery)
                ->whereNotNull('pdf_path')
                ->count();

            $exportReadyCount = (clone $baseQuery)
                ->where('status', 'active')
                ->whereNull('pdf_path')
                ->count();

            $contracts = Contract::with(['reservation.space', 'payments'])
                ->where('client_id', $client->id)
                ->when($request->filled('status') && $request->status !== 'all', function ($query) use ($request) {
                    $query->where('status', $request->status);
                })
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = $request->search;

                    $query->where(function ($subQuery) use ($search) {
                        $subQuery->where('title', 'like', "%{$search}%")
                            ->orWhereHas('reservation.space', function ($spaceQuery) use ($search) {
                                $spaceQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('code', 'like', "%{$search}%");
                            });
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString();
        }

        return view('client.contracts.index', compact(
            'client',
            'contracts',
            'activeCount',
            'draftCount',
            'totalCount',
            'signedPdfCount',
            'exportReadyCount'
        ));
    }

    public function show(Contract $contract): View
    {
        $client = $this->currentClient();

        abort_if(!$client || $contract->client_id !== $client->id, 403);

        $contract->load([
            'client',
            'reservation.space.spaceType',
            'reservation.campus',
            'reservation.floor',
            'payments' => function ($query) {
                $query->orderBy('due_date');
            },
        ]);

        return view('client.contracts.show', compact('client', 'contract'));
    }

    public function document(Contract $contract): View
    {
        $client = $this->currentClient();

        abort_if(!$client || $contract->client_id !== $client->id, 403);

        abort_if($contract->status !== 'active', 403);

        $contract->load([
            'client',
            'reservation.space.spaceType',
            'reservation.campus',
            'reservation.floor',
            'payments' => function ($query) {
                $query->orderBy('due_date');
            },
        ]);

        return view('client.contracts.document', compact('client', 'contract'));
    }

    private function currentClient(): ?Client
    {
        return Client::where('user_id', Auth::id())->first();
    }
}