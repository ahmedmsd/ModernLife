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

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DepartmentCategories::class, 'dept_type', 'category_id');
    }

    public function parentDepartment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
    return $this->belongsTo(Department::class, 'parent_dept_id', 'dept_id');
    }

    public $incrementing = true;
    protected $keyType = 'int';

    public function employees(): Department|\Illuminate\Database\Eloquent\Relations\HasMany
    {

        return $this->hasMany(Employee::class, 'department_id', 'dept_id');
    }
}
