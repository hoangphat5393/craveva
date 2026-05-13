<?php

namespace App\Http\Requests\Integrations;

use App\Models\ClientDetails;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAiOrderWebhookRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (empty($this->all())) {
            $decoded = json_decode((string) $this->getContent(), true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->merge($decoded);
            }
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $companyId = (int) $this->input('company_id', 0);
            if ($companyId <= 0) {
                return;
            }

            $rawCode = $this->input('client_code');
            $code = is_string($rawCode) ? trim($rawCode) : '';
            $hasCode = $code !== '';

            $rawId = $this->input('client_id');
            $hasId = $rawId !== null && $rawId !== '' && is_numeric($rawId);
            $userIdFromInput = $hasId ? (int) $rawId : null;

            if (! $hasCode && ! $hasId) {
                return;
            }

            $userFromCode = null;

            if ($hasCode) {
                $userFromCode = $this->lookupUserIdFromClientCode($companyId, $code);
                if ($userFromCode === null) {
                    $validator->errors()->add('client_code', __('modules.orders.apiWebhookClientCodeInvalid'));

                    return;
                }
                if (! $this->isActiveUserInCompany($companyId, $userFromCode)) {
                    $validator->errors()->add('client_code', __('modules.orders.apiWebhookClientUserInactive'));

                    return;
                }
            }

            if ($hasId && ! $this->isActiveUserInCompany($companyId, (int) $userIdFromInput)) {
                $validator->errors()->add('client_id', __('modules.orders.apiWebhookClientIdInvalid'));

                return;
            }

            if ($hasCode && $hasId && $userFromCode !== $userIdFromInput) {
                $validator->errors()->add('client_id', __('modules.orders.apiWebhookClientIdCodeMismatch'));

                return;
            }

            $resolved = $hasCode ? $userFromCode : $userIdFromInput;
            if ($resolved === null) {
                $validator->errors()->add('client_id', __('modules.orders.apiWebhookClientIdOrCodeRequired'));

                return;
            }

            $this->merge(['client_id' => (int) $resolved]);
        });

        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $companyId = (int) $this->input('company_id', 0);
            if ($companyId <= 0) {
                return;
            }

            $this->validateWebhookLineProducts($validator, $companyId);
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_code.required_without' => __('modules.orders.apiWebhookClientIdOrCodeRequired'),
            'client_id.required_without' => __('modules.orders.apiWebhookClientIdOrCodeRequired'),
            'client_id.exists' => __('modules.orders.apiWebhookClientUserInactive'),
        ];
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'client_code' => ['nullable', 'string', 'max:100', 'required_without:client_id'],
            'client_id' => ['nullable', 'integer', 'required_without:client_code'],
            'external_event_id' => ['nullable', 'string', 'max:191'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'company_address_id' => ['nullable', 'integer', 'exists:company_addresses,id'],
            'status' => ['nullable', Rule::in(['pending', 'on-hold', 'failed', 'processing', 'completed', 'canceled', 'refunded'])],
            'discount_type' => ['nullable', Rule::in(['percent', 'fixed'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'check_stock' => ['sometimes', 'boolean'],
            'warehouse_ids' => ['nullable', 'array'],
            'warehouse_ids.*' => ['integer', 'min:1'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.item_summary' => ['nullable', 'string'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.unit_id' => ['nullable', 'integer', 'exists:unit_types,id'],
            'items.*.sku' => ['nullable', 'string', 'max:191'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.taxes' => ['nullable', 'array'],
        ];
    }

    private function lookupUserIdFromClientCode(int $companyId, string $code): ?int
    {
        $userIdsInCompany = User::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->pluck('id');

        $userId = ClientDetails::withoutGlobalScopes()
            ->where('client_code', $code)
            ->where(function ($query) use ($companyId, $userIdsInCompany): void {
                $query->where('client_details.company_id', $companyId)
                    ->orWhereIn('client_details.user_id', $userIdsInCompany);
            })
            ->value('user_id');

        return $userId !== null ? (int) $userId : null;
    }

    private function isActiveUserInCompany(int $companyId, int $userId): bool
    {
        return User::withoutGlobalScopes()
            ->where('id', $userId)
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Resolve each line to a catalog product for this company: exact `name` first, then `sku`.
     * When `product_id` is already sent, it must belong to the same company_id.
     *
     * @param  Validator  $validator
     */
    private function validateWebhookLineProducts($validator, int $companyId): void
    {
        $items = $this->input('items', []);
        if (! is_array($items)) {
            return;
        }

        $out = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                $validator->errors()->add('items.' . $index, __('modules.orders.apiWebhookItemInvalid'));
                $out[] = [];

                continue;
            }

            $row = $item;

            $existingPid = isset($row['product_id']) ? (int) $row['product_id'] : 0;
            if ($existingPid > 0) {
                $belongs = Product::withoutGlobalScopes()
                    ->where('id', $existingPid)
                    ->where('company_id', $companyId)
                    ->exists();
                if (! $belongs) {
                    $validator->errors()->add('items.' . $index . '.product_id', __('modules.orders.apiWebhookProductIdWrongCompany'));
                }
                $out[] = $row;

                continue;
            }

            $name = isset($row['item_name']) && is_string($row['item_name']) ? trim($row['item_name']) : '';
            $skuRaw = $row['sku'] ?? null;
            $sku = is_string($skuRaw) ? trim($skuRaw) : '';

            $product = null;
            if ($name !== '') {
                $product = Product::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->where('name', $name)
                    ->first();
            }
            if ($product === null && $sku !== '') {
                $product = Product::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->where('sku', $sku)
                    ->first();
            }

            if ($product === null) {
                $validator->errors()->add('items.' . $index . '.item_name', __('modules.orders.apiWebhookProductNotFound'));
            } else {
                $row['product_id'] = $product->id;
            }

            $out[] = $row;
        }

        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $this->merge(['items' => $out]);
    }
}
