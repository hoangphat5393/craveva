@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar mb-3">
            <div>
                <x-forms.link-secondary :link="route('clients.index')" class="mr-3" icon="arrow-left">
                    @lang('app.back')
                </x-forms.link-secondary>
                <x-forms.link-secondary :link="route('clients.import')" class="openRightModal" icon="file-upload">
                    @lang('app.import')
                </x-forms.link-secondary>
            </div>
        </div>
        <x-cards.data :title="__('app.clientImportLog')">
            <div class="table-responsive">
                <table class="table table-hover border-0 w-100">
                    <thead>
                        <tr>
                            <th>@lang('app.date')</th>
                            <th>@lang('app.user')</th>
                            <th class="text-center">@lang('app.clientImportLogTotalJobs')</th>
                            <th class="text-center">@lang('app.clientImportLogProcessed')</th>
                            <th class="text-center">@lang('app.clientImportLogFailed')</th>
                            <th class="text-right">@lang('app.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log['completed_at']? \Carbon\Carbon::parse($log['completed_at'])->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format): '—' }}</td>
                                <td>{{ $log['user_name'] }}</td>
                                <td class="text-center">{{ $log['total_jobs'] }}</td>
                                <td class="text-center text-success">{{ $log['processed_jobs'] }}</td>
                                <td class="text-center {{ $log['failed_jobs'] > 0 ? 'text-danger' : '' }}">{{ $log['failed_jobs'] }}</td>
                                <td class="text-right">
                                    <a href="{{ route('clients.import_log.show', $log['batch_id']) }}" class="btn btn-secondary btn-sm openRightModal">@lang('app.view')</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">@lang('app.clientImportLogEmpty')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-cards.data>
    </div>
@endsection

@push('scripts')
    <script>
        // Copy button works in quick view modal (delegated)
        $('body').on('click', '.btn-copy-import-log', function() {
            var targetSelector = $(this).data('copy-target');
            var target = document.querySelector(targetSelector);
            if (target) {
                var text = target.innerText || target.textContent;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() {
                        window.Swal.fire({
                            icon: 'success',
                            title: '{{ __('app.copy') }}',
                            text: '{{ __('messages.copiedToClipboard') }}',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    });
                } else {
                    var textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    window.Swal.fire({
                        icon: 'success',
                        title: '{{ __('app.copy') }}',
                        text: '{{ __('messages.copiedToClipboard') }}',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            }
        });
    </script>
@endpush
