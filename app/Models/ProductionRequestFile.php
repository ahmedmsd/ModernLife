<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRequestFile extends Model
{
    protected $fillable = [
        'production_request_id',
        'department_id',
        'file_path',
    ];

    public function productionRequest(): BelongsTo
    {
        return $this->belongsTo(ProductionRequest::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'dept_id');
    }
}
