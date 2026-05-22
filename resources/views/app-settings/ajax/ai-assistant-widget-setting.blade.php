<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 ml-3 ">
    <div class="row">
        <div class="col-lg-12 mb-2">
            <p class="f-14 text-dark-grey">@lang('modules.accountSettings.aiAssistantWidgetHelp')</p>
        </div>
        <div class="col-lg-6 mb-0">
            <x-forms.text :fieldLabel="__('modules.accountSettings.aiAssistantWidgetAgentId')" :fieldPlaceholder="__('placeholders.id')" fieldName="ai_assistant_widget_agent_id" fieldId="ai_assistant_widget_agent_id" :fieldValue="global_setting()->ai_assistant_widget_agent_id" />
        </div>
        <div class="col-lg-6 mb-0">
            <x-forms.text :fieldLabel="__('modules.accountSettings.aiAssistantWidgetApiBase')" :fieldPlaceholder="__('modules.accountSettings.aiAssistantWidgetApiBasePlaceholder')" fieldName="ai_assistant_widget_api_base" fieldId="ai_assistant_widget_api_base" :fieldValue="global_setting()->ai_assistant_widget_api_base" />
        </div>
        <div class="col-lg-8 mb-0">
            <x-forms.password :fieldLabel="__('modules.accountSettings.aiAssistantWidgetApiKey')" :fieldPlaceholder="__('modules.accountSettings.aiAssistantWidgetApiKeyPlaceholder')" fieldName="ai_assistant_widget_api_key" fieldId="ai_assistant_widget_api_key" fieldValue="" :fieldHelp="__('modules.accountSettings.aiAssistantWidgetApiKeyHelp')" />
        </div>
        <div class="col-lg-12 mb-0">
            <x-forms.checkbox :fieldLabel="__('modules.accountSettings.aiAssistantWidgetRemoveApiKey')" fieldName="ai_assistant_widget_api_key_remove" fieldId="ai_assistant_widget_api_key_remove" fieldValue="1" :checked="false" />
        </div>
    </div>
</div>
<div class="w-100 border-top-grey set-btns ml-2">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-ai-assistant-widget-setting-form" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>
    </x-setting-form-actions>
</div>

<script>
    $('body').on('click', '#save-ai-assistant-widget-setting-form', function() {
        const url = "{{ route('app-settings.update', [global_setting()->id]) }}?page=ai-assistant-widget-setting";

        $.easyAjax({
            url: url,
            container: '#editSettings',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-ai-assistant-widget-setting-form",
            data: $('#editSettings').serialize(),
        })
    });
</script>
