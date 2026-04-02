<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesHistoryLine extends Model
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'sales_history_id',
        'shipment_date',
        'client_id',
        'client_details_id',
        'product_id',
        'quantity',
        'quantity_abs',
        'amount',
        'unit_price',
        'is_return',
        'currency_id',
        'source_sheet_name',
        'source_row_hash',
        'net_sales_volume_raw',
        'net_sales_amount_raw',
    ];

    protected $casts = [
        'shipment_date' => 'date',
        'is_return' => 'boolean',
        'quantity' => 'float',
        'quantity_abs' => 'float',
        'amount' => 'float',
        'unit_price' => 'float',
        'net_sales_volume_raw' => 'float',
        'net_sales_amount_raw' => 'float',
    ];

    public function salesHistory(): BelongsTo
    {
        return $this->belongsTo(SalesHistory::class, 'sales_history_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function clientDetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_details_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
