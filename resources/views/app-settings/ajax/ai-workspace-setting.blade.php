<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 ml-3 ">
    <div class="row">
        <div class="col-lg-12 mb-2">
            <p class="f-14 text-dark-grey">@lang('modules.accountSettings.aiWorkspacePageHelp')</p>
        </div>
        <div class="col-lg-6 mb-0">
            <x-forms.text :fieldLabel="__('modules.accountSettings.aiWorkspaceAgentId')" :fieldPlaceholder="__('placeholders.id')" fieldName="ai_workspace_agent_id" fieldId="ai_workspace_agent_id" :fieldValue="global_setting()->ai_workspace_agent_id" />
        </div>
        <div class="col-lg-6 mb-0">
            <x-forms.text :fieldLabel="__('modules.accountSettings.aiWorkspaceApiBase')" :fieldPlaceholder="__('modules.accountSettings.aiWorkspaceApiBasePlaceholder')" fieldName="ai_workspace_api_base" fieldId="ai_workspace_api_base" :fieldValue="global_setting()->ai_workspace_api_base" />
        </div>
        <div class="col-lg-8 mb-0">
            <x-forms.password :fieldLabel="__('modules.accountSettings.aiWorkspaceApiKey')" :fieldPlaceholder="__('modules.accountSettings.aiWorkspaceApiKeyPlaceholder')" fieldName="ai_workspace_api_key" fieldId="ai_workspace_api_key" fieldValue="" :fieldHelp="__('modules.accountSettings.aiWorkspaceApiKeyHelp')" />
        </div>
        <div class="col-lg-12 mb-0">
            <x-forms.checkbox :fieldLabel="__('modules.accountSettings.aiWorkspaceRemoveApiKey')" fieldName="ai_workspace_api_key_remove" fieldId="ai_workspace_api_key_remove" fieldValue="1" :checked="false" />
        </div>
    </div>
</div>
<div class="w-100 border-top-grey set-btns ml-2">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-ai-workspace-setting-form" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>
    </x-setting-form-actions>
</div>

<script>
    $('body').on('click', '#save-ai-workspace-setting-form', function() {
        const url = "{{ route('app-settings.update', [global_setting()->id]) }}?page=ai-workspace-setting";

        $.easyAjax({
            url: url,
            container: '#editSettings',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-ai-workspace-setting-form",
            data: $('#editSettings').serialize(),
        })
    });
</script>
