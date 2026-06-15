<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAttachment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ClientAttachmentController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $this->ensureCanAccessClient($client);

        $validated = $request->validate([
            'document_type' => [
                'required',
                'in:cin_recto,cin_verso,passeport,carte_sejour,ice,rc,patente,cnss,contrat,facture,autre',
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:5120',
            ],
        ], [
            'file.required' => 'Veuillez choisir un fichier.',
            'file.mimes' => 'Le fichier doit être un PDF ou une image.',
            'file.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
            'document_type.required' => 'Veuillez choisir le type de document.',
        ]);

        $file = $request->file('file');

        $path = $file->store("client-attachments/{$client->id}", 'local');

        $client->attachments()->create([
            'uploaded_by' => Auth::id(),
            'document_type' => $validated['document_type'],
            'title' => $validated['title'] ?? null,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Pièce jointe ajoutée avec succès.');
    }

    public function preview(ClientAttachment $attachment)
    {
        $this->ensureCanAccessClient($attachment->client);

        if (! Storage::disk('local')->exists($attachment->path)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('local')->path($attachment->path),
            [
                'Content-Type' => $attachment->mime_type ?? 'application/octet-stream',
            ]
        );
    }

    public function download(ClientAttachment $attachment)
    {
        $this->ensureCanAccessClient($attachment->client);

        if (! Storage::disk('local')->exists($attachment->path)) {
            abort(404);
        }

        return response()->download(
            Storage::disk('local')->path($attachment->path),
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type ?? 'application/octet-stream',
            ]
        );
    }

    public function destroy(ClientAttachment $attachment)
    {
        $this->ensureCanAccessClient($attachment->client);

        Storage::disk('local')->delete($attachment->path);

        $attachment->delete();

        return back()->with('success', 'Pièce jointe supprimée avec succès.');
    }

    private function ensureCanAccessClient(Client $client): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role !== 'commercial') {
            abort(403);
        }

        if ($this->commercialCanAccessClient($client, (int) $user->id)) {
            return;
        }

        abort(403);
    }

    private function commercialCanAccessClient(Client $client, int $userId): bool
    {
        if ($client->prospect()->where('assigned_to', $userId)->exists()) {
            return true;
        }

        foreach (['assigned_to', 'commercial_id', 'responsible_commercial_id', 'created_by'] as $column) {
            if (Schema::hasColumn('clients', $column) && (int) ($client->{$column} ?? 0) === $userId) {
                return true;
            }
        }

        $assignedCampusIds = $this->assignedCampusIds($userId);

        if (
            ! empty($assignedCampusIds)
            && $client->main_campus_id
            && in_array((int) $client->main_campus_id, $assignedCampusIds, true)
        ) {
            return true;
        }

        if (
            empty($assignedCampusIds)
            && ! Schema::hasColumn('clients', 'assigned_to')
            && ! Schema::hasColumn('clients', 'commercial_id')
            && ! Schema::hasColumn('clients', 'responsible_commercial_id')
            && ! Schema::hasColumn('clients', 'created_by')
            && $client->status === 'active'
        ) {
            return true;
        }

        return false;
    }

    private function assignedCampusIds(int $userId): array
    {
        if (! Schema::hasTable('staff_assignments')) {
            return [];
        }

        if (! Schema::hasColumn('staff_assignments', 'commercial_id')) {
            return [];
        }

        return DB::table('staff_assignments')
            ->where('commercial_id', $userId)
            ->whereNotNull('campus_id')
            ->pluck('campus_id')
            ->unique()
            ->values()
            ->map(fn($id) => (int) $id)
            ->toArray();
    }
}
