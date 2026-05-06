<?php

namespace Modules\Production\Entities;

use App\Models\BaseModel;
use App\Models\Order;
use App\Models\Product;
use App\Models\Project;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Warehouse\Entities\Warehouse;

class ProductionOrder extends BaseModel
{
    use HasCompany;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_RELEASED = 'released';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'production_orders';

    protected $fillable = [
        'company_id',
        'status',
        'output_product_id',
        'production_bom_id',
        'rm_warehouse_id',
        'fg_warehouse_id',
        'planned_quantity',
        'sales_order_id',
        'project_id',
        'created_by',
        'updated_by',
        'released_at',
        'completed_at',
        'bom_snapshot_at',
        'bom_snapshot_planned_quantity',
    ];

    protected function casts(): array
    {
        return [
            'planned_quantity' => 'float',
            'released_at' => 'datetime',
            'completed_at' => 'datetime',
            'bom_snapshot_at' => 'datetime',
            'bom_snapshot_planned_quantity' => 'float',
        ];
    }

    public function outputProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'output_product_id');
    }

    public function bom(): BelongsTo
    {
        return $this->belongsTo(ProductionBom::class, 'production_bom_id');
    }

    public function rmWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'rm_warehouse_id');
    }

    public function fgWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'fg_warehouse_id');
    }

    /**
     * @return HasMany<ProductionBatch, $this>
     */
    public function batches(): HasMany
    {
        return $this->hasMany(ProductionBatch::class, 'production_order_id');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'sales_order_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * @return HasMany<ProductionOrderBomSnapshotItem, $this>
     */
    public function bomSnapshotItems(): HasMany
    {
        return $this->hasMany(ProductionOrderBomSnapshotItem::class, 'production_order_id')->orderBy('sort_order')->orderBy('id');
    }
}
