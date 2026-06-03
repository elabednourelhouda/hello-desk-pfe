<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Models\ProspectRequest;
use Illuminate\Http\Request;

class ProspectRequestController extends Controller
{
    public function store(Request $request, Prospect $prospect)
    {
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
        $data['created_by'] = $request->user()->id;
        $data['status'] = 'new';

        ProspectRequest::create($data);

        return back()->with('success', 'Demande du prospect ajoutée avec succès.');
    }

    public function update(Request $request, ProspectRequest $prospectRequest)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:50'],
            'response_notes' => ['nullable', 'string'],
        ]);

        $prospectRequest->update($data);

        return back()->with('success', 'Demande mise à jour avec succès.');
    }

    public function destroy(ProspectRequest $prospectRequest)
    {
        $prospectRequest->delete();

        return back()->with('success', 'Demande supprimée avec succès.');
    }
}