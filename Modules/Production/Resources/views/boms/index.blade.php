@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                <div class="d-flex flex-wrap align-items-center mt-3">
                    <form method="get" action="{{ route('production.boms.index') }}" class="form-inline d-flex flex-wrap align-items-center mr-3 mb-2">
                        <label class="f-14 text-dark-grey mr-2 mb-0">@lang('production::app.fgProduct')</label>
                        <select name="output_product_id" class="form-control select-picker height-35 f-14" data-container="body" data-size="8" onchange="this.form.submit()">
                            <option value="">@lang('app.all')</option>
                            @foreach ($finishedGoodsFilter as $p)
                                <option value="{{ $p->id }}" @selected(request('output_product_id') == $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </form>

                    @if (in_array(user()->permission('add_production_orders'), ['all', 'added', 'owned', 'both'], true))
                        <x-forms.link-primary :link="route('production.boms.create')" class="mr-3 mb-2 float-left" icon="plus">
                            @lang('production::app.newBom')
                        </x-forms.link-primary>
                    @endif
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-3 mb-0">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-3 mb-0">{{ session('error') }}</div>
        @endif

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">ID</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.fgProduct')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.bomVersion')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.bomCode')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.bomLines')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.bomDefault')</th>
                        <th class="f-14 text-dark-grey text-right">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($boms as $bom)
                        <tr>
                            <td class="f-14">{{ $bom->id }}</td>
                            <td class="f-14">{{ $bom->outputProduct?->name ?? '—' }}</td>
                            <td class="f-14">{{ $bom->version }}</td>
                            <td class="f-14">{{ $bom->code ?: '—' }}</td>
                            <td class="f-14">{{ (int) $bom->items_count }}</td>
                            <td class="f-14">{{ $bom->is_default ? __('app.yes') : __('app.no') }}</td>
                            <td class="text-right">
                                <a href="{{ route('production.boms.show', $bom) }}" class="btn btn-secondary rounded f-14 btn-sm">
                                    <i class="fa fa-eye mr-1"></i>@lang('app.view')
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-5">
                                <x-cards.no-record icon="cubes" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($boms->hasPages())
            <div class="my-3 d-flex justify-content-end">
                {{ $boms->links() }}
            </div>
        @endif
    </div>
@endsection
