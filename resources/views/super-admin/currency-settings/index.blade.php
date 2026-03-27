@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        {{-- SAAS --}}
        @if(user()->is_superadmin)
            <x-super-admin.setting-sidebar :activeMenu="$activeSettingMenu"/>
        @else
            <x-setting-sidebar :activeMenu="$activeSettingMenu"/>
        @endif

        <x-setting-card>

            <x-slot name="alert">
                <div class="row">
                    <div class="col-md-12">
                        <x-alert type="info" icon="info-circle">
                            @lang('messages.exchangeRateNote')
                        </x-alert>
                    </div>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <a href="javascript:;" class="mr-1 mb-2 mb-lg-0 mb-md-0 btn-primary rounded f-14 p-2"
                           icon="plus" id="addNewCurrency">@lang('modules.currencySettings.addNewCurrency')</a>
                        <x-forms.button-secondary icon="key" id="addCurrencyExchangeKey">
                            @lang('modules.accountSettings.currencyConverterKey')
                        </x-forms.button-secondary>
                    </div>
                </div>
            </x-slot>

            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                        @lang('modules.accountSettings.currencySetting')</h2>
                </div>
            </x-slot>

            {{-- include tabs here --}}
            @include($view)

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
<script>


    $('#addNewCurrency').click(function () {
        const url = "{{ route('superadmin.settings.global-currency-settings.create') }}";

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $("body").on("click", ".edit-channel", function () {
        var currencyId = $(this).data('currency-id');
        var url = "{{ route('superadmin.settings.global-currency-settings.edit', ':id') }}";

        url = url.replace(':id', currencyId);
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

   $("body").on("click", "#editSettings .nav a", function(event) {
        event.preventDefault();

        $('.nav-item').removeClass('active');
        $(this).addClass('active');

        const requestUrl = this.href;

        window.history.pushState({ id: requestUrl }, '', requestUrl);
        $.easyBlockUI("#nav-tabContent");
        window.apiHttp.get(requestUrl)
            .then(function(response) {
                if (response.status == "success") {
                    $('#nav-tabContent').html(response.html);
                    init('#nav-tabContent');
                }
            })
            .catch(function (err) { $.handleApiFormError(err); })
            .finally(function () { $.easyUnblockUI("#nav-tabContent"); });
    });

    // Delete currency
    $('body').on('click', '.delete-table-row', function() {
        var id = $(this).data('currency-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                let url = "{{ route('superadmin.settings.global-currency-settings.destroy', ':id') }}";
                url = url.replace(':id', id);

                const token = "{{ csrf_token() }}";

                $.easyBlockUI();
                window.apiHttp.delete(url, token)
                    .then(function(response) {
                        if (response.status === "success") {
                            $('.row'+id).fadeOut();
                        }
                    })
                    .catch(function (err) { $.handleApiFormError(err); })
                    .finally(function () { $.easyUnblockUI(); });
            }
        });
    });

    // update exchange rates
    $('#update-exchange-rates').click(function() {
        var url = "{{ route('superadmin.settings.currency_settings.update_exchange_rates') }}";
        $.easyBlockUI();
        window.apiHttp.get(url)
            .then(function(response) {
                if (response.status == "success") {
                    $.unblockUI();
                    window.location.reload();
                }
            })
            .catch(function (err) { $.handleApiFormError(err); })
            .finally(function () { $.easyUnblockUI(); });
    });

    // Currency code converter modal open script
    $('#addCurrencyExchangeKey').click(function() {
        const url = "{{ route('superadmin.settings.currency_settings.exchange_key') }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

        $("body").on("click", "#save-currency-format", function() {
        const fmtUrl = "{{route('superadmin.settings.currency_settings.update_currency_format')}}";
        const qs = $('#editSettings').serialize();
        const sep = fmtUrl.indexOf('?') >= 0 ? '&' : '?';
        const $fmtBtn = $('#save-currency-format');
        const fmtPrev = $fmtBtn.html();
        $.easyBlockUI('#editSettings');
        $fmtBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
        window.apiHttp.get(fmtUrl + sep + qs)
            .then(function (response) {
                if (response.status === 'success') {
                    if (response.action === 'redirect' && response.url) {
                        window.location.href = response.url;
                    } else if (typeof response.message !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            text: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            customClass: { confirmButton: 'btn btn-primary' },
                            showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' }
                        });
                    }
                }
            })
            .catch(function (err) { $.handleApiFormError(err); })
            .finally(function () {
                $.easyUnblockUI('#editSettings');
                $fmtBtn.prop('disabled', false).html(fmtPrev);
            });
    });

</script>
@endpush
