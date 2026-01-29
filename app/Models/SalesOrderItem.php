<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sales_order_id',
        'zoho_line_item_id',
        'product_name',
        'product_id',
        'quantity',
        'list_price',
        'unit_price',
        'discount',
        'tax',
        'total',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'list_price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
