<?php

namespace Modules\Production\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionReworkOrder extends BaseModel
{
    use HasCompany;

    public const STATUS_REQUESTED = 'requested';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_COMPLETED = 'completed';

    protected $table = 'production_rework_orders';

    protected $fillable = [
        'company_id',
        'source_production_batch_id',
        'rework_production_order_id',
        'requested_quantity',
        'approved_quantity',
        'status',
        'reason',
        'decision_note',
        'requested_by',
        'approved_by',
        'approved_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_quantity' => 'float',
            'approved_quantity' => 'float',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function sourceBatch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'source_production_batch_id');
    }

    public function reworkOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'rework_production_order_id');
    }
}
