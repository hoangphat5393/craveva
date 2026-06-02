@php
    use Modules\Production\Support\ProductionBomFirstPolicy;

    $soPrefill = $salesOrderPrefill ?? null;
    $bomFirstWorkflow = ProductionBomFirstPolicy::enabled();
    $defaultOutputProductId = old('output_product_id', $soPrefill['output_product_id'] ?? null);
    $defaultBomId = old('production_bom_id', $soPrefill['production_bom_id'] ?? null);
    $defaultPlannedQty = old('planned_quantity', $soPrefill['planned_quantity'] ?? 1);
    $defaultRedirectUrl = request()->input('redirect_url') ?? (request()->input('redirectUrl') ?? url()->previous()) ?: route('production.orders.index');
@endphp

@unless (request()->ajax())
    <div class="d-flex justify-content-between action-bar flex-wrap border-bottom-grey pb-2 mb-3">
        <div class="align-items-center mt-3">
            <x-forms.link-secondary :link="route('production.orders.index')" class="float-left" icon="arrow-left">
                @lang('app.back')
            </x-forms.link-secondary>
        </div>
    </div>
@endunless

@if (!empty($soPrefill['hint']))
    <div class="alert alert-info f-14">
        {{ $soPrefill['hint'] }}
        @if (!empty($soPrefill['estimate_id']))
            <a href="{{ route('estimates.show', $soPrefill['estimate_id']) }}" class="alert-link openRightModal ml-1">@lang('app.view') @lang('app.quotation_ui.singular')</a>
        @endif
    </div>
@endif

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
        <form method="post" action="{{ route('production.orders.store') }}" id="save-production-order-form" class="bg-white rounded p-4" data-bom-first="{{ $bomFirstWorkflow ? '1' : '0' }}" data-bom-disable-fg="{{ ProductionBomFirstPolicy::bomFirstDisableFgSelect() ? '1' : '0' }}">
            @csrf
            @include('sections.password-autocomplete-hide')
            <input type="hidden" name="redirect_url" value="{{ $defaultRedirectUrl }}">

            <h4 class="mb-3 f-21 font-weight-normal">@lang('production::app.newOrder')</h4>

            @include('production::orders.partials.order-bom-header-fields', [
                'finishedGoods' => $finishedGoods,
                'boms' => $boms,
                'defaultOutputProductId' => $defaultOutputProductId,
                'defaultBomId' => $defaultBomId,
            ])

            @include('production::orders.partials.order-warehouse-row', [
                'warehouses' => $warehouses,
            ])

            <div class="form-group my-3">
                <x-forms.label fieldId="planned_quantity" :fieldLabel="__('production::app.plannedQty')" fieldRequired="true" />
                <input type="number" step="0.0001" min="0.0001" name="planned_quantity" id="planned_quantity" class="form-control height-35 f-14" value="{{ $defaultPlannedQty }}" required>
            </div>

            @include('production::orders.partials.bom-preview-panel')

            <x-forms.select fieldId="sales_order_id" :search="true" :fieldLabel="__('production::app.linkedSalesOrder')" fieldName="sales_order_id" :fieldRequired="false">
                <option value="">—</option>
                @foreach ($recentSalesOrders as $so)
                    <option value="{{ $so->id }}" @selected(old('sales_order_id', $prefillSalesOrderId ?? null) == $so->id)>
                        #{{ $so->id }} — {{ $so->order_number }} — {{ __('modules.invoices.' . $so->status) }}
                    </option>
                @endforeach
            </x-forms.select>
            <p class="f-12 text-muted mt-12 mb-0">@lang('production::app.linkedSalesOrderHint')</p>

            @if (config('production.ui.show_linked_project_on_order_form'))
                <x-forms.select fieldId="project_id" :fieldLabel="__('production::app.linkedProject')" fieldName="project_id" :fieldRequired="false">
                    <option value="">—</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}" @selected(old('project_id') == $proj->id)>
                            #{{ $proj->id }} — {{ $proj->project_name }}
                        </option>
                    @endforeach
                </x-forms.select>
            @endif

            <div class="w-100 border-top-grey pt-3 mt-2 d-flex flex-wrap">
                <x-forms.button-primary id="save-production-order-button" class="mr-3" icon="check">
                    @lang('production::app.saveDraft')
                </x-forms.button-primary>
                <x-forms.button-cancel :link="route('production.orders.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
            </div>
        </form>
    </div>
</div>

@include('production::orders.partials.bom-fg-sync-script')
@include('production::orders.partials.bom-preview-script')

<script>
    (() => {
        const formSelector = '#save-production-order-form';
        const buttonSelector = '#save-production-order-button';
        const tableId = 'production-orders-table';

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

            window.apiHttp.postUrlEncoded("{{ route('production.orders.store') }}", $(formSelector).serialize())
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
