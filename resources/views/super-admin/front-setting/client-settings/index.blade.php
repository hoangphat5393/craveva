@extends('layouts.app')

@section('content')
    <!-- SETTINGS START -->
    <div class="w-100 d-flex">

        <x-super-admin.front-setting-sidebar :activeMenu="$activeSettingMenu" />

        @include('super-admin.common.language-selector-with-view',[ 'route' => 'superadmin.front-settings.client-settings.index'])

    </div>
    <hr>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script>

        $("body").on("click", "#saveFrontSetting", function() {
            const $btn = $('#saveFrontSetting');
            const prev = $btn.html();
            $.easyBlockUI('#editSettings');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
            window.apiHttp.postUrlEncoded("{{ route('superadmin.front-settings.client_setting.update_lang') }}", $('#editSettings').serialize())
                .then(function (response) {
                    addBadge(response);
                })
                .catch(function (err) { $.handleApiFormError(err); })
                .finally(function () {
                    $.easyUnblockUI('#editSettings');
                    $btn.prop('disabled', false).html(prev);
                });
        });


        $('.cropper').on('dropify.fileReady', function(e) {
            var inputId = $(this).find('input').attr('id');
            var url = "{{ route('cropper', ':element') }}";
            url = url.replace(':element', inputId);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });


    </script>
@endpush
