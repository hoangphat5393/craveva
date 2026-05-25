@extends('layouts.app')

@section('content')
    <div class="w-100 d-flex">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card :withoutForm="true">
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link f-15 api-integration" href="{{ route('sales-order-settings.index') }}" role="tab" aria-selected="true">@lang('modules.orders.apiTab')
                            </a>
                            <a class="nav-item nav-link f-15 order-settings" href="{{ route('sales-order-settings.index') }}?tab=order-settings" role="tab" aria-selected="false">@lang('modules.orders.orderSettingsTab')
                            </a>
                        </div>
                    </nav>
                </div>
            </x-slot>

            @include($view)

        </x-setting-card>
    </div>
@endsection

@include('partials.settings-save-success-toast-script')

@push('scripts')
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
    <script>
        $('.nav-item').removeClass('active');
        const activeTab = "{{ $activeTab }}";
        $('.' + activeTab).addClass('active');

        $("body").on("click", "#tabs .nav a", function(event) {
            event.preventDefault();

            $('.nav-item').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            window.apiHttp.get(requestUrl)
                .then(function(response) {
                    if (response.status === 'success') {
                        window.history.pushState({}, '', requestUrl);
                        $('#nav-tabContent .flex-wrap').html(response.html);
                        init('#nav-tabContent');
                        if (response.activeTab === 'api-integration') {
                            initSalesOrderSettingsClipboard();
                        }
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                });
        });

        function initSalesOrderSettingsClipboard() {
            if (typeof ClipboardJS === 'undefined') {
                return;
            }
            var clipboard = new ClipboardJS('#nav-tabContent .btn-copy');
            clipboard.on('success', function(e) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        text: @json(__('app.copied')),
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        showClass: {
                            popup: 'swal2-noanimation',
                            backdrop: 'swal2-noanimation'
                        },
                    });
                }
                e.clearSelection();
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (activeTab === 'api-integration') {
                initSalesOrderSettingsClipboard();
            }
        });
    </script>
@endpush
