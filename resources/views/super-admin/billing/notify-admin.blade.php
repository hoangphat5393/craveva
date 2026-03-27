<x-auth>
    <x-form id="notify-admin" action="#" class="ajax-form">

        @if ($isNotified)
            <div class="alert alert-success">
                @lang('superadmin.packageIssueNotified')
            </div>
        @else
            <div id="alert" class="text-center">
                <h3 class="mb-1 f-w-500">@lang('superadmin.issueWithCompany')</h3>
                <h4 class="mb-4 mt-3 heading-h4 text-danger">@lang('superadmin.issueWithCompanyText')</h4>
                <button type="button" id="submit-notify-admin"
                    class="btn-primary f-w-500 rounded w-100 height-50 f-18">
                    @lang('superadmin.issueNotifyButton')
                </button>
            </div>
        @endif

    </x-form>


    <x-slot name="scripts">
        <script>
            $(document).ready(function () {

                $('#submit-notify-admin').click(function () {
                    var $btn = $('#submit-notify-admin');
                    var prev = $btn.html();
                    $.easyBlockUI('.login_box');
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
                    window.apiHttp.postUrlEncoded("{{ route('superadmin.notify.admin.submit') }}", $('#notify-admin').serialize()).then(function (response) {
                        if (response.status === 'success') {
                            if (response.action === 'redirect' && response.url) {
                                window.location.href = response.url;
                            } else if (typeof response.message !== 'undefined') {
                                var ele = $('.login_box').find('#alert');
                                var html = '<div class="alert alert-success">' + response.message + '</div>';
                                if (ele.length === 0) {
                                    $('.login_box').find('.form-group:first').before('<div id="alert">' + html + '</div>');
                                } else {
                                    ele.html(html);
                                }
                            }
                        }
                    }).catch(function (err) {
                        $.handleApiFormError(err);
                    }).finally(function () {
                        $.easyUnblockUI('.login_box');
                        $btn.prop('disabled', false).html(prev);
                    });
                });

            });
        </script>
    </x-slot>

</x-auth>
