<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'dept_name',
        'dept_code',
        'parent_dept_id',
        'dept_type',
        'location',
        'phone_extension',
        'email',
        'is_active',
        'color_code',
    ];

    public function managers()
    {
        return $this->hasMany(DepartmentManager::class, 'dept_id');
    }
}
