<?php

namespace App\Models;

use App\Models\Space;
use App\Models\Campus;
use App\Models\Floor;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCommercial(): bool
    {
        return $this->role === 'commercial';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function clientProfile()
    {
        return $this->hasOne(Client::class);
    }

    public function staffAssignments()
    {
        return $this->hasMany(\App\Models\StaffAssignment::class, 'commercial_id');
    }

    public function manageableCampusIds(): array
    {
        if ($this->isAdmin()) {
            return Campus::query()
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->values()
                ->all();
        }

        if (!$this->isCommercial()) {
            return [];
        }

        return $this->staffAssignments()
            ->pluck('campus_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function fullAccessCampusIds(): array
    {
        if ($this->isAdmin()) {
            return Campus::query()
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->values()
                ->all();
        }

        if (!$this->isCommercial()) {
            return [];
        }

        return $this->staffAssignments()
            ->whereNull('floor_id')
            ->pluck('campus_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function manageableFloorIds(): array
    {
        if ($this->isAdmin()) {
            return Floor::query()
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->values()
                ->all();
        }

        if (!$this->isCommercial()) {
            return [];
        }

        return $this->staffAssignments()
            ->whereNotNull('floor_id')
            ->pluck('floor_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function canManageCampus(int $campusId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->isCommercial()) {
            return false;
        }

        return $this->staffAssignments()
            ->where('campus_id', $campusId)
            ->exists();
    }

    public function canManageWholeCampus(int $campusId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->isCommercial()) {
            return false;
        }

        return $this->staffAssignments()
            ->where('campus_id', $campusId)
            ->whereNull('floor_id')
            ->exists();
    }

    public function canManageFloor(int $floorId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->isCommercial()) {
            return false;
        }

        $floor = Floor::find($floorId);

        if (!$floor) {
            return false;
        }

        return $this->staffAssignments()
            ->where('campus_id', $floor->campus_id)
            ->where(function ($query) use ($floorId) {
                $query->whereNull('floor_id')
                    ->orWhere('floor_id', $floorId);
            })
            ->exists();
    }

    public function canManageSpace(Space $space): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->isCommercial()) {
            return false;
        }

        if ($this->canManageWholeCampus((int) $space->campus_id)) {
            return true;
        }

        if ($space->floor_id && $this->canManageFloor((int) $space->floor_id)) {
            return true;
        }

        return false;
    }

    public function hasAnyStaffAssignment(): bool
    {
        return $this->staffAssignments()->exists();
    }

    public function assignmentsCreated()
    {
        return $this->hasMany(\App\Models\StaffAssignment::class, 'assigned_by');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
