<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */

    protected $table = 'employees';

    protected $primaryKey = 'employee_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'national_id',
        'employee_name',
        'gender',
        'birth_date',
        'email',
        'phone',
        'address',
        'department_id',
        'position',
        'hire_date',
        'salary',
        'employment_type',
        'is_active',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'is_active' => 'boolean',
        'salary' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        // Add any sensitive fields here if needed
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
}
