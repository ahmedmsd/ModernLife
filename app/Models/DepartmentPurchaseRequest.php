<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentPurchaseRequest extends Model
{
    protected $fillable = [
        'request_number','department_id','requested_by','title','description',
        'status','priority','total_estimated_cost','attachment',
        'submitted_at','factory_approved_at','factory_rejected_at','sent_to_purchasing_at',
        'purchased_at','delivered_at','factory_approved_by','purchased_by','delivered_to','delivery_attachment',
    ];

    protected $casts = [
        'submitted_at'           => 'datetime',
        'factory_approved_at'    => 'datetime',
        'factory_rejected_at'    => 'datetime',
        'sent_to_purchasing_at'  => 'datetime',
        'purchased_at'           => 'datetime',
        'delivered_at'           => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (! $m->request_number) {
                $next = (int) (static::query()->max('id') ?? 0) + 1;
                $m->request_number = 'DPR-' . now()->format('Ymd') . '-' . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
            }
        });

        static::saved(function (self $m) {
            $sum = $m->items()->selectRaw('SUM(COALESCE(quantity,0) * COALESCE(unit_price,0)) as s')->value('s');
            $m->total_estimated_cost = $sum ?: 0;
            $m->saveQuietly();
        });
    }

    public function items(): HasMany { return $this->hasMany(DepartmentPurchaseItem::class, 'request_id'); }
    public function logs(): HasMany { return $this->hasMany(DepartmentPurchaseLog::class, 'request_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class,'department_id','dept_id'); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function factoryApprover(): BelongsTo { return $this->belongsTo(User::class, 'factory_approved_by'); }
    public function purchaser(): BelongsTo { return $this->belongsTo(User::class, 'purchased_by'); }
    public function receiver(): BelongsTo { return $this->belongsTo(User::class, 'delivered_to'); }
}
