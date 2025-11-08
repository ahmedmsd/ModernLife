<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class MaintenanceRequest extends Model
{
    protected $table = 'maintenance_requests';
    protected $primaryKey = 'id';
    protected $fillable = [
        'project_id',
        'client_id',
        'requested_by',
        'request_date',
        'details',
        'images',
        'status',
        'current_owner_role',
        'current_owner_user_id',
        'sent_to_owner_at',
        'received_by_owner_at',
        'closed_at',
        'created_by',
        'acknowledged_at',
        'expected_start_at',
        'actual_start_at',
        'expected_end_at',
        'actual_end_at',
        'client_confirmation_file',
    ];

    protected $casts = [
        'images'               => 'array',
        'request_date'         => 'date',
        'sent_to_owner_at'     => 'datetime',
        'received_by_owner_at' => 'datetime',
        'closed_at'            => 'datetime',
        'acknowledged_at'      => 'datetime',
        'expected_start_at'    => 'datetime',
        'actual_start_at'      => 'datetime',
        'expected_end_at'      => 'datetime',
        'actual_end_at'        => 'datetime',
    ];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function client(): BelongsTo  { return $this->belongsTo(Client::class,'client_id','client_id'); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function ownerUser(): BelongsTo { return $this->belongsTo(User::class, 'current_owner_user_id'); }
    public function comments(): HasMany { return $this->hasMany(MaintenanceComment::class, 'maintenance_request_id')->latest(); }
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * @param string[] $casts
     * @return MaintenanceRequest
     */
    public function setCasts(array $casts): MaintenanceRequest
    {
        $this->casts = $casts;
        return $this;
    }

}
