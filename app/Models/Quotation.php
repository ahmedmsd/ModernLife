<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends Model
{
    protected $fillable = [
        'zoho_quote_id',
        'subject',
        'quote_number',
        'quote_stage',
        'valid_till',
        'total_amount',
        'sales_person',
        'sub_total',
        'tax',
        'adjustment',
        'discount',
        'client_id',
        'customer_name',
        'zoho_module',
        'contract_type',
        'raw_data',
        'quotation_pdf_url',
        'contract_pdf_url',
    ];

    protected $casts = [
        'valid_till' => 'date',
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
        return $this->hasMany(QuotationItem::class);
    }

    public function productionRequest(): HasOne
    {
        return $this->hasOne(ProductionRequest::class);
    }
}
