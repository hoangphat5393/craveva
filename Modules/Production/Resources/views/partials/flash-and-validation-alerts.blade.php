@php
    $hasFeedback = session('success') || session('error') || session('warning') || $errors->any();
@endphp

@if ($hasFeedback)
    <div id="production-feedback-alerts" class="mt-3 mb-0" tabindex="-1" role="alert" aria-live="assertive">
        @if (session('success'))
            <div class="alert alert-success mb-2">
                <i class="fa fa-check-circle mr-1"></i>{{ session('success') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning mb-2">
                <i class="fa fa-exclamation-triangle mr-1"></i>{{ session('warning') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger mb-2">
                <strong class="d-block">@lang('production::app.formActionFailedTitle')</strong>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger mb-0">
                <strong class="d-block mb-1">
                    <i class="fa fa-times-circle mr-1"></i>@lang('production::app.formValidationFailedTitle')
                </strong>
                <p class="mb-2 f-13">@lang('production::app.formValidationFailedSubtitle')</p>
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                @isset($productionFeedbackJumpAnchor)
                    <p class="mb-0 mt-2 f-13">
                        <a href="#{{ $productionFeedbackJumpAnchor }}" class="text-danger font-weight-bold">
                            @lang('production::app.formValidationJumpToForm')
                        </a>
                    </p>
                @endisset
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            $(function() {
                var el = document.getElementById('production-feedback-alerts');
                if (el) {
                    el.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    el.focus({
                        preventScroll: true
                    });
                }
            });
        </script>
    @endpush
@endif
