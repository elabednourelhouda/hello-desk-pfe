<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAttachment extends Model
{
    protected $fillable = [
        'client_id',
        'uploaded_by',
        'document_type',
        'title',
        'original_name',
        'path',
        'mime_type',
        'size',
        'notes',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}