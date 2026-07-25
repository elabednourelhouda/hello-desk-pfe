<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactType extends Model
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
     * ProspectVisit::contact_type is a plain string column storing this
     * type's `code` (there is no real foreign key on prospect_visits) —
     * so, same as ProspectSource::prospects(), this relation is defined
     * by matching `prospect_visits.contact_type` to `contact_types.code`
     * rather than the usual id/*_id pair.
     */
    public function prospectVisits()
    {
        return $this->hasMany(ProspectVisit::class, 'contact_type', 'code');
    }
}