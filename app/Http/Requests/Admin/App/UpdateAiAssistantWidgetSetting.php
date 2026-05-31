<?php

namespace App\Http\Requests\Admin\App;

use App\Http\Requests\CoreRequest;
use App\Models\GlobalSetting;

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
            'ai_assistant_widget_embed_code' => ['nullable', 'string', 'max:50000'],
        ];
    }
}
