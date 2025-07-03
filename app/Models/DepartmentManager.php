<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentManager extends Model
{
    protected $table = 'department_managers';

    protected $fillable = [
        'dept_id',
        'employee_id',
        'is_primary',
        'start_date',
        'end_date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    public function employee()
    {
        // return $this->belongsTo(Employee::class, 'employee_id');
    }
}

