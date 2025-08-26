<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    protected $table = 'project_files';

    protected $primaryKey = 'file_id';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'project_id', 'department_id', 'file_name', 'file_path', 'file_type',
        'file_size', 'uploaded_by', 'upload_date', 'description', 'version',
        'is_current', 'estimated_cost',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'is_current'     => 'boolean',
        'upload_date'    => 'datetime',
    ];

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
}
