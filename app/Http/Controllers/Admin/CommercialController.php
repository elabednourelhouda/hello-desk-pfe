<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Floor;
use App\Models\StaffAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;

class CommercialController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'commercial')
            ->withCount('staffAssignments')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $commercials = $query->paginate(10)->withQueryString();

        return view('admin.commercials.index', [
            'commercials' => $commercials,
            'totalCount' => User::where('role', 'commercial')->count(),
            'assignedCount' => StaffAssignment::distinct('commercial_id')->count('commercial_id'),
        ]);
    }

    public function create()
    {
        $campuses = Campus::where('is_active', true)
            ->with(['floors' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('level')
                    ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('admin.commercials.create', [
            'campuses' => $campuses,
            'floorsByCampus' => $this->floorsByCampus($campuses),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'floor_id' => ['nullable', 'exists:floors,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Le nom complet est obligatoire.',
            'email.required' => 'L’email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'campus_id.required' => 'Veuillez choisir un campus.',
            'campus_id.exists' => 'Le campus sélectionné est invalide.',
            'floor_id.exists' => 'L’étage sélectionné est invalide.',
        ]);

        if (!empty($validated['floor_id'])) {
            $floorBelongsToCampus = Floor::where('id', $validated['floor_id'])
                ->where('campus_id', $validated['campus_id'])
                ->exists();

            if (!$floorBelongsToCampus) {
                return back()
                    ->withInput()
                    ->with('error', 'L’étage sélectionné ne correspond pas au campus choisi.');
            }
        }

        $temporaryPassword = 'HD-' . Str::upper(Str::random(8));

        $commercial = DB::transaction(function () use ($validated, $temporaryPassword) {
            $commercial = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'role' => 'commercial',
                'must_change_password' => true,
            ]);

            StaffAssignment::create([
                'commercial_id' => $commercial->id,
                'campus_id' => $validated['campus_id'],
                'floor_id' => $validated['floor_id'] ?? null,
                'assigned_by' => Auth::id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            return $commercial;
        });

        return redirect()
            ->route('admin.commercials.show', $commercial)
            ->with('success', 'Commercial créé avec succès.')
            ->with('temporary_password', $temporaryPassword);
    }

    public function show(User $commercial)
    {
        $this->ensureCommercial($commercial);

        $commercial->load([
            'staffAssignments.campus',
            'staffAssignments.floor',
            'staffAssignments.assignedBy',
        ]);

        $campuses = Campus::where('is_active', true)
            ->with(['floors' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('level')
                    ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('admin.commercials.show', [
            'commercial' => $commercial,
            'campuses' => $campuses,
            'floorsByCampus' => $this->floorsByCampus($campuses),
        ]);
    }

    public function resetPassword(User $commercial)
    {
        $this->ensureCommercial($commercial);

        $temporaryPassword = 'HD-' . Str::upper(Str::random(8));

        $commercial->update([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('admin.commercials.show', $commercial)
            ->with('success', 'Mot de passe réinitialisé avec succès.')
            ->with('temporary_password', $temporaryPassword);
    }

    public function storeAssignment(Request $request, User $commercial)
    {
        $this->ensureCommercial($commercial);

        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'floor_id' => ['nullable', 'exists:floors,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'campus_id.required' => 'Veuillez choisir un campus.',
            'campus_id.exists' => 'Le campus sélectionné est invalide.',
            'floor_id.exists' => 'L’étage sélectionné est invalide.',
        ]);

        if (!empty($validated['floor_id'])) {
            $floorBelongsToCampus = Floor::where('id', $validated['floor_id'])
                ->where('campus_id', $validated['campus_id'])
                ->exists();

            if (!$floorBelongsToCampus) {
                return redirect()
                    ->route('admin.commercials.show', $commercial)
                    ->with('error', 'L’étage sélectionné ne correspond pas au campus choisi.');
            }
        }

        $alreadyAssignedToWholeCampus = StaffAssignment::where('commercial_id', $commercial->id)
            ->where('campus_id', $validated['campus_id'])
            ->whereNull('floor_id')
            ->exists();

        if ($alreadyAssignedToWholeCampus && !empty($validated['floor_id'])) {
            return redirect()
                ->route('admin.commercials.show', $commercial)
                ->with('error', 'Ce commercial est déjà affecté à tout ce campus.');
        }

        if (empty($validated['floor_id'])) {
            StaffAssignment::where('commercial_id', $commercial->id)
                ->where('campus_id', $validated['campus_id'])
                ->delete();
        } else {
            $duplicate = StaffAssignment::where('commercial_id', $commercial->id)
                ->where('campus_id', $validated['campus_id'])
                ->where('floor_id', $validated['floor_id'])
                ->exists();

            if ($duplicate) {
                return redirect()
                    ->route('admin.commercials.show', $commercial)
                    ->with('error', 'Cette affectation existe déjà.');
            }
        }

        StaffAssignment::create([
            'commercial_id' => $commercial->id,
            'campus_id' => $validated['campus_id'],
            'floor_id' => $validated['floor_id'] ?? null,
            'assigned_by' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.commercials.show', $commercial)
            ->with('success', 'Affectation ajoutée avec succès.');
    }

    public function destroyAssignment(User $commercial, StaffAssignment $assignment)
    {
        $this->ensureCommercial($commercial);

        if ((int) $assignment->commercial_id !== (int) $commercial->id) {
            abort(404);
        }

        $assignment->delete();

        return redirect()
            ->route('admin.commercials.show', $commercial)
            ->with('success', 'Affectation supprimée avec succès.');
    }

    private function ensureCommercial(User $commercial): void
    {
        if ($commercial->role !== 'commercial') {
            abort(404);
        }
    }

    private function floorsByCampus(Collection $campuses): array
    {
        return $campuses->mapWithKeys(function ($campus) {
            return [
                $campus->id => $campus->floors->map(function ($floor) {
                    return [
                        'id' => $floor->id,
                        'name' => $floor->name,
                    ];
                })->values(),
            ];
        })->toArray();
    }
}