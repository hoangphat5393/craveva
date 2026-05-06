<?php

namespace Modules\Production\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Support\ProductionTenantAccess;

class UpdateProductionBomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ProductionTenantAccess::tenantMayUseProduction()
            && in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ProductionBom|null $bom */
        $bom = $this->route('bom');

        $companyId = (int) (company()?->id ?? 0);
        $bomId = $bom instanceof ProductionBom ? (int) $bom->id : 0;

        return [
            'output_product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('company_id', $companyId)],
            'version' => ['required', 'string', 'max:32', Rule::unique('production_boms', 'version')->ignore($bomId)->where(function ($query) use ($companyId): void {
                $query->where('company_id', $companyId)->where('output_product_id', (int) $this->input('output_product_id'));
            })],
            'code' => ['nullable', 'string', 'max:64'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_default' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.component_product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('company_id', $companyId)],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->filter(fn (mixed $line): bool => filled(data_get($line, 'component_product_id')))
            ->values()
            ->all();

        $this->merge([
            'items' => $items,
            'is_default' => $this->boolean('is_default'),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $fg = (int) $this->input('output_product_id');
            foreach ($this->input('items', []) as $index => $line) {
                if ((int) data_get($line, 'component_product_id') === $fg) {
                    $validator->errors()->add('items.'.$index.'.component_product_id', __('production::app.bomComponentMustDifferFromOutput'));
                }
            }
        });
    }
}
