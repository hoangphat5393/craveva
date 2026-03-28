<?php

namespace Modules\Warehouse\Entities;

use App\Models\BaseModel;
use App\Models\Invoice;
use App\Models\InvoiceItems;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceWarehouseStockPosting extends BaseModel
{
    protected $table = 'invoice_warehouse_stock_postings';

    protected $fillable = [
        'company_id',
        'invoice_id',
        'invoice_item_id',
        'warehouse_id',
        'product_id',
        'quantity',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItems::class, 'invoice_item_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
