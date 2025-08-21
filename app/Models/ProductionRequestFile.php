<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductionRequestFile extends Model
{
    protected $fillable = [
        'production_request_id',
        'department_id',
        'file_path',
        // (اختياري) 'file_name', 'description'
    ];

    protected $appends = ['url']; // يضيف حقل url تلقائيًا عند التحويل لمصفوفة/JSON

    public function productionRequest(): BelongsTo
    {
        return $this->belongsTo(ProductionRequest::class, 'production_request_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'dept_id');
    }

    /** رابط التحميل للـ public disk */
    public function getUrlAttribute(): ?string
    {
        return $this->file_path
            ? Storage::disk('public')->url($this->file_path)
            : null;
    }
}
