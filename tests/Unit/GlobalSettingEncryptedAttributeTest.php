<?php

use App\Models\GlobalSetting;

it('returns null for invalid optional AI encrypted settings', function () {
    $setting = new GlobalSetting;
    $setting->setRawAttributes([
        'ai_workspace_api_key' => 'invalid-encrypted-value',
        'ai_assistant_widget_api_key' => 'invalid-encrypted-value',
    ]);

    expect($setting->aiWorkspaceApiKey())->toBeNull()
        ->and($setting->aiAssistantWidgetApiKey())->toBeNull();
});

it('returns decryptable optional AI settings', function () {
    $setting = new GlobalSetting;
    $setting->ai_workspace_api_key = 'workspace-secret';
    $setting->ai_assistant_widget_api_key = 'assistant-secret';

    expect($setting->aiWorkspaceApiKey())->toBe('workspace-secret')
        ->and($setting->aiAssistantWidgetApiKey())->toBe('assistant-secret');
});
