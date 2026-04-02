<?php

namespace Modules\Warehouse\Entities;

use App\Models\BaseModel;

class WarehouseSyncReconciliationLog extends BaseModel
{
    protected $table = 'warehouse_sync_reconciliation_logs';

    protected $fillable = [
        'company_id',
        'report_date',
        'report_type',
        'summary_json',
    ];
}
