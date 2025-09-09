<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LegacyClientProjectFile extends Model
{
    protected $fillable = [
        'legacy_project_id', 'category', 'title', 'description',
        'file_path', 'mime_type', 'file_size', 'uploaded_by',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(LegacyClientProject::class, 'legacy_project_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }
}
