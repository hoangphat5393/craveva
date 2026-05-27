@php
    use Modules\Production\Support\ProductionBomFirstPolicy;
@endphp

@if (ProductionBomFirstPolicy::showBomPreviewOnOrderForm())
    <div id="production-order-bom-preview" class="border rounded p-3 my-3 bg-light" data-preview-url="{{ route('production.orders.bom-preview') }}" data-placeholder="@lang('production::app.bomPreviewSelectBom')">
        <p class="f-13 text-muted mb-0">@lang('production::app.bomPreviewSelectBom')</p>
    </div>
@endif
