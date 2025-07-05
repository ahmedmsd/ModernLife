<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $primaryKey = 'group_id';
    protected $fillable = ['group_name', 'description'];
    
    use \Spatie\Permission\Traits\HasRoles;
    
    protected $guard_name = 'group';
    
    public function getTitleAttribute()
    {
        return $this->group_name;
    }
}
