<?php

namespace Modules\Warehouse\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class WarehouseProductBatchesDataTable extends BaseDataTable
{
    /**
     * @param  Builder<WarehouseProductBatch>  $query
     */
    public function dataTable($query): DataTableAbstract
    {
        $datatables = datatables()->eloquent($query);

        $datatables->editColumn('batch_label', function (WarehouseProductBatch $row): string {
            $label = '<span class="font-weight-semibold">#'.$row->id.'</span>';

            if ($row->batch_number) {
                $label .= '<br><small class="text-muted">'.e($row->batch_number).'</small>';
            }

            return $label;
        });

        $datatables->editColumn('product_label', function (WarehouseProductBatch $row): string {
            $productName = $row->product?->name ?? '—';
            $productSku = $row->product?->sku ?? '—';

            return e($productName).'<br><small class="text-lightest">'.e($productSku).'</small>';
        });

        $datatables->editColumn('warehouse_label', function (WarehouseProductBatch $row): string {
            if (! $row->warehouse) {
                return '—';
            }

            $code = $row->warehouse->code ? ' ('.$row->warehouse->code.')' : '';

            return e($row->warehouse->name.$code);
        });

        $datatables->editColumn('quantity', fn (WarehouseProductBatch $row): string => $this->formatQuantity($row->quantity, true));
        $datatables->editColumn('reserved_quantity', fn (WarehouseProductBatch $row): string => $this->formatQuantity($row->reserved_quantity ?? 0));

        $datatables->addColumn('action', function (WarehouseProductBatch $row): string {
            return '<a href="'.route('warehouse.product-batches.show', $row->id).'" class="btn btn-sm btn-secondary rounded f-13">
                        <i class="fa fa-eye mr-1"></i>'.e(__('app.view')).'
                    </a>';
        });

        $datatables->addIndexColumn();
        $datatables->smart(false);
        $datatables->setRowId(fn (WarehouseProductBatch $row): string => 'row-'.$row->id);
        $datatables->rawColumns(['action', 'batch_label', 'product_label', 'quantity', 'reserved_quantity', 'warehouse_label']);

        return $datatables;
    }

    /**
     * @return Builder<WarehouseProductBatch>
     */
    public function query(WarehouseProductBatch $model): Builder
    {
        $request = $this->request();
        $companyId = company()?->id ?? user()?->company_id;

        $model = $model->newQuery()
            ->with([
                'warehouse:id,name,code,company_id',
                'product:id,name,sku',
            ])
            ->select('warehouse_product_batches.*');

        if ($companyId) {
            $model->whereHas('warehouse', function (Builder $query) use ($companyId): void {
                $query->where('company_id', (int) $companyId);
            });
        }

        if (($request->warehouse_id ?? '') !== '') {
            $model->where('warehouse_id', (int) $request->warehouse_id);
        }

        if (($request->searchText ?? '') !== '') {
            $search = (string) $request->searchText;
            $term = '%'.$search.'%';

            $model->where(function (Builder $query) use ($search, $term): void {
                $query->whereHas('product', function (Builder $productQuery) use ($term): void {
                    $productQuery->where('name', 'like', $term)
                        ->orWhere('sku', 'like', $term);
                })
                    ->orWhere('batch_number', 'like', $term);

                if (is_numeric($search)) {
                    $query->orWhere('warehouse_product_batches.id', (int) $search);
                }
            });
        }

        if (! $request->has('order')) {
            $model->orderByDesc('warehouse_product_batches.id');
        }

        return $model;
    }

    public function html()
    {
        $dataTable = $this->setBuilder('warehouse-product-batches-table', 1)
            ->parameters([
                'order' => [[1, 'desc']],
                'initComplete' => 'function () {
                    try {
                        if (window.LaravelDataTables && window.LaravelDataTables["warehouse-product-batches-table"] && window.LaravelDataTables["warehouse-product-batches-table"].buttons) {
                            window.LaravelDataTables["warehouse-product-batches-table"].buttons().container().appendTo("#table-actions");
                        }
                    } catch (error) {
                        console.error("Warehouse product batches DataTable init error:", error);
                    }
                }',
            ]);

        $buttons = [
            Button::make([
                'extend' => 'colvis',
                'text' => '<i class="fa fa-columns"></i> '.trans('app.columns'),
                'columns' => ':not(:last)',
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
            '#' => [
                'data' => 'DT_RowIndex',
                'orderable' => false,
                'searchable' => false,
                'visible' => ! showId(),
                'title' => '#',
            ],
            __('app.id') => [
                'data' => 'id',
                'name' => 'warehouse_product_batches.id',
                'title' => __('app.id'),
                'visible' => showId(),
            ],
            __('warehouse::app.batch') => [
                'data' => 'batch_label',
                'name' => 'warehouse_product_batches.batch_number',
                'title' => __('warehouse::app.batch'),
            ],
            __('warehouse::app.product') => [
                'data' => 'product_label',
                'name' => 'product.name',
                'title' => __('warehouse::app.product'),
                'orderable' => false,
            ],
            __('warehouse::app.warehouse') => [
                'data' => 'warehouse_label',
                'name' => 'warehouse.name',
                'title' => __('warehouse::app.warehouse'),
                'orderable' => false,
            ],
            __('warehouse::app.quantity') => [
                'data' => 'quantity',
                'name' => 'warehouse_product_batches.quantity',
                'title' => __('warehouse::app.quantity'),
            ],
            __('warehouse::app.reservedQuantity') => [
                'data' => 'reserved_quantity',
                'name' => 'warehouse_product_batches.reserved_quantity',
                'title' => __('warehouse::app.reservedQuantity'),
            ],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false),
        ];
    }

    private function formatQuantity(float|int|null $value, bool $highlight = false): string
    {
        $formatted = rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');

        if (! $highlight) {
            return $formatted === '' ? '0' : $formatted;
        }

        return '<span class="font-weight-semibold">'.($formatted === '' ? '0' : $formatted).'</span>';
    }
}
