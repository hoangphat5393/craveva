<?php

declare(strict_types=1);

namespace Modules\Production\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Support\ProductionBomFirstPolicy;

trait ValidatesProductionOrderBomPolicy
{
    /**
     * @return array<string, mixed>
     */
    protected function productionBomIdRules(int $companyId): array
    {
        $existsRule = Rule::exists('production_boms', 'id')->where(function ($query) use ($companyId): void {
            $query->whereNull('company_id')->orWhere('company_id', $companyId);
        });

        if (ProductionBomFirstPolicy::requireBomOnOrder()) {
            return ['required', 'integer', $existsRule];
        }

        return ['nullable', 'integer', $existsRule];
    }

    protected function validateProductionOrderBomPolicy(Validator $validator): void
    {
        $bomId = $this->input('production_bom_id');
        if ($bomId === null || $bomId === '') {
            if (ProductionBomFirstPolicy::requireBomOnOrder()) {
                $validator->errors()->add('production_bom_id', __('production::app.bomRequired'));
            }

            return;
        }

        $companyId = (int) (company()?->id ?? 0);
        $bom = ProductionBom::query()
            ->withCount('items')
            ->where(function ($query) use ($companyId): void {
                $query->whereNull('company_id')->orWhere('company_id', $companyId);
            })
            ->find((int) $bomId);

        if ($bom === null) {
            return;
        }

        if ((int) ($bom->items_count ?? 0) < 1) {
            $validator->errors()->add('production_bom_id', __('production::app.bomHasNoLines'));
        }
    }
}
