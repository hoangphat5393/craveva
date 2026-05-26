<?php

declare(strict_types=1);

namespace Modules\Production\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Services\ProductionOrderMaterialRequirementsSummary;
use Modules\Production\Support\ProductionOrderStatusBadge;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ProductionOrdersDataTable extends BaseDataTable
{
    private string $viewProductionOrderPermission;

    private string $editProductionOrderPermission;

    public function __construct(
        private readonly ProductionOrderMaterialRequirementsSummary $materialRequirementsSummary,
    ) {
        parent::__construct();

        $this->viewProductionOrderPermission = (string) user()->permission('view_production_orders');
        $this->editProductionOrderPermission = (string) user()->permission('edit_production_orders');
    }

    /**
     * @param  Builder<ProductionOrder>  $query
     */
    public function dataTable($query): DataTableAbstract
    {
        $datatables = datatables()->eloquent($query);

        $datatables->editColumn('output_product_name', function (ProductionOrder $row): string {
            return e((string) ($row->output_product_name ?: '—'));
        });

        $datatables->editColumn('fg_unit_type', function (ProductionOrder $row): string {
            return e((string) ($row->fg_unit_type ?: '—'));
        });

        $datatables->addColumn('bom_label', function (ProductionOrder $row): string {
            if ($row->bom_row_id === null) {
                return '—';
            }

            $parts = array_values(array_filter([
                trim((string) ($row->bom_code ?? '')),
                trim((string) ($row->bom_version ?? '')),
            ], static fn(?string $value): bool => $value !== null && $value !== ''));

            $label = $parts !== []
                ? implode(' · ', $parts)
                : __('production::app.bomSelectIdLabel', ['id' => $row->bom_row_id]);

            $html = '<a href="' . route('production.boms.show', [$row->bom_row_id]) . '" class="text-dark-grey">' . e($label) . '</a>';

            if ($row->bom_snapshot_at !== null) {
                $html .= ' <span class="badge badge-secondary f-10 ml-1" title="' . e(__('production::app.bomSnapshotTitle')) . '">' . e(__('production::app.bomSnapshotFrozenBadge')) . '</span>';
            }

            return $html;
        });

        $datatables->editColumn('planned_quantity', function (ProductionOrder $row): string {
            $value = (float) $row->planned_quantity;

            return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.') ?: '0';
        });

        $datatables->editColumn('status', fn(ProductionOrder $row): string => $this->statusColumnHtml($row));
        $datatables->addColumn('material_availability', fn(ProductionOrder $row): string => $this->materialAvailabilityColumnHtml($row));

        $datatables->addColumn('action', function (ProductionOrder $row): string {
            $canView = in_array($this->viewProductionOrderPermission, ['all', 'added', 'owned', 'both'], true);
            $canEdit = in_array($this->editProductionOrderPermission, ['all', 'added', 'owned', 'both'], true)
                && (string) $row->status === ProductionOrder::STATUS_DRAFT;

            if (! $canView && ! $canEdit) {
                return '<span class="text-lightest">—</span>';
            }

            $action = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="production-order-actions-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="production-order-actions-' . $row->id . '" tabindex="0">';

            if ($canView) {
                $action .= '<a class="dropdown-item" href="' . route('production.orders.show', [$row->id]) . '"><i class="fa fa-eye mr-2 text-dark-grey"></i>' . e(__('app.view')) . '</a>';
            }

            if ($canEdit) {
                $action .= '<a class="dropdown-item openRightModal" href="' . route('production.orders.edit', [$row->id]) . '?redirect_url=' . urlencode(route('production.orders.index')) . '"><i class="fa fa-edit mr-2 text-dark-grey"></i>' . e(__('app.edit')) . '</a>';
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });

        $datatables->smart(false);
        $datatables->setRowId(fn(ProductionOrder $row): string => 'row-' . $row->id);
        $datatables->rawColumns(['action', 'bom_label', 'status', 'material_availability']);

        return $datatables;
    }

    /**
     * @return Builder<ProductionOrder>
     */
    public function query(ProductionOrder $model): Builder
    {
        $request = $this->request();

        $query = $model->newQuery()
            ->leftJoin('products as output_products', 'output_products.id', '=', 'production_orders.output_product_id')
            ->leftJoin('unit_types as output_unit_types', 'output_unit_types.id', '=', 'output_products.unit_id')
            ->leftJoin('production_boms as boms', 'boms.id', '=', 'production_orders.production_bom_id')
            ->select(
                'production_orders.*',
                'output_products.name as output_product_name',
                'output_unit_types.unit_type as fg_unit_type',
                'boms.id as bom_row_id',
                'boms.code as bom_code',
                'boms.version as bom_version',
            )
            ->where('production_orders.company_id', (int) company()->id);

        if (! is_null($request->status) && $request->status !== '' && $request->status !== 'all') {
            $query->where('production_orders.status', (string) $request->status);
        }

        if (($request->searchText ?? '') !== '') {
            $term = '%' . $request->searchText . '%';

            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('production_orders.id', 'like', $term)
                    ->orWhere('output_products.name', 'like', $term)
                    ->orWhere('production_orders.planned_quantity', 'like', $term)
                    ->orWhere('production_orders.status', 'like', $term)
                    ->orWhere('boms.code', 'like', $term)
                    ->orWhere('boms.version', 'like', $term);
            });
        }

        if (! $request->has('order')) {
            $query->orderByDesc('production_orders.id');
        }

        return $query;
    }

    public function html()
    {
        $dataTable = $this->setBuilder('production-orders-table', 0)
            ->parameters([
                'order' => [[0, 'desc']],
                'initComplete' => 'function () {
                    try {
                        if (window.LaravelDataTables && window.LaravelDataTables["production-orders-table"] && window.LaravelDataTables["production-orders-table"].buttons) {
                            window.LaravelDataTables["production-orders-table"].buttons().container().appendTo("#table-actions");
                        }
                    } catch (error) {
                        console.error("Production orders DataTable init error:", error);
                    }
                }',
                'fnDrawCallback' => 'function () {
                    try {
                        $("body").tooltip({
                            selector: \'[data-toggle="tooltip"]\'
                        });
                    } catch (error) {
                        console.error("Production orders DataTable draw error:", error);
                    }
                }',
            ]);

        $buttons = [
            Button::make([
                'extend' => 'colvis',
                'text' => '<i class="fa fa-columns"></i> ' . trans('app.columns'),
                'columns' => ':not(:last)',
            ]),
        ];

        if (canDataTableExport()) {
            array_unshift($buttons, Button::make([
                'extend' => 'excel',
                'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel'),
            ]));
        }

        $dataTable->buttons($buttons);

        return $dataTable;
    }

    protected function getColumns(): array
    {
        return [
            __('app.id') => [
                'data' => 'id',
                'name' => 'production_orders.id',
                'title' => __('app.id'),
            ],
            __('production::app.manufacturedProduct') => [
                'data' => 'output_product_name',
                'name' => 'output_products.name',
                'title' => __('production::app.manufacturedProduct'),
            ],
            __('modules.invoices.unitType') => [
                'data' => 'fg_unit_type',
                'name' => 'output_unit_types.unit_type',
                'title' => __('modules.invoices.unitType'),
            ],
            __('production::app.bom') => [
                'data' => 'bom_label',
                'name' => 'boms.code',
                'title' => __('production::app.bom'),
                'orderable' => false,
                'searchable' => false,
            ],
            __('production::app.plannedQty') => [
                'data' => 'planned_quantity',
                'name' => 'production_orders.planned_quantity',
                'title' => __('production::app.plannedQty'),
            ],
            __('production::app.materialAvailabilityShortColumn') => [
                'data' => 'material_availability',
                'name' => 'material_availability',
                'title' => __('production::app.materialAvailabilityShortColumn'),
                'orderable' => false,
                'searchable' => false,
                'exportable' => false,
                'className' => 'text-center',
            ],
            __('production::app.status') => [
                'data' => 'status',
                'name' => 'production_orders.status',
                'title' => __('production::app.status'),
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

    protected function statusColumnHtml(ProductionOrder $row): string
    {
        return ProductionOrderStatusBadge::html((string) $row->status);
    }

    protected function materialAvailabilityColumnHtml(ProductionOrder $row): string
    {
        if (! in_array((string) $row->status, [
            ProductionOrder::STATUS_DRAFT,
            ProductionOrder::STATUS_RELEASED,
            ProductionOrder::STATUS_IN_PROGRESS,
        ], true)) {
            return '<span class="text-lightest">—</span>';
        }

        $hasShortfall = $this->materialRequirementsSummary->shortfallStateForOrder($row);

        if ($hasShortfall === null) {
            return '<span class="text-lightest">—</span>';
        }

        $variant = $hasShortfall ? 'danger' : 'success';
        $fullLabel = __(
            $hasShortfall
                ? 'production::app.materialAvailabilityLabels.insufficient'
                : 'production::app.materialAvailabilityLabels.sufficient'
        );

        return '<span class="badge badge-' . $variant . '" data-toggle="tooltip" title="' . e($fullLabel) . '">' . e($fullLabel) . '</span>';
    }
}
