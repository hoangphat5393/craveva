@extends('layouts.app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ __($pageTitle) }}</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}">@lang('app.menu.home')</a></li>
                <li class="active">{{ __($pageTitle) }}</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="white-box">
                <div class="row m-b-10">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <a href="{{ route('warehouse.stock.create') }}" class="btn btn-outline btn-success btn-sm">@lang('warehouse::app.addStock') <i class="fa fa-plus" aria-hidden="true"></i></a>
                            <a href="{{ route('warehouse.transfer.create') }}" class="btn btn-outline btn-info btn-sm m-l-5">@lang('warehouse::app.transferStock') <i class="fa fa-exchange" aria-hidden="true"></i></a>
                        </div>
                    </div>
                    <div class="col-sm-6 text-right">
                        <form action="{{ route('warehouse.stock.index') }}" method="GET" class="form-inline">
                            <div class="form-group">
                                <select name="warehouse_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">-- @lang('warehouse::app.allWarehouses') --</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ $warehouseId == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="@lang('app.search')..." value="{{ request('search') }}">
                            </div>
                            <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover toggle-circle default footable-loaded footable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('warehouse::app.product')</th>
                                <th>@lang('warehouse::app.warehouse')</th>
                                <th>@lang('warehouse::app.quantity')</th>
                                <th>@lang('app.updatedAt')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stocks as $key => $stock)
                                <tr>
                                    <td>{{ $loop->iteration + ($stocks->currentPage() - 1) * $stocks->perPage() }}</td>
                                    <td>
                                        {{ $stock->product->name }} <br>
                                        <small class="text-muted">{{ $stock->product->sku }}</small>
                                    </td>
                                    <td>{{ $stock->warehouse->name }}</td>
                                    <td>
                                        <span class="font-bold {{ $stock->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $stock->quantity }}
                                        </span>
                                    </td>
                                    <td>{{ $stock->updated_at->format('d-m-Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="empty-space" style="height: 200px;">
                                            <div class="empty-space-inner">
                                                <div class="icon" style="font-size: 30px"><i class="fa fa-cubes"></i></div>
                                                <div class="title" style="font-size: 18px">@lang('warehouse::app.noStockFound')</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-right">
                    {{ $stocks->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
