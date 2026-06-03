<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Models\ProspectVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProspectVisitController extends Controller
{
    public function store(Request $request, Prospect $prospect)
    {
        $validated = $request->validate([
            'visit_date' => ['required', 'date'],
            'visit_time' => ['nullable', 'date_format:H:i'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'space_type_id' => ['nullable', 'exists:space_types,id'],
            'status' => ['required', 'in:planned,done,cancelled'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['prospect_id'] = $prospect->id;
        $validated['commercial_id'] = $prospect->assigned_to;
        $validated['created_by'] = Auth::id();

        ProspectVisit::create($validated);

        return back()->with('success', 'Visite ajoutée avec succès.');
    }

    public function markDone(ProspectVisit $visit)
    {
        $visit->update([
            'status' => 'done',
        ]);

        return back()->with('success', 'La visite a été marquée comme effectuée.');
    }

    public function cancel(ProspectVisit $visit)
    {
        $visit->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'La visite a été annulée.');
    }

    public function destroy(ProspectVisit $visit)
    {
        $visit->delete();

        return back()->with('success', 'Visite supprimée avec succès.');
    }
}