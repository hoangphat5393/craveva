<div class="p-4 col-lg-12 col-md-12 ntfcn-tab-content-left w-100">
    <div class="row">
        <div class="col-lg-12 mb-2">
            <p class="f-14 text-dark-grey">{{ __('modules.accountSettings.aiAssistantWidgetEmbedHelp') }}</p>
        </div>
        <div class="col-lg-12 mb-0">
            <div class="form-group my-3">
                <x-forms.label fieldId="ai_assistant_widget_embed_code" :fieldLabel="__('modules.accountSettings.aiAssistantWidgetEmbedCode')" />
                <textarea class="form-control f-13 font-monospace" rows="16" name="ai_assistant_widget_embed_code" id="ai_assistant_widget_embed_code" placeholder="{{ __('modules.accountSettings.aiAssistantWidgetEmbedPlaceholder') }}">{{ old('ai_assistant_widget_embed_code', global_setting()->ai_assistant_widget_embed_code) }}</textarea>
                <small class="f-12 text-dark-grey d-block mt-1">{{ __('modules.accountSettings.aiAssistantWidgetEmbedHint') }}</small>
            </div>
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
        const url = "{{ route('craveva-ai-settings.update', [global_setting()->id]) }}?page=ai-assistant-widget-setting";

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
