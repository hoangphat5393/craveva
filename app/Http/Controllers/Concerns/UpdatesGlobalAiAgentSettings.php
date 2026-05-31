<?php

namespace App\Http\Controllers\Concerns;

use App\Models\GlobalSetting;
use Illuminate\Http\Request;

trait UpdatesGlobalAiAgentSettings
{
    protected function updateAiWorkspaceSetting(Request $request): void
    {
        $this->updateAiEmbedCode($request, 'ai_workspace_embed_code');
    }

    protected function updateAiAssistantWidgetSetting(Request $request): void
    {
        $this->updateAiEmbedCode($request, 'ai_assistant_widget_embed_code');
    }

    protected function updateAiEmbedCode(Request $request, string $field): void
    {
        $globalSetting = GlobalSetting::first();
        $value = $request->input($field);
        $globalSetting->{$field} = filled($value) ? trim((string) $value) : null;
        $globalSetting->save();
        cache()->forget('global_setting');
    }
}
