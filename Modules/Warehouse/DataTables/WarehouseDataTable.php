<?php

namespace Modules\Warehouse\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Modules\Warehouse\Entities\Warehouse;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class WarehouseDataTable extends BaseDataTable
{
    private string $addWarehousePermission;

    private string $editWarehousePermission;

    private string $deleteWarehousePermission;

    private string $viewWarehousePermission;

    private bool $canViewWarehouse;

    private bool $canBulkWarehouseAction;

    public function __construct()
    {
        parent::__construct();

        $this->viewWarehousePermission = (string) user()->permission('view_warehouses');
        $this->addWarehousePermission = (string) user()->permission('add_warehouses');
        $this->editWarehousePermission = (string) user()->permission('edit_warehouses');
        $this->deleteWarehousePermission = (string) user()->permission('delete_warehouses');
        $this->canViewWarehouse = $this->viewWarehousePermission !== 'none' && $this->viewWarehousePermission !== '';
        $this->canBulkWarehouseAction = in_array($this->editWarehousePermission, ['all', 'added'], true)
            || in_array($this->deleteWarehousePermission, ['all', 'added'], true);
    }

    /**
     * @param  Builder<Warehouse>  $query
     */
    public function dataTable($query): DataTableAbstract
    {
        $datatables = datatables()->eloquent($query);

        $datatables->addColumn('check', fn (Warehouse $row): string => $this->checkBox($row, ! $this->canBulkWarehouseAction));

        $datatables->editColumn('name', function (Warehouse $row): string {
            return '<h5 class="mb-0 f-13 text-darkest-grey"><a href="'.route('warehouse.show', [$row->id]).'" class="text-darkest-grey">'.e($row->name).'</a></h5>';
        });

        $datatables->editColumn('code', fn (Warehouse $row): string => $row->code ?: '—');

        $datatables->editColumn('address', function (Warehouse $row): string {
            $address = $row->address ? Str::limit($row->address, 60) : '—';

            return '<span class="text-dark-grey">'.e($address).'</span>';
        });

        $datatables->editColumn('warehouse_type', function (Warehouse $row): string {
            $typeLabel = trim(view('warehouse::partials.warehouse-type-label', [
                'type' => $row->warehouse_type ?? 'normal',
            ])->render());

            return '<span class="badge badge-light">'.$typeLabel.'</span>';
        });

        $datatables->editColumn('status', function (Warehouse $row): string {
            if (in_array($this->editWarehousePermission, ['all', 'added'], true)) {
                $status = '<select class="form-control select-picker change-warehouse-status" data-size="8" data-warehouse-id="'.$row->id.'" data-current-status="'.e($row->status).'">';
                $status .= '<option '.($row->status === 'active' ? 'selected' : '').' value="active" data-content="<i class=\'fa fa-circle mr-2 text-light-green\'></i> '.e(__('app.active')).'">'.e(__('app.active')).'</option>';
                $status .= '<option '.($row->status === 'inactive' ? 'selected' : '').' value="inactive" data-content="<i class=\'fa fa-circle mr-2 text-red\'></i> '.e(__('app.inactive')).'">'.e(__('app.inactive')).'</option>';
                $status .= '</select>';

                return $status;
            }

            if ($row->status === 'active') {
                return '<i class="fa fa-circle mr-1 text-light-green f-10"></i> '.e(__('app.active'));
            }

            return '<i class="fa fa-circle mr-1 text-red f-10"></i> '.e(__('app.inactive'));
        });

        $datatables->editColumn('is_default', function (Warehouse $row): string {
            if ($row->is_default) {
                return '<i class="fa fa-check-circle text-success f-16" title="'.e(__('warehouse::app.isDefault')).'"></i>';
            }

            return '<span class="text-lightest">—</span>';
        });

        $datatables->addColumn('action', function (Warehouse $row): string {
            if (
                ! $this->canViewWarehouse
                && ! in_array($this->editWarehousePermission, ['all', 'added'], true)
                && ! in_array($this->deleteWarehousePermission, ['all', 'added'], true)
            ) {
                return '<span class="text-lightest">—</span>';
            }

            $action = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="warehouse-actions-'.$row->id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="warehouse-actions-'.$row->id.'" tabindex="0">';

            if ($this->canViewWarehouse) {
                $action .= '<a class="dropdown-item" href="'.route('warehouse.show', [$row->id]).'"><i class="fa fa-eye mr-2 text-dark-grey"></i>'.e(__('app.view')).'</a>';
            }

            if (in_array($this->editWarehousePermission, ['all', 'added'], true)) {
                $action .= '<a class="dropdown-item openRightModal" href="'.route('warehouse.edit', [$row->id]).'" data-redirect-url="'.route('warehouse.index').'"><i class="fa fa-edit mr-2 text-dark-grey"></i>'.e(__('app.edit')).'</a>';
            }

            if (in_array($this->deleteWarehousePermission, ['all', 'added'], true)) {
                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-warehouse-id="'.$row->id.'"><i class="fa fa-trash-alt mr-2 text-dark-grey"></i>'.e(__('app.delete')).'</a>';
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });

        $datatables->addIndexColumn();
        $datatables->smart(false);
        $datatables->setRowId(fn (Warehouse $row): string => 'row-'.$row->id);
        $datatables->rawColumns(['action', 'address', 'check', 'is_default', 'name', 'status', 'warehouse_type']);

        return $datatables;
    }

    /**
     * @return Builder<Warehouse>
     */
    public function query(Warehouse $model): Builder
    {
        $request = $this->request();

        $model = $model->newQuery()->select('warehouses.*');

        if (! is_null($request->status) && $request->status !== 'all' && $request->status !== '') {
            $model->where('warehouses.status', $request->status);
        }

        if (($request->searchText ?? '') !== '') {
            $term = '%'.$request->searchText.'%';
            $model->where(function (Builder $query) use ($term): void {
                $query->where('warehouses.name', 'like', $term)
                    ->orWhere('warehouses.code', 'like', $term)
                    ->orWhere('warehouses.address', 'like', $term);
            });
        }

        if (! $request->has('order')) {
            $model->orderBy('warehouses.id', 'desc');
        }

        return $model;
    }

    public function html()
    {
        $dataTable = $this->setBuilder('warehouse-table', 2)
            ->parameters([
                'order' => [[2, 'desc']],
                'initComplete' => 'function () {
                    try {
                        if (window.LaravelDataTables && window.LaravelDataTables["warehouse-table"] && window.LaravelDataTables["warehouse-table"].buttons) {
                            window.LaravelDataTables["warehouse-table"].buttons().container().appendTo("#table-actions");
                        }
                    } catch (error) {
                        console.error("Warehouse DataTable init error:", error);
                    }
                }',
                'fnDrawCallback' => 'function () {
                    try {
                        $("#warehouse-table .select-picker").selectpicker();
                        $("body").tooltip({
                            selector: \'[data-toggle="tooltip"]\'
                        });
                    } catch (error) {
                        console.error("Warehouse DataTable draw error:", error);
                    }
                }',
            ]);

        $buttons = [
            Button::make([
                'extend' => 'colvis',
                'text' => '<i class="fa fa-columns"></i> '.trans('app.columns'),
                'columns' => ':not(:first):not(:last)',
            ]),
        ];

        if (canDataTableExport()) {
            array_unshift($buttons, Button::make([
                'extend' => 'excel',
                'text' => '<i class="fa fa-file-export"></i> '.trans('app.exportExcel'),
            ]));
        }

        $dataTable->buttons($buttons);

        return $dataTable;
    }

    protected function getColumns(): array
    {
        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'visible' => $this->canBulkWarehouseAction,
            ],
            '#' => [
                'data' => 'DT_RowIndex',
                'orderable' => false,
                'searchable' => false,
                'visible' => ! showId(),
                'title' => '#',
            ],
            __('app.id') => [
                'data' => 'id',
                'name' => 'warehouses.id',
                'title' => __('app.id'),
                'visible' => showId(),
            ],
            __('warehouse::app.name') => [
                'data' => 'name',
                'name' => 'warehouses.name',
                'title' => __('warehouse::app.name'),
            ],
            __('warehouse::app.code') => [
                'data' => 'code',
                'name' => 'warehouses.code',
                'title' => __('warehouse::app.code'),
            ],
            __('warehouse::app.address') => [
                'data' => 'address',
                'name' => 'warehouses.address',
                'title' => __('warehouse::app.address'),
            ],
            __('warehouse::app.warehouseType') => [
                'data' => 'warehouse_type',
                'name' => 'warehouses.warehouse_type',
                'title' => __('warehouse::app.warehouseType'),
            ],
            __('warehouse::app.statusLabel') => [
                'data' => 'status',
                'name' => 'warehouses.status',
                'title' => __('warehouse::app.statusLabel'),
                'exportable' => false,
            ],
            __('warehouse::app.isDefault') => [
                'data' => 'is_default',
                'name' => 'warehouses.is_default',
                'title' => __('warehouse::app.isDefault'),
                'exportable' => false,
            ],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }
}
