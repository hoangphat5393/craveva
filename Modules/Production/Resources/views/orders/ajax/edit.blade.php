@php
    use Modules\Production\Support\ProductionProductSelectLabel;

    $defaultRedirectUrl = request()->input('redirect_url', url()->previous() ?: route('production.orders.show', $order));
@endphp

@unless (request()->ajax())
    <div class="d-flex justify-content-between action-bar flex-wrap border-bottom-grey pb-2 mb-3">
        <div class="align-items-center mt-3">
            <x-forms.link-secondary :link="route('production.orders.show', $order)" class="float-left" icon="arrow-left">
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
    <div class="col-lg-8">
        <form method="post" action="{{ route('production.orders.update', $order) }}" id="update-production-order-form" class="bg-white rounded p-4">
            @csrf
            @method('PUT')
            @include('sections.password-autocomplete-hide')
            <input type="hidden" name="redirect_url" value="{{ $defaultRedirectUrl }}">

            <h4 class="mb-3 f-21 font-weight-normal">@lang('app.edit') @lang('production::app.menuProductionOrders')</h4>

            <x-forms.select fieldId="output_product_id" :search="true" :fieldLabel="__('production::app.manufacturedProduct')" fieldName="output_product_id" fieldRequired="true">
                @foreach ($finishedGoods as $p)
                    @php
                        $fgSelectLabel = ProductionProductSelectLabel::forProduct($p);
                        $fgSku = trim((string) ($p->sku ?? ''));
                    @endphp
                    <option value="{{ $p->id }}" data-content="{{ $fgSelectLabel }}" @if ($fgSku !== '') data-tokens="{{ $fgSku }}" @endif @selected(old('output_product_id', $order->output_product_id) == $p->id)>{{ $fgSelectLabel }}</option>
                @endforeach
            </x-forms.select>

            <x-forms.select fieldId="production_bom_id" :search="true" :fieldLabel="__('production::app.bom') . ' (' . __('app.optional') . ')'" fieldName="production_bom_id" :fieldRequired="false">
                <option value="">—</option>
                @foreach ($boms as $bom)
                    <option value="{{ $bom->id }}" data-output-product-id="{{ $bom->output_product_id }}" @selected(old('production_bom_id', $order->production_bom_id) == $bom->id)>
                        {{ $bom->labelForSelect() }}
                    </option>
                @endforeach
            </x-forms.select>
            <p class="f-12 text-muted mt-12 mb-0">
                @lang('production::app.bomManageFromSettingsHint')
                <a href="{{ route('production.boms.index') }}">@lang('production::app.menuBillOfMaterials')</a>
            </p>

            <x-forms.select fieldId="rm_warehouse_id" :fieldLabel="__('production::app.rawMaterialWarehouse')" fieldName="rm_warehouse_id" fieldRequired="true">
                @foreach ($warehouses as $w)
                    <option value="{{ $w->id }}" @selected(old('rm_warehouse_id', $order->rm_warehouse_id) == $w->id)>{{ $w->name }}</option>
                @endforeach
            </x-forms.select>

            <x-forms.select fieldId="fg_warehouse_id" :fieldLabel="__('production::app.manufacturedProductWarehouse')" fieldName="fg_warehouse_id" fieldRequired="true">
                @foreach ($warehouses as $w)
                    <option value="{{ $w->id }}" @selected(old('fg_warehouse_id', $order->fg_warehouse_id) == $w->id)>{{ $w->name }}</option>
                @endforeach
            </x-forms.select>

            <div class="form-group my-3">
                <x-forms.label fieldId="planned_quantity" :fieldLabel="__('production::app.plannedQty')" fieldRequired="true" />
                <input type="number" step="0.0001" min="0.0001" name="planned_quantity" id="planned_quantity" class="form-control height-35 f-14" value="{{ old('planned_quantity', $order->planned_quantity) }}" required>
            </div>

            <x-forms.select fieldId="sales_order_id" :search="true" :fieldLabel="__('production::app.linkedSalesOrder')" fieldName="sales_order_id" :fieldRequired="false">
                <option value="">—</option>
                @foreach ($recentSalesOrders as $so)
                    <option value="{{ $so->id }}" @selected(old('sales_order_id', $order->sales_order_id) == $so->id)>
                        #{{ $so->id }} — {{ $so->order_number }} — {{ __('modules.invoices.' . $so->status) }}
                    </option>
                @endforeach
            </x-forms.select>
            <p class="f-12 text-muted mt-12 mb-0">@lang('production::app.linkedSalesOrderHint')</p>

            @if (config('production.ui.show_linked_project_on_order_form'))
                <x-forms.select fieldId="project_id" :fieldLabel="__('production::app.linkedProject')" fieldName="project_id" :fieldRequired="false">
                    <option value="">—</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}" @selected(old('project_id', $order->project_id) == $proj->id)>
                            #{{ $proj->id }} — {{ $proj->project_name }}
                        </option>
                    @endforeach
                </x-forms.select>
            @endif

            <div class="w-100 border-top-grey pt-3 mt-2 d-flex flex-wrap">
                <x-forms.button-primary id="update-production-order-button" class="mr-3" icon="check">
                    @lang('app.save')
                </x-forms.button-primary>
                <x-forms.button-cancel :link="route('production.orders.show', $order)" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
            </div>
        </form>
    </div>
</div>

@include('production::orders.partials.bom-fg-sync-script')

<script>
    (() => {
        const formSelector = '#update-production-order-form';
        const buttonSelector = '#update-production-order-button';
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

            window.apiHttp.postUrlEncoded("{{ route('production.orders.update', $order) }}", $(formSelector).serialize())
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
