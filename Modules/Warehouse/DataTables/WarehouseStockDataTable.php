<?php

namespace Modules\Warehouse\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Entities\WarehouseProductStock;
use Modules\Warehouse\Services\WarehouseFlowPolicyService;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Button;

class WarehouseStockDataTable extends BaseDataTable
{
    public function __construct(
        private readonly WarehouseFlowPolicyService $flowPolicyService
    ) {
        parent::__construct();
    }

    /**
     * @param  Builder<WarehouseProductStock>  $query
     */
    public function dataTable($query): DataTableAbstract
    {
        $datatables = datatables()->eloquent($query);

        $datatables->addColumn('product_label', function (WarehouseProductStock $row): string {
            $productName = $row->product?->name ?? __('warehouse::app.deletedProduct');
            $productSku = $row->product?->sku ?? '--';

            return '<span class="font-weight-semibold">'.e($productName).'</span><br><small class="text-lightest">'.e($productSku).'</small>';
        });

        $datatables->addColumn('warehouse_label', function (WarehouseProductStock $row): string {
            $warehouseName = $row->warehouse?->name ?? __('warehouse::app.deletedWarehouse');
            $code = $row->warehouse?->code ? ' ('.$row->warehouse->code.')' : '';
            $label = e($warehouseName.$code);

            if ($row->warehouse?->is_default) {
                $label .= '<span class="badge badge-light ml-1">'.e(__('warehouse::app.defaultBadge')).'</span>';
            }

            return $label;
        });

        $datatables->addColumn('warehouse_type_label', function (WarehouseProductStock $row): string {
            $type = (string) ($row->warehouse?->warehouse_type ?? 'normal');
            $typeLabel = trim(view('warehouse::partials.warehouse-type-label', ['type' => $type])->render());

            return '<span class="badge badge-light">'.$typeLabel.'</span>';
        });

        $datatables->editColumn('quantity', fn (WarehouseProductStock $row): string => $this->formatQuantityBadge($row->quantity));
        $datatables->addColumn('reserved_quantity_display', fn (WarehouseProductStock $row): string => $this->formatQuantity((float) ($row->reserved_quantity ?? 0)));
        $datatables->addColumn('available_quantity_display', fn (WarehouseProductStock $row): string => $this->formatQuantity($this->availableQuantity($row)));
        $datatables->addColumn('sellable_quantity_display', fn (WarehouseProductStock $row): string => $this->formatQuantityBadge($this->sellableQuantity($row)));

        $datatables->editColumn('updated_at', function (WarehouseProductStock $row): string {
            return $row->updated_at
                ? $row->updated_at->timezone(company()->timezone)->format(company()->date_format.' H:i')
                : '—';
        });

        $datatables->addIndexColumn();
        $datatables->smart(false);
        $datatables->setRowId(fn (WarehouseProductStock $row): string => 'row-'.$row->id);
        $datatables->rawColumns([
            'available_quantity_display',
            'product_label',
            'quantity',
            'reserved_quantity_display',
            'sellable_quantity_display',
            'warehouse_label',
            'warehouse_type_label',
        ]);

        return $datatables;
    }

    /**
     * @return Builder<WarehouseProductStock>
     */
    public function query(WarehouseProductStock $model): Builder
    {
        $request = $this->request();
        $companyId = company()?->id ?? user()?->company_id;

        $batchAgg = WarehouseProductBatch::query()
            ->selectRaw('warehouse_id, product_id, SUM(reserved_quantity) as reserved_qty')
            ->groupBy('warehouse_id', 'product_id');

        $model = $model->newQuery()
            ->with([
                'product:id,name,sku',
                'warehouse:id,name,code,is_default,warehouse_type,company_id',
            ])
            ->select('warehouse_product_stock.*')
            ->leftJoinSub($batchAgg, 'batch_reserved', function ($join) {
                $join->on('batch_reserved.warehouse_id', '=', 'warehouse_product_stock.warehouse_id')
                    ->on('batch_reserved.product_id', '=', 'warehouse_product_stock.product_id');
            })
            ->addSelect(DB::raw('COALESCE(batch_reserved.reserved_qty, 0) as reserved_quantity'))
            ->whereHas('product')
            ->whereHas('warehouse', function (Builder $query) use ($companyId): void {
                if ($companyId) {
                    $query->where('company_id', (int) $companyId);
                }
            });

        if (($request->warehouse_id ?? '') !== '') {
            $model->where('warehouse_product_stock.warehouse_id', (int) $request->warehouse_id);
        }

        if (($request->searchText ?? '') !== '') {
            $term = '%'.$request->searchText.'%';
            $model->whereHas('product', function (Builder $query) use ($term): void {
                $query->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term);
            });
        }

        if (! $request->has('order')) {
            $model->orderByDesc('warehouse_product_stock.updated_at')
                ->orderByDesc('warehouse_product_stock.id');
        }

        return $model;
    }

    public function html()
    {
        $dataTable = $this->setBuilder('warehouse-stock-table', 9)
            ->parameters([
                'order' => [[9, 'desc'], [1, 'desc']],
                'initComplete' => 'function () {
                    try {
                        if (window.LaravelDataTables && window.LaravelDataTables["warehouse-stock-table"] && window.LaravelDataTables["warehouse-stock-table"].buttons) {
                            window.LaravelDataTables["warehouse-stock-table"].buttons().container().appendTo("#table-actions");
                        }
                    } catch (error) {
                        console.error("Warehouse stock DataTable init error:", error);
                    }
                }',
            ]);

        $buttons = [
            Button::make([
                'extend' => 'colvis',
                'text' => '<i class="fa fa-columns"></i> '.trans('app.columns'),
                'columns' => ':not(:first)',
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
                'name' => 'warehouse_product_stock.id',
                'title' => __('app.id'),
                'visible' => showId(),
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
            __('warehouse::app.warehouseType') => [
                'data' => 'warehouse_type_label',
                'name' => 'warehouse.warehouse_type',
                'title' => __('warehouse::app.warehouseType'),
                'orderable' => false,
            ],
            __('warehouse::app.quantity') => [
                'data' => 'quantity',
                'name' => 'warehouse_product_stock.quantity',
                'title' => __('warehouse::app.quantity'),
            ],
            __('warehouse::app.reservedQuantity') => [
                'data' => 'reserved_quantity_display',
                'name' => 'reserved_quantity',
                'title' => __('warehouse::app.reservedQuantity'),
            ],
            __('warehouse::app.availableQuantity') => [
                'data' => 'available_quantity_display',
                'name' => 'available_quantity_display',
                'title' => __('warehouse::app.availableQuantity'),
                'orderable' => false,
            ],
            __('warehouse::app.sellableQuantity') => [
                'data' => 'sellable_quantity_display',
                'name' => 'sellable_quantity_display',
                'title' => __('warehouse::app.sellableQuantity'),
                'orderable' => false,
            ],
            __('app.updatedAt') => [
                'data' => 'updated_at',
                'name' => 'warehouse_product_stock.updated_at',
                'title' => __('app.updatedAt'),
            ],
        ];
    }

    private function availableQuantity(WarehouseProductStock $row): float
    {
        $onHand = (float) $row->quantity;
        $reserved = (float) ($row->reserved_quantity ?? 0);

        return max(0.0, $onHand - $reserved);
    }

    private function sellableQuantity(WarehouseProductStock $row): float
    {
        $warehouseType = (string) ($row->warehouse?->warehouse_type ?? 'normal');

        if (! $this->flowPolicyService->isSellableWarehouseType($warehouseType)) {
            return 0.0;
        }

        return $this->availableQuantity($row);
    }

    private function formatQuantity(float|int|null $value): string
    {
        $formatted = rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    private function formatQuantityBadge(float|int|null $value): string
    {
        $number = (float) $value;
        $class = $number > 0 ? 'text-success' : 'text-danger';

        return '<span class="font-weight-semibold '.$class.'">'.$this->formatQuantity($number).'</span>';
    }
}
