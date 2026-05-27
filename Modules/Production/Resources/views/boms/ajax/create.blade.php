@php
    $defaultRedirectUrl = request()->input('redirect_url') ?? (request()->input('redirectUrl') ?? url()->previous()) ?: route('production.boms.index');
@endphp

@unless (request()->ajax())
    <div class="d-flex justify-content-between action-bar flex-wrap border-bottom-grey pb-2 mb-3">
        <div class="align-items-center mt-3">
            <x-forms.link-secondary :link="route('production.boms.index')" class="float-left" icon="arrow-left">
                @lang('app.back')
            </x-forms.link-secondary>
        </div>
    </div>
@endunless

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-10">
        <form method="post" action="{{ route('production.boms.store') }}" id="save-production-bom-form" class="bg-white rounded p-4">
            @csrf
            @include('sections.password-autocomplete-hide')
            <input type="hidden" name="redirect_url" value="{{ $defaultRedirectUrl }}">

            <h4 class="mb-3 f-21 font-weight-normal">@lang('production::app.newBom')</h4>

            @include('production::boms.partials.form', ['bom' => null])

            <div class="w-100 border-top-grey pt-3 mt-3 d-flex flex-wrap">
                <x-forms.button-primary id="save-production-bom-button" class="mr-3" icon="check">
                    @lang('app.save')
                </x-forms.button-primary>
                <x-forms.button-cancel :link="route('production.boms.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
            </div>
        </form>
    </div>
</div>

@if (request()->ajax())
    @stack('scripts')
@endif

<script>
    (() => {
        const formSelector = '#save-production-bom-form';
        const buttonSelector = '#save-production-bom-button';
        const tableId = 'production-boms-table';

        const handleSuccess = (response) => {
            if (response.status !== 'success') {
                return;
            }

            if ($(RIGHT_MODAL).hasClass('show')) {
                $(RIGHT_MODAL).modal('hide');

                if ($('#' + tableId).length && window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].draw(true);

                    return;
                }
            }

            window.location.href = response.redirectUrl;
        };

        const submitForm = () => {
            const $button = $(buttonSelector);
            $button.prop('disabled', true);
            $.easyBlockUI(formSelector);

            window.apiHttp.postUrlEncoded("{{ route('production.boms.store') }}", $(formSelector).serialize())
                .then(handleSuccess)
                .catch(function(error) {
                    $.handleApiFormError(error);
                })
                .finally(function() {
                    $button.prop('disabled', false);
                    $.easyUnblockUI(formSelector);
                });
        };

        $(function() {
            if (typeof $.fn.selectpicker === 'function') {
                $(formSelector).find('.select-picker').selectpicker();
            }

            $(formSelector).on('submit', function(event) {
                event.preventDefault();
                submitForm();
            });

            $(buttonSelector).on('click', function(event) {
                event.preventDefault();
                $(formSelector).trigger('submit');
            });

            init(RIGHT_MODAL);
        });
    })();
</script>
