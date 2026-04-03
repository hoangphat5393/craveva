<?php

namespace App\DataTables;

use App\Helper\Common;
use App\Models\SalesHistoryLine;
use Carbon\Carbon;
use Yajra\DataTables\Html\Button;

class SalesHistoryDataTable extends BaseDataTable
{
    /**
     * Browser state may still send old column names from previous versions.
     * Normalize them so server-side ordering/search always targets valid aliases.
     */
    private function normalizeLegacyRequestColumns(): void
    {
        $request = $this->request();
        $columns = $request->input('columns');

        if (! is_array($columns)) {
            return;
        }

        $legacyToAlias = [
            'sales_history_lines.shipment_date' => 'shipment_date',
            'sales_history_lines.amount' => 'amount',
            'sales_history_lines.quantity' => 'quantity',
            'sales_history_lines.is_return' => 'is_return',
            'products.name' => 'product_name',
            'product.name' => 'product_name',
            'products.sku' => 'product_sku',
            'product.sku' => 'product_sku',
            'users.name' => 'client_name',
            'user.name' => 'client_name',
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

    public function dataTable($query)
    {
        $datatable = datatables()
            ->eloquent($query)
            ->editColumn('client_name', fn ($row) => $row->client_name ?: '--')
            ->editColumn('product_sku', fn ($row) => $row->product_sku ?: '--')
            ->editColumn('product_name', fn ($row) => $row->product_name ?: '--')
            ->addColumn('shipment_date_formatted', function ($row) {
                if (! $row->shipment_date) {
                    return '--';
                }

                try {
                    return Carbon::parse($row->shipment_date)->format($this->company->date_format);
                } catch (\Throwable $e) {
                    return '--';
                }
            })
            ->addColumn('amount_formatted', function ($row) {
                if ($row->amount === null) {
                    return '--';
                }

                try {
                    return currency_format($row->amount, $row->currency_id);
                } catch (\Throwable $e) {
                    return (string) $row->amount;
                }
            })
            ->addColumn('return_label', fn ($row) => $row->is_return ? __('app.yes') : __('app.no'))
            ->addIndexColumn()
            ->setRowId(fn ($row) => 'row-'.$row->id)
            ->rawColumns([]);

        // Server-side ordering must use real columns (joins below), not relation dot-notation.
        $datatable->orderColumn('shipment_date', 'sales_history_lines.shipment_date $1');
        $datatable->orderColumn('client_name', 'users.name $1');
        $datatable->orderColumn('product_name', 'products.name $1');
        $datatable->orderColumn('product_sku', 'products.sku $1');
        $datatable->orderColumn('amount', 'sales_history_lines.amount $1');
        $datatable->orderColumn('is_return', 'sales_history_lines.is_return $1');
        $datatable->orderColumn('quantity', 'sales_history_lines.quantity $1');
        $datatable->orderColumn('id', 'sales_history_lines.id $1');

        return $datatable;
    }

    public function query(SalesHistoryLine $model)
    {
        $this->normalizeLegacyRequestColumns();
        $request = $this->request();

        $model = $model->newQuery()
            ->leftJoin('users', 'users.id', '=', 'sales_history_lines.client_id')
            ->leftJoin('products', 'products.id', '=', 'sales_history_lines.product_id')
            ->select([
                'sales_history_lines.*',
                'users.name as client_name',
                'products.sku as product_sku',
                'products.name as product_name',
            ]);

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            if (! is_null($startDate)) {
                $model->whereDate('sales_history_lines.shipment_date', '>=', $startDate);
            }
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            if (! is_null($endDate)) {
                $model->whereDate('sales_history_lines.shipment_date', '<=', $endDate);
            }
        }

        if ($request->clientID != 'all' && ! is_null($request->clientID)) {
            $model->where('sales_history_lines.client_id', $request->clientID);
        }

        if ($request->searchText != '') {
            $safeTerm = Common::safeString(request('searchText'));
            // Dùng cột từ join (users, products) — tránh whereHas + join gây SQL lỗi trên một số DB.
            $model->where(function ($query) use ($safeTerm) {
                $query->where('products.name', 'like', '%'.$safeTerm.'%')
                    ->orWhere('products.sku', 'like', '%'.$safeTerm.'%')
                    ->orWhere('users.name', 'like', '%'.$safeTerm.'%');
            });
        }

        return $model->orderBy('sales_history_lines.shipment_date', 'desc');
    }

    public function html()
    {
        $dataTable = $this->setBuilder('sales-history-dt', 1)
            ->parameters([
                // Tránh state cũ (tên cột product.name) sau khi đổi schema → Ajax lỗi.
                'stateSave' => false,
                'pageLength' => 50,
                'initComplete' => 'function () {
                    window.LaravelDataTables["sales-history-dt"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                $(".select-picker").selectpicker();
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> '.trans('app.exportExcel')]));
        }

        return $dataTable;
    }

    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'searchable' => false, 'width' => '72px'],
            __('app.date') => ['data' => 'shipment_date_formatted', 'name' => 'shipment_date', 'title' => __('app.salesHistoryShipmentDate'), 'searchable' => false],
            __('app.client') => ['data' => 'client_name', 'name' => 'client_name', 'title' => __('app.client'), 'searchable' => false],
            __('app.product') => ['data' => 'product_name', 'name' => 'product_name', 'title' => __('app.product'), 'searchable' => false],
            'SKU' => ['data' => 'product_sku', 'name' => 'product_sku', 'title' => 'SKU', 'searchable' => false],
            __('app.salesHistoryNetQty') => ['data' => 'quantity', 'name' => 'quantity', 'title' => __('app.salesHistoryNetQty')],
            __('modules.invoices.total') => ['data' => 'amount_formatted', 'name' => 'amount', 'title' => __('modules.invoices.total'), 'searchable' => false],
            __('app.salesHistoryReturn') => ['data' => 'return_label', 'name' => 'is_return', 'title' => __('app.salesHistoryReturn'), 'searchable' => false],
        ];
    }
}
