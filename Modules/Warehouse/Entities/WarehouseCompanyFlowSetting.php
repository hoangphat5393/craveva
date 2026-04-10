<?php

namespace Modules\Warehouse\Entities;

use App\Models\BaseModel;
use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-company warehouse flow overrides. Intentionally no CompanyScope: resolved by explicit company_id
 * from documents, webhooks, and jobs.
 */
class WarehouseCompanyFlowSetting extends BaseModel
{
    protected $fillable = [
        'company_id',
        'allow_negative_stock',
        'strict_unit_conversion',
        'inbound_from_purchase_order_delivered',
        'inbound_from_delivery_order_received',
        'sales_outbound_enabled',
        'sales_outbound_mode',
        'ai_order_webhook_check_stock',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allow_negative_stock' => 'boolean',
            'strict_unit_conversion' => 'boolean',
            'inbound_from_purchase_order_delivered' => 'boolean',
            'inbound_from_delivery_order_received' => 'boolean',
            'sales_outbound_enabled' => 'boolean',
            'ai_order_webhook_check_stock' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
