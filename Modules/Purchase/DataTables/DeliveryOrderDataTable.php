<?php

namespace Modules\Purchase\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Support\FlowPermission;
use Modules\Purchase\Support\GrnRuntime;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class DeliveryOrderDataTable extends BaseDataTable
{
    /**
     * Datatable state can persist legacy column names in browser storage.
     * Normalize them to runtime-safe aliases so ORDER BY never targets removed tables.
     */
    private function normalizeLegacyRequestColumns(): void
    {
        $request = $this->request();
        $columns = $request->input('columns');

        if (! is_array($columns)) {
            return;
        }

        $legacyToAlias = [
            'delivery_orders.delivery_number' => 'delivery_number',
            'delivery_orders.delivery_date' => 'delivery_date',
            'grns.grn_number' => 'delivery_number',
            'grns.grn_date' => 'delivery_date',
        ];

        $updated = false;
        foreach ($columns as $index => $column) {
            $name = $column['name'] ?? null;
            if (! is_string($name) || ! isset($legacyToAlias[$name])) {
                continue;
            }

            $columns[$index]['name'] = $legacyToAlias[$name];
            $updated = true;
        }

        if ($updated) {
            request()->merge(['columns' => $columns]);
        }
    }

    private function grnRouteName(string $action): string
    {
        $prefix = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'delivery-orders' : 'grn';

        return $prefix . '.' . $action;
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Build DataTable class.
     *
     * @param  mixed  $query  Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($row) {
                $showRoute = $this->grnRouteName('show');
                $editRoute = $this->grnRouteName('edit');
                $downloadRoute = $this->grnRouteName('download');
                $canView = FlowPermission::allowsAlias('grn.view');
                $canUpdate = FlowPermission::allowsAlias('grn.update');
                $canDelete = FlowPermission::allowsAlias('grn.delete');
                $isReceived = (string) $row->status === 'received' || (bool) $row->inbound_stock_applied;
                $action = '<div class="task_view">';
                $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if ($canView) {
                    $action .= '<a href="' . route($showRoute, $row->id) . '" class="dropdown-item f-14 text-dark">
                                    <i class="fa fa-eye mr-2"></i>' . trans('app.view') . '
                                </a>';
                }

                if ($canUpdate && ! $isReceived) {
                    $action .= '<a class="dropdown-item f-14 text-dark openRightModal" href="' . route($editRoute, $row->id) . '">
                                    <i class="fa fa-edit mr-2"></i>' . trans('app.edit') . '
                                </a>';
                }

                if ($canView) {
                    $action .= '<a class="dropdown-item f-14 text-dark" href="' . route($downloadRoute, $row->id) . '">
                                    <i class="fa fa-download mr-2"></i>' . trans('app.download') . '
                                </a>';
                }

                if ($canDelete && ! $isReceived) {
                    $action .= '<a class="dropdown-item f-14 text-dark delete-table-row" href="javascript:;" data-id="' . $row->id . '">
                                    <i class="fa fa-trash mr-2"></i>' . trans('app.delete') . '
                                </a>';
                }

                $action .= '</div></div></div>';

                return $action;
            })
            ->editColumn('delivery_number', function ($row) {
                $canView = FlowPermission::allowsAlias('grn.view');
                $showRoute = $this->grnRouteName('show');
                $title = e($row->delivery_number);
                if (! $canView) {
                    return '<div class="media align-items-center">
                                <div class="media-body">
                                    <h5 class="mb-0 f-13 text-darkest-grey">' . $title . '</h5>
                                </div>
                            </div>';
                }

                return '<div class="media align-items-center">
                            <div class="media-body">
                        <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route($showRoute, $row->id) . '">' . $title . '</a></h5>
                        </div>
                      </div>';
            })
            ->editColumn('delivery_date', function ($row) {
                return ! is_null($row->delivery_date) ? Carbon::parse($row->delivery_date)->translatedFormat(company()->date_format) : '----';
            })
            ->editColumn('vendor_name', function ($row) {
                if ($row->vendor_name) {
                    // vendor_id is in purchase_orders table, so we need to grab it.
                    // But we joined tables, so purchase_vendors.id should be available if we selected it.
                    // However, to be safe with IDs overlap, we should select purchase_vendors.id as vendor_id explicitly in query.
                    return '<a href="' . route('vendors.show', [$row->vendor_id]) . '" class="text-dark-grey">' . $row->vendor_name . '</a>';
                }

                return '-';
            })
            ->editColumn('status', function ($row) {
                $canChangeStatus = FlowPermission::allowsAlias('grn.change_status');
                $statusLocked = (string) $row->status === 'received' || (bool) $row->inbound_stock_applied;
                $status = '<div class="dropdown bootstrap-select form-control select-picker change-do-status">';
                $status .= '<select class="form-control select-picker change-do-status" data-delivery-id="' . $row->id . '"' . ($canChangeStatus && ! $statusLocked ? '' : ' disabled') . '>';

                $status .= '<option value="draft" ' . ($row->status == 'draft' ? 'selected' : '') .
                    ' data-content="<i class=\'fa fa-circle mr-2 text-dark\'></i> Draft">Draft</option>';

                $status .= '<option value="inbound" ' . ($row->status == 'inbound' ? 'selected' : '') .
                    ' data-content="<i class=\'fa fa-circle mr-2 text-yellow\'></i> Inbound">Inbound</option>';

                $status .= '<option value="received" ' . ($row->status == 'received' ? 'selected' : '') .
                    ' data-content="<i class=\'fa fa-circle mr-2 text-light-green\'></i> Received">Received</option>';

                $status .= '</select>';
                $status .= '</div>';

                return $status;
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'delivery_number', 'vendor_name', 'status']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query($model = null)
    {
        $this->normalizeLegacyRequestColumns();
        $request = $this->request();
        $headerModelClass = GrnRuntime::headerModelClass();
        $headerTable = GrnRuntime::headerTable();
        $numberColumn = GrnRuntime::numberColumn();
        $dateColumn = GrnRuntime::dateColumn();
        $model = new $headerModelClass;

        // Join tables to allow sorting by vendor name
        $model = $model->select($headerTable . '.*', 'purchase_vendors.primary_name as vendor_name', 'purchase_vendors.id as vendor_id')
            ->selectRaw($headerTable . '.' . $numberColumn . ' as delivery_number')
            ->selectRaw($headerTable . '.' . $dateColumn . ' as delivery_date')
            ->join('purchase_orders', 'purchase_orders.id', '=', $headerTable . '.purchase_order_id')
            ->join('purchase_vendors', 'purchase_vendors.id', '=', 'purchase_orders.vendor_id');

        $company = company();

        if ($company instanceof Company) {
            $model->where($headerTable . '.company_id', $company->id);
        }

        if ($request->searchText != '') {
            $model = $model->where(function ($query) {
                $query->where(GrnRuntime::headerTable() . '.' . GrnRuntime::numberColumn(), 'like', '%' . request('searchText') . '%')
                    ->orWhere('purchase_vendors.primary_name', 'like', '%' . request('searchText') . '%');
            });
        }

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = Carbon::createFromFormat(company()->date_format, $request->startDate)->toDateString();
            $model = $model->where(DB::raw('DATE(' . $headerTable . '.' . $dateColumn . ')'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = Carbon::createFromFormat(company()->date_format, $request->endDate)->toDateString();
            $model = $model->where(DB::raw('DATE(' . $headerTable . '.' . $dateColumn . ')'), '<=', $endDate);
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->setBuilder('delivery-order-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["delivery-order-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $(".select-picker").selectpicker();
                }',
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $headerTable = GrnRuntime::headerTable();
        $numberColumn = GrnRuntime::numberColumn();
        $dateColumn = GrnRuntime::dateColumn();

        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => $headerTable . '.id', 'visible' => false, 'title' => 'Id'],
            __('app.orderNumber') => ['data' => 'delivery_number', 'name' => 'delivery_number', 'title' => 'Order Number'],
            __('app.date') => ['data' => 'delivery_date', 'name' => 'delivery_date', 'title' => 'Date'],
            __('purchase::app.menu.vendor') => ['data' => 'vendor_name', 'name' => 'purchase_vendors.primary_name', 'title' => 'Vendor'],
            __('app.status') => ['data' => 'status', 'name' => $headerTable . '.status', 'title' => 'Delivery Status'],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }
}
