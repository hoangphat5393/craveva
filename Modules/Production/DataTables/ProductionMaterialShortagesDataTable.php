<?php

declare(strict_types=1);

namespace Modules\Production\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Support\Collection;
use Modules\Production\Services\ProductionMaterialSummaryService;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ProductionMaterialShortagesDataTable extends BaseDataTable
{
    public function __construct(
        private readonly ProductionMaterialSummaryService $materialSummaryService,
    ) {
        parent::__construct();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $query
     */
    public function dataTable($query): DataTableAbstract
    {
        $datatables = datatables()->collection($query);

        $datatables->editColumn('total_required', fn (array $row): string => $this->formatQuantity((float) $row['total_required']));
        $datatables->editColumn('available_stock', fn (array $row): string => $this->formatQuantity((float) $row['available_stock']));
        $datatables->editColumn('shortage_to_procure', fn (array $row): string => $this->formatQuantity((float) $row['shortage_to_procure']));
        $datatables->editColumn('affected_orders_count', fn (array $row): string => (string) ((int) $row['affected_orders_count']));
        $datatables->addColumn('action', function (array $row): string {
            $url = route('production.material-shortages.orders', [
                'material_id' => (int) $row['component_product_id'],
                'warehouse_id' => (int) $row['rm_warehouse_id'],
                'status_scope' => $this->request()->input('status_scope', 'active'),
            ]);

            return '<a href="'.$url.'" class="text-dark-grey">'.e(__('production::app.viewOrders')).'</a>';
        });

        $datatables->smart(false);
        $datatables->rawColumns(['action']);

        return $datatables;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function query(): Collection
    {
        $request = $this->request();

        return collect($this->materialSummaryService->summaries((int) company()->id, [
            'status_scope' => $request->input('status_scope'),
            'warehouse_id' => $request->input('warehouse_id'),
            'material_id' => $request->input('material_id'),
            'only_shortage' => $request->has('only_shortage')
                ? $request->input('only_shortage')
                : true,
        ]));
    }

    public function html()
    {
        $dataTable = $this->setBuilder('production-material-shortages-table', 4)
            ->parameters([
                'order' => [[4, 'desc']],
                'initComplete' => 'function () {
                    try {
                        if (window.LaravelDataTables && window.LaravelDataTables["production-material-shortages-table"] && window.LaravelDataTables["production-material-shortages-table"].buttons) {
                            window.LaravelDataTables["production-material-shortages-table"].buttons().container().appendTo("#table-actions");
                        }
                    } catch (error) {
                        console.error("Production material shortages DataTable init error:", error);
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
            __('production::app.rawMaterialProduct') => [
                'data' => 'component_name',
                'name' => 'component_name',
                'title' => __('production::app.rawMaterialProduct'),
            ],
            __('production::app.rawMaterialWarehouse') => [
                'data' => 'rm_warehouse_name',
                'name' => 'rm_warehouse_name',
                'title' => __('production::app.rawMaterialWarehouse'),
            ],
            __('production::app.materialTotalRequired') => [
                'data' => 'total_required',
                'name' => 'total_required',
                'title' => __('production::app.materialTotalRequired'),
            ],
            __('production::app.materialAvailableStock') => [
                'data' => 'available_stock',
                'name' => 'available_stock',
                'title' => __('production::app.materialAvailableStock'),
            ],
            __('production::app.materialShortageToProcure') => [
                'data' => 'shortage_to_procure',
                'name' => 'shortage_to_procure',
                'title' => __('production::app.materialShortageToProcure'),
            ],
            __('production::app.baseUnit') => [
                'data' => 'unit_label_base',
                'name' => 'unit_label_base',
                'title' => __('production::app.baseUnit'),
            ],
            __('production::app.affectedOrders') => [
                'data' => 'affected_orders_count',
                'name' => 'affected_orders_count',
                'title' => __('production::app.affectedOrders'),
            ],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }

    protected function formatQuantity(float $value): string
    {
        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.') ?: '0';
    }
}
