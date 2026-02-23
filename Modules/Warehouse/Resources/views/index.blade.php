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
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <a href="{{ route('warehouse.create') }}" class="btn btn-outline btn-success btn-sm">@lang('warehouse::app.addNew') <i class="fa fa-plus" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover toggle-circle default footable-loaded footable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Default</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warehouses as $key => $warehouse)
                                <tr>
                                    <td>{{ $loop->iteration + ($warehouses->currentPage() - 1) * $warehouses->perPage() }}</td>
                                    <td>{{ $warehouse->name }}</td>
                                    <td>{{ $warehouse->code }}</td>
                                    <td>{{ $warehouse->address }}</td>
                                    <td>
                                        @if($warehouse->status == 'active')
                                            <label class="label label-success">Active</label>
                                        @else
                                            <label class="label label-danger">Inactive</label>
                                        @endif
                                    </td>
                                    <td>
                                        @if($warehouse->is_default)
                                            <i class="fa fa-check text-success"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group dropdown m-r-10">
                                            <button aria-expanded="false" data-toggle="dropdown" class="btn btn-default dropdown-toggle waves-effect waves-light" type="button"><i class="fa fa-gears "></i></button>
                                            <ul role="menu" class="dropdown-menu pull-right">
                                                <li><a href="{{ route('warehouse.edit', $warehouse->id) }}"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a></li>
                                                <li>
                                                    <a href="javascript:;" class="sa-params" data-user-id="{{ $warehouse->id }}"><i class="fa fa-times" aria-hidden="true"></i> Delete</a>
                                                    <form action="{{ route('warehouse.destroy', $warehouse->id) }}" method="POST" id="delete-warehouse-{{ $warehouse->id }}" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="empty-space" style="height: 200px;">
                                            <div class="empty-space-inner">
                                                <div class="icon" style="font-size: 30px"><i class="fa fa-warehouse"></i></div>
                                                <div class="title" style="font-size: 18px">No warehouses found</div>
                                                <div class="subtitle">
                                                    <a href="{{ route('warehouse.create') }}" class="btn btn-outline btn-success btn-sm">Create Warehouse <i class="fa fa-plus" aria-hidden="true"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-right">
                    {{ $warehouses->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('footer-script')
<script>
    $('.sa-params').click(function(){
        var id = $(this).data('user-id');
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this warehouse!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel please!",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm){
            if (isConfirm) {
                document.getElementById('delete-warehouse-' + id).submit();
            }
        });
    });
</script>
@endpush
