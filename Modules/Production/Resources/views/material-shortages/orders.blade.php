@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap">
            <div class="flex-grow-1 align-items-center mt-3">
                <x-forms.link-secondary :link="route('production.material-shortages.index', ['status_scope' => $statusScope, 'warehouse_id' => $warehouse->id, 'material_id' => $material->id, 'only_shortage' => 1])" class="mr-3 mb-2 float-left" icon="arrow-left">
                    @lang('production::app.materialShortageSummary')
                </x-forms.link-secondary>
            </div>
        </div>

        <div class="bg-white rounded p-4 mt-3">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <h5 class="mb-1">@lang('production::app.rawMaterialProduct')</h5>
                    <p class="mb-0 text-dark-grey">{{ $material->name }}</p>
                </div>
                <div class="col-md-3 mb-3">
                    <h5 class="mb-1">@lang('production::app.rawMaterialWarehouse')</h5>
                    <p class="mb-0 text-dark-grey">{{ $warehouse->name }}</p>
                </div>
                <div class="col-md-2 mb-3">
                    <h5 class="mb-1">@lang('production::app.materialTotalRequired')</h5>
                    <p class="mb-0 text-dark-grey">{{ $summary ? number_format((float) $summary['total_required'], 4, '.', ',') : '0.0000' }}</p>
                </div>
                <div class="col-md-2 mb-3">
                    <h5 class="mb-1">@lang('production::app.materialAvailableStock')</h5>
                    <p class="mb-0 text-dark-grey">{{ $summary ? number_format((float) $summary['available_stock'], 4, '.', ',') : '0.0000' }}</p>
                </div>
                <div class="col-md-2 mb-3">
                    <h5 class="mb-1">@lang('production::app.materialShortageToProcure')</h5>
                    <p class="mb-0 text-danger">{{ $summary ? number_format((float) $summary['shortage_to_procure'], 4, '.', ',') : '0.0000' }}</p>
                </div>
            </div>

            <div class="mt-2">
                <span class="badge badge-light mr-2">@lang('production::app.status'): {{ __('production::app.materialShortageStatusScopes.' . $statusScope) }}</span>
                @if ($summary && !empty($summary['unit_label_base']))
                    <span class="badge badge-light">@lang('production::app.baseUnit'): {{ $summary['unit_label_base'] }}</span>
                @endif
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            <table class="table table-hover border-0 mb-0">
                <thead>
                    <tr>
                        <th>@lang('app.id')</th>
                        <th>@lang('production::app.manufacturedProduct')</th>
                        <th>@lang('production::app.status')</th>
                        <th>@lang('production::app.plannedQty')</th>
                        <th>@lang('production::app.materialRequiredQuantity')</th>
                        <th>@lang('production::app.baseUnit')</th>
                        <th>@lang('production::app.materialRequirementSource')</th>
                        <th class="text-right">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orderRows as $row)
                        <tr>
                            <td>{{ $row['order_id'] }}</td>
                            <td>{{ $row['manufactured_product_name'] }}</td>
                            <td>{{ $row['order_status_label'] }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $row['planned_quantity'], 4, '.', ''), '0'), '.') ?: '0' }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $row['required_quantity'], 4, '.', ''), '0'), '.') ?: '0' }}</td>
                            <td>{{ $row['unit_label_base'] ?? '—' }}</td>
                            <td>{{ $row['source_label'] }}</td>
                            <td class="text-right">
                                <a href="{{ $row['order_url'] }}" class="text-dark-grey">@lang('production::app.openOrder')</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-lightest py-4">@lang('production::app.noOrdersInMaterialShortageSummary')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
