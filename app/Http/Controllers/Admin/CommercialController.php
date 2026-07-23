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
use Illuminate\Validation\ValidationException;

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
            'assignedCount' => StaffAssignment::whereNotNull('commercial_id')
                ->distinct()
                ->count('commercial_id'),
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
        $validatedUser = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ], [
            'name.required' => 'Le nom complet est obligatoire.',
            'email.required' => 'L’email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
        ]);

        $validatedAssignment = $this->validateAssignmentData($request);

        $temporaryPassword = 'HD-' . Str::upper(Str::random(8));

        $commercial = DB::transaction(function () use ($validatedUser, $validatedAssignment, $temporaryPassword) {
            $commercial = User::create([
                'name' => $validatedUser['name'],
                'email' => $validatedUser['email'],
                'password' => Hash::make($temporaryPassword),
                'role' => 'commercial',
                'must_change_password' => true,
            ]);

            $this->createAssignmentsForCommercial($commercial, $validatedAssignment);

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

        $validatedAssignment = $this->validateAssignmentData($request);

        DB::transaction(function () use ($commercial, $validatedAssignment) {
            $this->createAssignmentsForCommercial($commercial, $validatedAssignment);
        });

        return redirect()
            ->route('admin.commercials.show', $commercial)
            ->with('success', 'Affectation enregistrée avec succès.');
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

    private function validateAssignmentData(Request $request): array
    {
        $validated = $request->validate([
            'assignment_type' => ['required', 'in:all_campuses,campus,floors'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'floor_ids' => ['nullable', 'array'],
            'floor_ids.*' => ['integer', 'exists:floors,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'assignment_type.required' => 'Veuillez choisir le type d’affectation.',
            'assignment_type.in' => 'Le type d’affectation sélectionné est invalide.',
            'campus_id.exists' => 'Le campus sélectionné est invalide.',
            'floor_ids.array' => 'La liste des étages sélectionnés est invalide.',
            'floor_ids.*.exists' => 'Un des étages sélectionnés est invalide.',
        ]);

        if ($validated['assignment_type'] !== 'all_campuses' && empty($validated['campus_id'])) {
            throw ValidationException::withMessages([
                'campus_id' => 'Veuillez choisir un site.',
            ]);
        }

        if ($validated['assignment_type'] === 'floors') {
            $floorIds = collect($validated['floor_ids'] ?? [])
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if (empty($floorIds)) {
                throw ValidationException::withMessages([
                    'floor_ids' => 'Veuillez choisir au moins un étage.',
                ]);
            }

            $validFloorIds = Floor::whereIn('id', $floorIds)
                ->where('campus_id', $validated['campus_id'])
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->values()
                ->all();

            if (count($validFloorIds) !== count($floorIds)) {
                throw ValidationException::withMessages([
                    'floor_ids' => 'Un des étages sélectionnés ne correspond pas au campus choisi.',
                ]);
            }

            $validated['floor_ids'] = $floorIds;
        }

        return $validated;
    }

    private function createAssignmentsForCommercial(User $commercial, array $validated): void
    {
        $notes = $validated['notes'] ?? null;

        if ($validated['assignment_type'] === 'all_campuses') {
            StaffAssignment::where('commercial_id', $commercial->id)->delete();

            $campuses = Campus::where('is_active', true)
                ->orderBy('name')
                ->get();

            foreach ($campuses as $campus) {
                StaffAssignment::create([
                    'commercial_id' => $commercial->id,
                    'campus_id' => $campus->id,
                    'floor_id' => null,
                    'assigned_by' => Auth::id(),
                    'notes' => $notes,
                ]);
            }

            return;
        }

        if ($validated['assignment_type'] === 'campus') {
            StaffAssignment::where('commercial_id', $commercial->id)
                ->where('campus_id', $validated['campus_id'])
                ->delete();

            StaffAssignment::create([
                'commercial_id' => $commercial->id,
                'campus_id' => $validated['campus_id'],
                'floor_id' => null,
                'assigned_by' => Auth::id(),
                'notes' => $notes,
            ]);

            return;
        }

        $alreadyAssignedToWholeCampus = StaffAssignment::where('commercial_id', $commercial->id)
            ->where('campus_id', $validated['campus_id'])
            ->whereNull('floor_id')
            ->exists();

        if ($alreadyAssignedToWholeCampus) {
            throw ValidationException::withMessages([
                'floor_ids' => 'Ce commercial est déjà affecté à tout ce campus.',
            ]);
        }

        foreach ($validated['floor_ids'] as $floorId) {
            StaffAssignment::firstOrCreate(
                [
                    'commercial_id' => $commercial->id,
                    'campus_id' => $validated['campus_id'],
                    'floor_id' => $floorId,
                ],
                [
                    'assigned_by' => Auth::id(),
                    'notes' => $notes,
                ]
            );
        }
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
