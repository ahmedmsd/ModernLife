<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    protected $fillable = [
        'zoho_so_id',
        'subject',
        'so_number',
        'status',
        'total_amount',
        'sub_total',
        'tax',
        'adjustment',
        'discount',
        'client_id',
        'zoho_module',
        'raw_data',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'tax' => 'decimal:2',
        'adjustment' => 'decimal:2',
        'discount' => 'decimal:2',
        'raw_data' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }
}
