<?php

namespace App\Services\Estimates;

use App\Models\Estimate;
use App\Models\EstimateBomLine;
use App\Models\Product;
use Illuminate\Http\Request;

class EstimateBomLineSync
{
    /**
     * @return array{error: string|null, lines: list<array<string, mixed>>}
     */
    public function parseFromRequest(Request $request): array
    {
        $productIds = (array) $request->input('bom_product_id', []);
        $materialNames = (array) $request->input('bom_material_name', []);
        $quantities = (array) $request->input('bom_quantity', []);
        $unitIds = (array) $request->input('bom_unit_id', []);
        $unitCosts = (array) $request->input('bom_unit_cost', []);
        $notes = (array) $request->input('bom_notes', []);
        $lineIds = (array) $request->input('bom_line_id', []);

        $lines = [];
        $rowCount = max(
            count($productIds),
            count($materialNames),
            count($quantities),
            count($unitCosts),
        );

        for ($index = 0; $index < $rowCount; $index++) {
            $productId = isset($productIds[$index]) && $productIds[$index] !== ''
                ? (int) $productIds[$index]
                : null;
            $materialName = trim((string) ($materialNames[$index] ?? ''));
            $quantityRaw = $quantities[$index] ?? null;
            $unitCostRaw = $unitCosts[$index] ?? null;
            $unitId = isset($unitIds[$index]) && $unitIds[$index] !== ''
                ? (int) $unitIds[$index]
                : null;
            $lineNote = trim((string) ($notes[$index] ?? ''));

            $hasQuantity = $quantityRaw !== null && $quantityRaw !== '';
            $hasCost = $unitCostRaw !== null && $unitCostRaw !== '';
            $hasProduct = $productId !== null && $productId > 0;
            $hasMaterialName = $materialName !== '';

            if (! $hasProduct && ! $hasMaterialName && ! $hasQuantity && ! $hasCost) {
                continue;
            }

            if (! $hasProduct && ! $hasMaterialName) {
                return ['error' => __('modules.estimates.bomMaterialRequired'), 'lines' => []];
            }

            if (! $hasQuantity || ! is_numeric($quantityRaw) || (float) $quantityRaw <= 0) {
                return ['error' => __('modules.estimates.bomQuantityRequired'), 'lines' => []];
            }

            if (! $hasCost || ! is_numeric($unitCostRaw) || (float) $unitCostRaw < 0) {
                return ['error' => __('modules.estimates.bomUnitCostRequired'), 'lines' => []];
            }

            $product = null;
            if ($hasProduct) {
                $product = Product::query()
                    ->where('company_id', company()->id)
                    ->find($productId);
                if ($product === null) {
                    return ['error' => __('messages.itemNotFound'), 'lines' => []];
                }

                if ($materialName === '') {
                    $materialName = $product->name;
                }

                if ($unitId === null && $product->unit_id) {
                    $unitId = (int) $product->unit_id;
                }
            }

            $quantity = round((float) $quantityRaw, 4);
            $unitCost = round((float) $unitCostRaw, 4);
            $lineTotal = round($quantity * $unitCost, 4);

            $lines[] = [
                'id' => isset($lineIds[$index]) && $lineIds[$index] !== '' ? (int) $lineIds[$index] : null,
                'product_id' => $product?->id,
                'material_name' => $materialName,
                'quantity' => $quantity,
                'unit_id' => $unitId,
                'unit_cost' => $unitCost,
                'line_total' => $lineTotal,
                'notes' => $lineNote !== '' ? $lineNote : null,
            ];
        }

        return ['error' => null, 'lines' => $lines];
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function sync(Estimate $estimate, array $lines): void
    {
        $companyId = (int) $estimate->company_id;
        $persistedIds = [];

        foreach ($lines as $sortOrder => $line) {
            $existingId = $line['id'] ?? null;
            $bomLine = null;

            if ($existingId) {
                $bomLine = EstimateBomLine::query()
                    ->where('estimate_id', $estimate->id)
                    ->where('company_id', $companyId)
                    ->find($existingId);
            }

            if ($bomLine === null) {
                $bomLine = new EstimateBomLine;
                $bomLine->company_id = $companyId;
                $bomLine->estimate_id = $estimate->id;
            }

            $bomLine->product_id = $line['product_id'] ?? null;
            $bomLine->material_name = (string) $line['material_name'];
            $bomLine->quantity = $line['quantity'];
            $bomLine->unit_id = $line['unit_id'] ?? null;
            $bomLine->unit_cost = $line['unit_cost'];
            $bomLine->line_total = $line['line_total'];
            $bomLine->sort_order = $sortOrder + 1;
            $bomLine->notes = $line['notes'] ?? null;
            $bomLine->save();

            $persistedIds[] = $bomLine->id;
        }

        EstimateBomLine::query()
            ->where('estimate_id', $estimate->id)
            ->where('company_id', $companyId)
            ->when($persistedIds !== [], fn ($query) => $query->whereNotIn('id', $persistedIds))
            ->when($persistedIds === [], fn ($query) => $query)
            ->delete();
    }
}
