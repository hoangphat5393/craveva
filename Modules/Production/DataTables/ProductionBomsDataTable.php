<?php

declare(strict_types=1);

namespace Modules\Production\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Production\Entities\ProductionBom;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ProductionBomsDataTable extends BaseDataTable
{
    private string $viewProductionBomPermission;

    private string $editProductionBomPermission;

    public function __construct()
    {
        parent::__construct();

        $this->viewProductionBomPermission = (string) user()->permission('view_production_orders');
        $this->editProductionBomPermission = (string) user()->permission('edit_production_orders');
    }

    /**
     * @param  Builder<ProductionBom>  $query
     */
    public function dataTable($query): DataTableAbstract
    {
        $datatables = datatables()->eloquent($query);

        $datatables->editColumn('output_product_name', fn(ProductionBom $row): string => e((string) ($row->output_product_name ?: '—')));
        $datatables->editColumn('fg_unit_type', fn(ProductionBom $row): string => e((string) ($row->fg_unit_type ?: '—')));
        $datatables->editColumn('version', fn(ProductionBom $row): string => e((string) ($row->version ?: '—')));
        $datatables->editColumn('code', fn(ProductionBom $row): string => e((string) ($row->code ?: '—')));
        $datatables->editColumn('items_count', fn(ProductionBom $row): string => (string) ((int) ($row->items_count ?? 0)));
        $datatables->editColumn('is_default', function (ProductionBom $row): string {
            if ($row->is_default) {
                return '<i class="fa fa-check-circle text-dark-green" data-toggle="tooltip" title="' . e(__('app.yes')) . '"></i>';
            }

            return '<i class="fa fa-times text-red" data-toggle="tooltip" title="' . e(__('app.no')) . '"></i>';
        });
        $datatables->addColumn('is_default_export', fn(ProductionBom $row): string => $row->is_default ? __('app.yes') : __('app.no'));

        $datatables->addColumn('action', function (ProductionBom $row): string {
            $canView = in_array($this->viewProductionBomPermission, ['all', 'added', 'owned', 'both'], true);
            $canEdit = in_array($this->editProductionBomPermission, ['all', 'added', 'owned', 'both'], true)
                && (int) ($row->production_orders_count ?? 0) === 0;

            if (! $canView && ! $canEdit) {
                return '<span class="text-lightest">—</span>';
            }

            $action = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="production-bom-actions-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="production-bom-actions-' . $row->id . '" tabindex="0">';

            if ($canView) {
                $action .= '<a class="dropdown-item" href="' . route('production.boms.show', [$row->id]) . '"><i class="fa fa-eye mr-2 text-dark-grey"></i>' . e(__('app.view')) . '</a>';
            }

            if ($canEdit) {
                $action .= '<a class="dropdown-item openRightModal" href="' . route('production.boms.edit', [$row->id]) . '" data-redirect-url="' . e(route('production.boms.index')) . '"><i class="fa fa-edit mr-2 text-dark-grey"></i>' . e(__('app.edit')) . '</a>';
                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-bom-id="' . $row->id . '"><i class="fa fa-trash mr-2 text-dark-grey"></i>' . e(__('app.delete')) . '</a>';
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });

        $datatables->smart(false);
        $datatables->setRowId(fn(ProductionBom $row): string => 'row-' . $row->id);
        $datatables->rawColumns(['action', 'is_default']);

        return $datatables;
    }

    /**
     * @return Builder<ProductionBom>
     */
    public function query(ProductionBom $model): Builder
    {
        $request = $this->request();

        $query = $model->newQuery()
            ->leftJoin('products as output_products', 'output_products.id', '=', 'production_boms.output_product_id')
            ->leftJoin('unit_types as output_unit_types', 'output_unit_types.id', '=', 'output_products.unit_id')
            ->select(
                'production_boms.*',
                'output_products.name as output_product_name',
                'output_unit_types.unit_type as fg_unit_type',
            )
            ->withCount(['items', 'productionOrders'])
            ->where('production_boms.company_id', (int) company()->id);

        if (
            ! is_null($request->unit_type_id)
            && (string) $request->unit_type_id !== ''
            && (string) $request->unit_type_id !== 'all'
        ) {
            $query->where('output_products.unit_id', (int) $request->unit_type_id);
        }

        if (($request->searchText ?? '') !== '') {
            $term = '%' . $request->searchText . '%';

            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('production_boms.id', 'like', $term)
                    ->orWhere('production_boms.code', 'like', $term)
                    ->orWhere('production_boms.version', 'like', $term)
                    ->orWhere('output_products.name', 'like', $term);
            });
        }

        if (! $request->has('order')) {
            $query->orderByDesc('production_boms.id');
        }

        return $query;
    }

    public function html()
    {
        $dataTable = $this->setBuilder('production-boms-table', 0)
            ->parameters([
                'order' => [[0, 'desc']],
                'initComplete' => 'function () {
                    try {
                        if (window.LaravelDataTables && window.LaravelDataTables["production-boms-table"] && window.LaravelDataTables["production-boms-table"].buttons) {
                            window.LaravelDataTables["production-boms-table"].buttons().container().appendTo("#table-actions");
                        }
                    } catch (error) {
                        console.error("Production BOMs DataTable init error:", error);
                    }
                }',
                'fnDrawCallback' => 'function () {
                    try {
                        $("body").tooltip({
                            selector: \'[data-toggle="tooltip"]\'
                        });
                    } catch (error) {
                        console.error("Production BOMs DataTable draw error:", error);
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
                'name' => 'production_boms.id',
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
            __('production::app.bomVersion') => [
                'data' => 'version',
                'name' => 'production_boms.version',
                'title' => __('production::app.bomVersion'),
            ],
            __('production::app.bomCode') => [
                'data' => 'code',
                'name' => 'production_boms.code',
                'title' => __('production::app.bomCode'),
            ],
            __('production::app.bomLines') => [
                'data' => 'items_count',
                'name' => 'items_count',
                'title' => __('production::app.bomLines'),
                'searchable' => false,
            ],
            __('production::app.bomDefaultForManufacturedProduct') => [
                'data' => 'is_default',
                'name' => 'production_boms.is_default',
                'title' => __('production::app.bomDefaultForManufacturedProduct'),
                'searchable' => false,
                'exportable' => false,
                'className' => 'text-center',
            ],
            'is_default_export' => [
                'data' => 'is_default_export',
                'name' => 'is_default_export',
                'title' => __('production::app.bomDefaultForManufacturedProduct'),
                'visible' => false,
                'exportable' => true,
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
