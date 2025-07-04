<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'dept_id';

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

    public function category()
    {
        return $this->belongsTo(DepartmentCategories::class, 'dept_type', 'category_id');
    }

    public function parentDepartment()
    {
    return $this->belongsTo(Department::class, 'parent_dept_id', 'dept_id');
    }
}
