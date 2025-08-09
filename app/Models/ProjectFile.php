<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id',
        'department_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
        'upload_date',
        'description',
        'version',
        'is_current',
    ];
}
