@php
    $t = (string) ($type ?? 'normal');
@endphp
@if (in_array($t, ['normal', 'locked', 'scrap', 'transit'], true))
    {{ __('warehouse::app.warehouseType' . \Illuminate\Support\Str::studly($t)) }}
@else
    {{ ucfirst($t) }}
@endif
