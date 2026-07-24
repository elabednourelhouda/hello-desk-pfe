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
            'contact_type' => ['required', 'in:appel_telephonique,whatsapp,email,message_recu,note_interne'],
            'summary' => ['required', 'string', 'max:2000'],
            'next_followup_at' => ['nullable', 'date', 'after_or_equal:visit_date'],
        ], [
            'next_followup_at.after_or_equal' => 'La date de prochaine relance doit être après la date de ce suivi.',
        ]);

        $contactTypes = [
            'appel_telephonique' => 'Appel téléphonique',
            'whatsapp' => 'Message WhatsApp',
            'email' => 'Email',
            'message_recu' => 'Message reçu',
            'note_interne' => 'Note interne',
        ];

        $typeLabel = $contactTypes[$validated['contact_type']] ?? 'Suivi';

        ProspectVisit::create([
            'prospect_id' => $prospect->id,
            'commercial_id' => $prospect->assigned_to,
            'created_by' => Auth::id(),
            'visit_date' => $validated['visit_date'],
            'visit_time' => $validated['visit_time'] ?? null,
            'next_followup_at' => $validated['next_followup_at'] ?? null,
            'campus_id' => null,
            'space_type_id' => null,
            'status' => 'done',
            'notes' => "Type de contact : {$typeLabel}\nRésumé : {$validated['summary']}",
        ]);

        return back()->with('success', 'Suivi ajouté à l’archive avec succès.');
    }

    public function markDone(ProspectVisit $visit)
    {
        $visit->update([
            'status' => 'done',
        ]);

        return back()->with('success', 'Le suivi a été marqué comme effectué.');
    }

    public function cancel(ProspectVisit $visit)
    {
        $visit->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Le suivi a été annulé.');
    }

    public function destroy(ProspectVisit $visit)
    {
        $visit->delete();

        return back()->with('success', 'Suivi supprimé avec succès.');
    }
}