<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceStatus extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Space::status is a plain string column storing this status's
     * `code` (there is no real foreign key on spaces) — so, same as
     * ContactType::prospectVisits(), this relation is defined by
     * matching `spaces.status` to `space_statuses.code` rather than
     * the usual id/*_id pair.
     *
     * Note: this will never include spaces currently showing as
     * "Réservé" on the map — that display status is computed from
     * active reservations, never stored as `spaces.status`.
     */
    public function spaces()
    {
        return $this->hasMany(Space::class, 'status', 'code');
    }
}