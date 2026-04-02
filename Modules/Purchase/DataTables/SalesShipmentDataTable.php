<?php

namespace Modules\Purchase\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Support\FlowPermission;
use Modules\Purchase\Support\SalesDoRuntime;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class SalesShipmentDataTable extends BaseDataTable
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
            'sales_shipments.shipment_number' => 'shipment_number',
            'sales_shipments.shipment_date' => 'shipment_date',
            'sales_dos.do_number' => 'shipment_number',
            'sales_dos.do_date' => 'shipment_date',
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

    private function salesDoRouteName(string $action): string
    {
        $prefix = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'sales-shipments' : 'sales-do';

        return $prefix . '.' . $action;
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($row) {
                $showRoute = $this->salesDoRouteName('show');
                $editRoute = $this->salesDoRouteName('edit');
                $canUpdate = FlowPermission::allowsAlias('sales_do.update');
                $canShip = FlowPermission::allowsAlias('sales_do.ship');
                $canCancel = FlowPermission::allowsAlias('sales_do.cancel');

                $action = '<div class="task_view"><div class="dropdown">';
                $action .= '<a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                    id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="icon-options-vertical icons"></i></a>';
                $action .= '<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';
                $action .= '<a href="' . route($showRoute, $row->id) . '" class="dropdown-item f-14 text-dark"><i class="fa fa-eye mr-2"></i>' . trans('app.view') . '</a>';

                if ($canUpdate && ! in_array($row->status, ['shipped', 'delivered', 'cancelled'], true)) {
                    $action .= '<a class="dropdown-item f-14 text-dark openRightModal" href="' . route($editRoute, $row->id) . '"><i class="fa fa-edit mr-2"></i>' . trans('app.edit') . '</a>';
                }

                if ($canShip && $row->status === 'draft') {
                    $action .= '<a class="dropdown-item f-14 text-dark sales-shipment-confirm" data-id="' . $row->id . '" href="javascript:;"><i class="fa fa-check mr-2"></i>' . trans('app.confirm') . '</a>';
                }
                if ($canShip && in_array($row->status, ['draft', 'confirmed'], true)) {
                    $action .= '<a class="dropdown-item f-14 text-dark sales-shipment-ship" data-id="' . $row->id . '" href="javascript:;"><i class="fa fa-truck mr-2"></i>' . trans('purchase::app.ship') . '</a>';
                }
                if ($canShip && $row->status === 'shipped') {
                    $action .= '<a class="dropdown-item f-14 text-dark sales-shipment-deliver" data-id="' . $row->id . '" href="javascript:;"><i class="fa fa-box mr-2"></i>' . trans('purchase::modules.salesShipment.delivered') . '</a>';
                }
                if ($canCancel && in_array($row->status, ['shipped', 'delivered'], true)) {
                    $action .= '<a class="dropdown-item f-14 text-dark sales-shipment-reverse" data-id="' . $row->id . '" href="javascript:;"><i class="fa fa-undo mr-2"></i>' . trans('purchase::modules.salesShipment.reverse') . '</a>';
                }
                if ($canCancel && $row->status !== 'cancelled') {
                    $action .= '<a class="dropdown-item f-14 text-dark sales-shipment-cancel" data-id="' . $row->id . '" href="javascript:;"><i class="fa fa-ban mr-2"></i>' . trans('app.cancel') . '</a>';
                }
                $action .= '</div></div></div>';

                return $action;
            })
            ->editColumn('shipment_number', fn($row) => '<a href="' . route($this->salesDoRouteName('show'), $row->id) . '">' . $row->shipment_number . '</a>')
            ->editColumn('shipment_date', fn($row) => Carbon::parse($row->shipment_date)->translatedFormat(company()->date_format))
            ->editColumn('status', function ($row) {
                $class = match ($row->status) {
                    'draft' => 'text-dark border-dark',
                    'confirmed' => 'text-info border-info',
                    'shipped' => 'text-primary border-primary',
                    'delivered' => 'text-success border-success',
                    'cancelled' => 'text-danger border-danger',
                    default => 'text-dark border-dark',
                };

                return '<span class="unpaid rounded f-12 ' . $class . '">' . trans('purchase::modules.salesShipment.' . $row->status) . '</span>';
            })
            ->addIndexColumn()
            ->rawColumns(['shipment_number', 'status', 'action']);
    }

    public function query($model = null)
    {
        $this->normalizeLegacyRequestColumns();
        $headerModelClass = SalesDoRuntime::headerModelClass();
        $headerTable = SalesDoRuntime::headerTable();
        $numberColumn = SalesDoRuntime::numberColumn();
        $dateColumn = SalesDoRuntime::dateColumn();

        $model = new $headerModelClass;
        $request = $this->request();
        $query = $model->newQuery()
            ->select($headerTable . '.*', 'orders.order_number')
            ->selectRaw($headerTable . '.' . $numberColumn . ' as shipment_number')
            ->selectRaw($headerTable . '.' . $dateColumn . ' as shipment_date')
            ->leftJoin('orders', 'orders.id', '=', $headerTable . '.order_id');

        $company = company();
        if ($company instanceof Company) {
            $query->where($headerTable . '.company_id', $company->id);
        }

        if ($request->searchText != '') {
            $query->where(function ($q) use ($request) {
                $q->where(SalesDoRuntime::headerTable() . '.' . SalesDoRuntime::numberColumn(), 'like', '%' . $request->searchText . '%')
                    ->orWhere('orders.order_number', 'like', '%' . $request->searchText . '%');
            });
        }

        if ($request->startDate) {
            $query->where(DB::raw('DATE(' . $headerTable . '.' . $dateColumn . ')'), '>=', Carbon::createFromFormat(company()->date_format, $request->startDate)->toDateString());
        }
        if ($request->endDate) {
            $query->where(DB::raw('DATE(' . $headerTable . '.' . $dateColumn . ')'), '<=', Carbon::createFromFormat(company()->date_format, $request->endDate)->toDateString());
        }

        return $query;
    }

    public function html()
    {
        return $this->setBuilder('sales-shipment-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["sales-shipment-table"].buttons().container().appendTo("#table-actions")
                }',
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    protected function getColumns()
    {
        $salesDoColumnLabel = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy'
            ? __('purchase::app.menu.salesShipments')
            : __('purchase::app.menu.saleDeliveryOrder');
        $headerTable = SalesDoRuntime::headerTable();
        $numberColumn = SalesDoRuntime::numberColumn();
        $dateColumn = SalesDoRuntime::dateColumn();

        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('app.id') => ['data' => 'id', 'name' => $headerTable . '.id', 'visible' => false],
            $salesDoColumnLabel => ['data' => 'shipment_number', 'name' => 'shipment_number'],
            __('app.orderNumber') => ['data' => 'order_number', 'name' => 'orders.order_number'],
            __('app.date') => ['data' => 'shipment_date', 'name' => 'shipment_date'],
            __('app.status') => ['data' => 'status', 'name' => $headerTable . '.status'],
            Column::computed('action', __('app.action'))->exportable(false)->printable(false)->orderable(false)->searchable(false)->addClass('text-right pr-20'),
        ];
    }
}
