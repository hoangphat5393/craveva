@php
    use Modules\Production\Support\ProductionOrderStatusBadge;
@endphp
{!! ProductionOrderStatusBadge::html($status) !!}
