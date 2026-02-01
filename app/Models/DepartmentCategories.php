<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartmentCategories extends Model
{
    use HasFactory;
    protected $primaryKey = 'category_id';
    protected $fillable = [
        'category_name',
        'description',
        'color_code',
        'icon'
    ];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'dept_type');
    }
}
