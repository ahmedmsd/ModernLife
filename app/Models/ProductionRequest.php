<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasStatusScopes;
class ProductionRequest extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasStatusScopes;
    protected $fillable = [
        'project_name',
        'client_id',
        'showroom_id',
        'agreement_file',
        'additional_work_file',
        'status',
        'created_by',
        'submitted_at',
        'request_type',
        'quotation_id',
        'current_phase',
        'phase_status',
        'current_owner_role',
        'current_owner_user_id',
        'sent_to_owner_at',
        'received_by_owner_at',
    ];

    protected $casts = [
        'submitted_at'         => 'datetime',
        'sent_to_owner_at'     => 'datetime',
        'received_by_owner_at' => 'datetime',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
        'request_type'         => 'string'

    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function showroom(): BelongsTo
    {
        return $this->belongsTo(Showroom::class, 'showroom_id', 'id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'production_request_id', 'id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProductionRequestFile::class, 'production_request_id', 'id');
    }

    public function productionRequestFiles(): HasMany
    {
        return $this->files();
    }

    public function currentOwnerUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'current_owner_user_id','id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProductionRequestLog::class, 'production_request_id', 'id')
            ->orderByDesc('happened_at');
    }

    public function getTotalPriceAttribute(): float
    {
        foreach (['total_price', 'price', 'amount', 'contract_amount'] as $k) {
            $v = $this->{$k} ?? null;
            if (!is_null($v)) {
                return (float) $v;
            }
        }
        return 0.0;
    }
}
