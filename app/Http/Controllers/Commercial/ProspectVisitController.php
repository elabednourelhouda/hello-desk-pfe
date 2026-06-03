<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Models\ProspectVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProspectVisitController extends Controller
{
    public function store(Request $request, Prospect $prospect)
    {
        $this->authorizeCommercialProspect($prospect);

        $data = $request->validate([
            'visit_date' => ['required', 'date'],
            'visit_time' => ['nullable', 'date_format:H:i'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'space_type_id' => ['nullable', 'exists:space_types,id'],
            'status' => ['required', 'in:planned,done,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $prospect->visits()->create($data);

        return redirect()
            ->route('commercial.prospects.show', $prospect)
            ->with('success', 'La visite a été ajoutée avec succès.');
    }

    public function markDone(ProspectVisit $visit)
    {
        $this->authorizeCommercialProspect($visit->prospect);

        $visit->update([
            'status' => 'done',
        ]);

        return redirect()
            ->route('commercial.prospects.show', $visit->prospect)
            ->with('success', 'La visite a été marquée comme effectuée.');
    }

    public function cancel(ProspectVisit $visit)
    {
        $this->authorizeCommercialProspect($visit->prospect);

        $visit->update([
            'status' => 'cancelled',
        ]);

        return redirect()
            ->route('commercial.prospects.show', $visit->prospect)
            ->with('success', 'La visite a été annulée.');
    }

    public function destroy(ProspectVisit $visit)
    {
        $this->authorizeCommercialProspect($visit->prospect);

        $prospect = $visit->prospect;

        $visit->delete();

        return redirect()
            ->route('commercial.prospects.show', $prospect)
            ->with('success', 'La visite a été supprimée.');
    }

    private function authorizeCommercialProspect(Prospect $prospect): void
    {
        abort_if((int) $prospect->assigned_to !== (int) Auth::id(), 403);
    }
}