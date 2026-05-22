<?php

namespace App\Http\Requests\Admin\App;

use App\Http\Requests\CoreRequest;
use App\Models\GlobalSetting;
use Illuminate\Contracts\Validation\Validator;

class UpdateAiAssistantWidgetSetting extends CoreRequest
{
    public function authorize(): bool
    {
        return ! GlobalSetting::validateSuperAdmin('manage_superadmin_app_settings');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ai_assistant_widget_agent_id' => ['nullable', 'string', 'max:32', 'regex:/^[a-f0-9]{24}$/i'],
            'ai_assistant_widget_api_base' => ['nullable', 'string', 'url', 'max:255'],
            'ai_assistant_widget_api_key' => ['nullable', 'string', 'max:500'],
            'ai_assistant_widget_api_key_remove' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $agent = $this->input('ai_assistant_widget_agent_id');
            $base = $this->input('ai_assistant_widget_api_base');
            $hasAgent = filled($agent);
            $hasBase = filled($base);

            if ($hasAgent xor $hasBase) {
                if (! $hasBase) {
                    $validator->errors()->add('ai_assistant_widget_api_base', __('validation.required'));
                }
                if (! $hasAgent) {
                    $validator->errors()->add('ai_assistant_widget_agent_id', __('validation.required'));
                }
            }
        });
    }
}
