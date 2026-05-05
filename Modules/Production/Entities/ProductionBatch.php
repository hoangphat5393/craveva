<?php

namespace Modules\Production\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionBatch extends BaseModel
{
    use HasCompany;

    protected $table = 'production_batches';

    protected $fillable = [
        'company_id',
        'production_order_id',
        'batch_code',
        'notes',
        'posted_consumptions_at',
        'posted_receipt_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'posted_consumptions_at' => 'datetime',
            'posted_receipt_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    /**
     * @return HasMany<ProductionBatchConsumption, $this>
     */
    public function consumptions(): HasMany
    {
        return $this->hasMany(ProductionBatchConsumption::class, 'production_batch_id')->orderBy('line_order');
    }

    /**
     * @return HasMany<ProductionBatchOutput, $this>
     */
    public function outputs(): HasMany
    {
        return $this->hasMany(ProductionBatchOutput::class, 'production_batch_id');
    }
}
