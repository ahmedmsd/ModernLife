<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentPurchaseItem extends Model
{
    protected $fillable = ['request_id','item_name','quantity','unit','unit_price','notes'];

    public function request(): BelongsTo { return $this->belongsTo(DepartmentPurchaseRequest::class, 'request_id'); }
}
