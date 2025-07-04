<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentCategories extends Model
{
    protected $primaryKey = 'category_id';
    protected $fillable = [
        'category_name',
        'description',
        'color_code',
        'icon'
    ];
    
    public function departments()
    {
        return $this->hasMany(Department::class, 'dept_type');
    }
}