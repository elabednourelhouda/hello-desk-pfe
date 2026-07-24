<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProspectSource extends Model
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
     * Prospect::source is a plain string column storing this source's
     * `code` (there is no real foreign key on prospects) — so this
     * relation is defined by matching `prospects.source` to
     * `prospect_sources.code` rather than the usual id/*_id pair.
     */
    public function prospects()
    {
        return $this->hasMany(Prospect::class, 'source', 'code');
    }
}