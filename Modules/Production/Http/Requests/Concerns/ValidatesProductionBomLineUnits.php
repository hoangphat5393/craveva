<?php

declare(strict_types=1);

namespace Modules\Production\Http\Requests\Concerns;

use Modules\Production\Support\ProductionBomComponentUnitOptions;

trait ValidatesProductionBomLineUnits
{
    protected function mergeDefaultBomLineUnitIds(): void
    {
        $companyId = (int) (company()?->id ?? 0);
        if ($companyId <= 0) {
            return;
        }

        $options = app(ProductionBomComponentUnitOptions::class);
        $items = collect($this->input('items', []))
            ->map(function (mixed $line) use ($companyId, $options): array {
                $line = is_array($line) ? $line : [];
                $productId = (int) ($line['component_product_id'] ?? 0);
                if ($productId <= 0 || filled($line['unit_id'] ?? null)) {
                    return $line;
                }

                $defaultUnitId = $options->defaultUnitIdForProduct($companyId, $productId);
                if ($defaultUnitId !== null) {
                    $line['unit_id'] = $defaultUnitId;
                }

                return $line;
            })
            ->all();

        $this->merge(['items' => $items]);
    }

    protected function validateProductionBomLineUnits($validator): void
    {
        $companyId = (int) (company()?->id ?? 0);
        if ($companyId <= 0) {
            return;
        }

        $options = app(ProductionBomComponentUnitOptions::class);

        foreach ($this->input('items', []) as $index => $line) {
            $productId = (int) data_get($line, 'component_product_id');
            if ($productId <= 0) {
                continue;
            }

            $unitId = data_get($line, 'unit_id');
            if (! $options->isAllowedUnit($companyId, $productId, $unitId)) {
                $validator->errors()->add(
                    'items.'.$index.'.unit_id',
                    __('production::app.bomComponentUnitInvalid'),
                );
            }
        }
    }
}
