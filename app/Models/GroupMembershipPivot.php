<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Permission\Traits\HasRoles;

class GroupMembershipPivot extends Pivot
{
    use HasRoles;
    
    protected $guard_name = 'group';
    
}