<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
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

    public function assignmentsCreated()
    {
        return $this->hasMany(\App\Models\StaffAssignment::class, 'assigned_by');
    }
}