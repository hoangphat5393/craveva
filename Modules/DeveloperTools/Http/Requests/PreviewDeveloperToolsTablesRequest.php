<?php

namespace Modules\DeveloperTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\DeveloperTools\Services\DbAccessPolicy;

class PreviewDeveloperToolsTablesRequest extends FormRequest
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
        ];
    }
}
