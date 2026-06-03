<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Models\ProspectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProspectRequestController extends Controller
{
    public function store(Request $request, Prospect $prospect)
    {
        $this->authorizeCommercialProspect($prospect);

        $data = $request->validate([
            'request_date' => ['required', 'date'],
            'request_type' => ['nullable', 'string', 'max:100'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'space_type_id' => ['nullable', 'exists:space_types,id'],
            'desired_start_date' => ['nullable', 'date'],
            'duration_type' => ['nullable', 'string', 'max:50'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'description' => ['required', 'string'],
        ]);

        $data['prospect_id'] = $prospect->id;
        $data['created_by'] = Auth::id();
        $data['status'] = 'new';

        ProspectRequest::create($data);

        return back()->with('success', 'Demande du prospect ajoutée avec succès.');
    }

    public function update(Request $request, ProspectRequest $prospectRequest)
    {
        $this->authorizeCommercialProspect($prospectRequest->prospect);

        $data = $request->validate([
            'status' => ['required', 'string', 'max:50'],
            'response_notes' => ['nullable', 'string'],
        ]);

        $prospectRequest->update($data);

        return back()->with('success', 'Demande mise à jour avec succès.');
    }

    public function destroy(ProspectRequest $prospectRequest)
    {
        $this->authorizeCommercialProspect($prospectRequest->prospect);

        $prospectRequest->delete();

        return back()->with('success', 'Demande supprimée avec succès.');
    }

    private function authorizeCommercialProspect(Prospect $prospect): void
    {
        if ((int) $prospect->assigned_to !== (int) Auth::id()) {
            abort(403, 'Vous ne pouvez gérer que les prospects qui vous sont affectés.');
        }
    }
}