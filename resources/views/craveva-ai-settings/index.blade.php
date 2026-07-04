@extends('layouts.app')

@section('content')
    <!-- CRAVEVA AI SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-super-admin.setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link f-15 ai-workspace-setting" href="{{ route('craveva-ai-settings.index') }}?tab=ai-workspace-setting" role="tab" aria-controls="nav-ai-workspace" aria-selected="true" ajax="false">@lang('app.menu.aiWorkspace')
                            </a>

                            <a class="nav-item nav-link f-15 ai-assistant-widget-setting" href="{{ route('craveva-ai-settings.index') }}?tab=ai-assistant-widget-setting" role="tab" aria-controls="nav-ai-assistant-widget" aria-selected="true" ajax="false">@lang('app.menu.aiAssistantWidget')
                            </a>
                        </div>
                    </nav>
                </div>
            </x-slot>

            @include($view)

        </x-setting-card>

    </div>
    <!-- CRAVEVA AI SETTINGS END -->
@endsection

@push('scripts')
    <script>
        $('.nav-item').removeClass('active');
        const activeTab = "{{ $activeTab }}";
        $('.' + activeTab).addClass('active');

        $("body").on("click", "#editSettings .nav a", function(event) {
            event.preventDefault();

            $('.nav-item').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            historyPush(requestUrl);
            $.easyBlockUI("#nav-tabContent");

            window.apiHttp.get(requestUrl)
                .then(function(response) {
                    if (response.status === "success") {
                        $('#nav-tabContent .flex-wrap').html(response.html);
                        init('#nav-tabContent');
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                })
                .finally(function() {
                    $.easyUnblockUI("#nav-tabContent");
                });
        });
    </script>
@endpush
