<?php

namespace Modules\DeveloperTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\DeveloperTools\Services\DbAccessPolicy;

class StoreDeveloperToolsCredentialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $policy = app(DbAccessPolicy::class);
        $allowedModuleKeys = array_keys($policy->availableModulesForUi());

        return [
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in($allowedModuleKeys)],
            'tables' => ['nullable', 'array', 'max:5000'],
            'tables.*' => ['string', 'regex:/^[a-zA-Z0-9_]+$/'],
        ];
    }
}
