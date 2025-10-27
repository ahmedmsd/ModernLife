<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentPurchaseLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['request_id','causer_id','action','data','note','created_at'];
    protected $casts = ['data' => 'array','created_at'=>'datetime'];

    public function request(): BelongsTo { return $this->belongsTo(DepartmentPurchaseRequest::class, 'request_id'); }
    public function causer(): BelongsTo { return $this->belongsTo(User::class, 'causer_id'); }
}
