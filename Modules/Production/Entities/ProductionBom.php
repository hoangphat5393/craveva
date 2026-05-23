<?php

namespace Modules\Production\Entities;

use App\Models\BaseModel;
use App\Models\Product;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionBom extends BaseModel
{
    use HasCompany;

    protected $table = 'production_boms';

    protected $fillable = [
        'company_id',
        'output_product_id',
        'version',
        'code',
        'effective_from',
        'effective_to',
        'is_default',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_default' => 'boolean',
        ];
    }

    public function outputProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'output_product_id');
    }

    /**
     * @return HasMany<ProductionBomItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProductionBomItem::class, 'production_bom_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<ProductionOrder, $this>
     */
    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class, 'production_bom_id');
    }

    /**
     * Human-readable label for production order BOM dropdowns (finished good + code).
     */
    public function labelForSelect(): string
    {
        $fgName = trim((string) ($this->outputProduct?->name ?? ''));

        if ($fgName === '') {
            $fgName = __('production::app.manufacturedProduct').' #'.$this->output_product_id;
        }

        $code = trim((string) ($this->code ?? ''));

        if ($code !== '') {
            return $fgName.' — '.$code;
        }

        return $fgName.' '.__('production::app.bomSelectIdLabel', ['id' => $this->id]);
    }

    /**
     * Compact BOM label for production order list (code · version), without repeating FG name.
     */
    public function listLabelForOrderIndex(): string
    {
        $code = trim((string) ($this->code ?? ''));
        $version = trim((string) ($this->version ?? ''));

        if ($code !== '' && $version !== '') {
            return $code.' · '.$version;
        }

        if ($code !== '') {
            return $code;
        }

        if ($version !== '') {
            return $version;
        }

        return __('production::app.bomSelectIdLabel', ['id' => $this->id]);
    }
}
