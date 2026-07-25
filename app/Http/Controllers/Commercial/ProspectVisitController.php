<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\ContactType;
use App\Models\Prospect;
use App\Models\ProspectVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProspectVisitController extends Controller
{
    public function store(Request $request, Prospect $prospect)
    {
        $this->authorizeCommercialProspect($prospect);

        $validated = $request->validate([
            'visit_date' => ['required', 'date'],
            'visit_time' => ['nullable', 'date_format:H:i'],
            'contact_type' => ['required', Rule::in(ContactType::where('is_active', true)->pluck('code'))],
            'summary' => ['required', 'string', 'max:2000'],
            'next_followup_at' => ['nullable', 'date', 'after_or_equal:visit_date'],
        ], [
            'contact_type.required' => 'Le type de contact est obligatoire.',
            'contact_type.in' => 'Ce type de contact n’est plus disponible, veuillez en choisir un autre.',
            'next_followup_at.after_or_equal' => 'La date de prochaine relance doit être après la date de ce suivi.',
        ]);

        $prospect->visits()->create([
            'commercial_id' => Auth::id(),
            'created_by' => Auth::id(),
            'visit_date' => $validated['visit_date'],
            'visit_time' => $validated['visit_time'] ?? null,
            'next_followup_at' => $validated['next_followup_at'] ?? null,
            'campus_id' => null,
            'space_type_id' => null,
            'status' => 'done',
            'contact_type' => $validated['contact_type'],
            'notes' => $validated['summary'],
        ]);

        return back()->with('success', 'Suivi ajouté à l’archive avec succès.');
    }

    public function markDone(ProspectVisit $visit)
    {
        $this->authorizeCommercialProspect($visit->prospect);

        $visit->update([
            'status' => 'done',
        ]);

        return redirect()
            ->route('commercial.prospects.show', $visit->prospect)
            ->with('success', 'Le suivi a été marqué comme effectué.');
    }

    public function cancel(ProspectVisit $visit)
    {
        $this->authorizeCommercialProspect($visit->prospect);

        $visit->update([
            'status' => 'cancelled',
        ]);

        return redirect()
            ->route('commercial.prospects.show', $visit->prospect)
            ->with('success', 'Le suivi a été annulé.');
    }

    public function destroy(ProspectVisit $visit)
    {
        $this->authorizeCommercialProspect($visit->prospect);

        $prospect = $visit->prospect;

        $visit->delete();

        return back()->with('success', 'Suivi supprimé avec succès.');
    }

    private function authorizeCommercialProspect(Prospect $prospect): void
    {
        abort_if((int) $prospect->assigned_to !== (int) Auth::id(), 403);
    }
}