<?php

namespace Modules\Warehouse\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Button;

class WarehouseMovementsDataTable extends BaseDataTable
{
    /**
     * @param  Builder<StockMovement>  $query
     */
    public function dataTable($query): DataTableAbstract
    {
        $datatables = datatables()->eloquent($query);

        $datatables->editColumn('created_at', function (StockMovement $row): string {
            return $row->created_at
                ? $row->created_at->timezone(company()->timezone)->format(company()->date_format.' H:i')
                : '—';
        });

        $datatables->editColumn('movement_type', function (StockMovement $row): string {
            return match ($row->movement_type) {
                'inbound' => '<span class="badge badge-success">'.e(__('warehouse::app.inbound')).'</span>',
                'outbound' => '<span class="badge badge-warning">'.e(__('warehouse::app.outbound')).'</span>',
                default => '<span class="badge badge-secondary">'.e((string) $row->movement_type).'</span>',
            };
        });

        $datatables->addColumn('product_label', function (StockMovement $row): string {
            if (! $row->product) {
                return '—';
            }

            return e($row->product->name).'<br><small class="text-lightest">'.e($row->product->sku ?? '').'</small>';
        });

        $datatables->addColumn('warehouse_from_label', function (StockMovement $row): string {
            if (! $row->warehouseFrom) {
                return '—';
            }

            $code = $row->warehouseFrom->code ? ' ('.$row->warehouseFrom->code.')' : '';

            return e($row->warehouseFrom->name.$code);
        });

        $datatables->addColumn('warehouse_to_label', function (StockMovement $row): string {
            if (! $row->warehouseTo) {
                return '—';
            }

            $code = $row->warehouseTo->code ? ' ('.$row->warehouseTo->code.')' : '';

            return e($row->warehouseTo->name.$code);
        });

        $datatables->editColumn('quantity', function (StockMovement $row): string {
            return '<span class="font-weight-semibold">'.$this->formatQuantity($row->quantity).'</span>';
        });

        $datatables->editColumn('batch_number', fn (StockMovement $row): string => '<span class="text-dark-grey">'.e($row->batch_number ?: '—').'</span>');

        $datatables->addColumn('reference_label', function (StockMovement $row): string {
            $label = $this->resolveReferenceLabel($row->reference_type);
            $html = '<small class="text-dark-grey movement-ref-line" title="'.e($label).'">'.e($label).'</small>';

            if ($row->reference_id) {
                $html .= '<br><small class="text-lightest">'.e(__('app.id')).' #'.$row->reference_id.'</small>';
            }

            return $html;
        });

        $datatables->addIndexColumn();
        $datatables->smart(false);
        $datatables->setRowId(fn (StockMovement $row): string => 'row-'.$row->id);
        $datatables->rawColumns(['batch_number', 'movement_type', 'product_label', 'quantity', 'reference_label', 'warehouse_from_label', 'warehouse_to_label']);

        return $datatables;
    }

    /**
     * @return Builder<StockMovement>
     */
    public function query(StockMovement $model): Builder
    {
        $request = $this->request();
        $companyId = company()?->id ?? user()?->company_id;

        $model = $model->newQuery()
            ->with([
                'product:id,name,sku',
                'warehouseFrom:id,name,code',
                'warehouseTo:id,name,code',
            ])
            ->select('stock_movements.*');

        if ($companyId) {
            $model->where('company_id', (int) $companyId);
        }

        if (($request->warehouse_id ?? '') !== '') {
            $warehouseId = (int) $request->warehouse_id;
            $model->where(function (Builder $query) use ($warehouseId): void {
                $query->where('warehouse_from_id', $warehouseId)
                    ->orWhere('warehouse_to_id', $warehouseId);
            });
        }

        if (($request->movement_type ?? '') !== '') {
            $model->where('movement_type', $request->movement_type);
        }

        if (($request->searchText ?? '') !== '') {
            $term = '%'.$request->searchText.'%';
            $model->whereHas('product', function (Builder $query) use ($term): void {
                $query->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term);
            });
        }

        if (! $request->has('order')) {
            $model->orderByDesc('created_at')->orderByDesc('id');
        }

        return $model;
    }

    public function html()
    {
        $dataTable = $this->setBuilder('warehouse-movements-table', 2)
            ->parameters([
                'order' => [[2, 'desc'], [1, 'desc']],
                'initComplete' => 'function () {
                    try {
                        if (window.LaravelDataTables && window.LaravelDataTables["warehouse-movements-table"] && window.LaravelDataTables["warehouse-movements-table"].buttons) {
                            window.LaravelDataTables["warehouse-movements-table"].buttons().container().appendTo("#table-actions");
                        }
                    } catch (error) {
                        console.error("Warehouse movements DataTable init error:", error);
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
                'name' => 'stock_movements.id',
                'title' => __('app.id'),
                'visible' => showId(),
            ],
            __('warehouse::app.dateTime') => [
                'data' => 'created_at',
                'name' => 'stock_movements.created_at',
                'title' => __('warehouse::app.dateTime'),
            ],
            __('warehouse::app.movementType') => [
                'data' => 'movement_type',
                'name' => 'stock_movements.movement_type',
                'title' => __('warehouse::app.movementType'),
            ],
            __('warehouse::app.product') => [
                'data' => 'product_label',
                'name' => 'product.name',
                'title' => __('warehouse::app.product'),
                'orderable' => false,
            ],
            __('warehouse::app.fromWarehouse') => [
                'data' => 'warehouse_from_label',
                'name' => 'warehouseFrom.name',
                'title' => __('warehouse::app.fromWarehouse'),
                'orderable' => false,
            ],
            __('warehouse::app.toWarehouse') => [
                'data' => 'warehouse_to_label',
                'name' => 'warehouseTo.name',
                'title' => __('warehouse::app.toWarehouse'),
                'orderable' => false,
            ],
            __('warehouse::app.quantity') => [
                'data' => 'quantity',
                'name' => 'stock_movements.quantity',
                'title' => __('warehouse::app.quantity'),
                'className' => 'text-right',
            ],
            __('warehouse::app.batch') => [
                'data' => 'batch_number',
                'name' => 'stock_movements.batch_number',
                'title' => __('warehouse::app.batch'),
            ],
            __('warehouse::app.reference') => [
                'data' => 'reference_label',
                'name' => 'stock_movements.reference_type',
                'title' => __('warehouse::app.reference'),
                'orderable' => false,
            ],
        ];
    }

    private function resolveReferenceLabel(?string $referenceType): string
    {
        $raw = $referenceType ? (str_contains($referenceType, '\\') ? class_basename($referenceType) : $referenceType) : '';
        $key = strtolower(str_replace(['\\', '_'], ['', '_'], $raw));

        return match ($key) {
            'manual_warehouse_stock' => __('warehouse::app.reference_manual_warehouse_stock'),
            'manual_transfer' => __('warehouse::app.reference_manual_transfer'),
            'invoice' => __('warehouse::app.reference_invoice'),
            'invoice_stock_reversal' => __('warehouse::app.reference_invoice_stock_reversal'),
            'creditnotes' => __('warehouse::app.reference_credit_notes'),
            'credit_note_stock_reversal' => __('warehouse::app.reference_credit_note_stock_reversal'),
            'purchasevendorcredit' => __('warehouse::app.reference_purchase_vendor_credit'),
            'purchase_vendor_credit_stock_reversal' => __('warehouse::app.reference_purchase_vendor_credit_stock_reversal'),
            'salesshipment' => __('warehouse::app.reference_sales_shipment'),
            'sales_shipment_stock_reversal' => __('warehouse::app.reference_sales_shipment_stock_reversal'),
            'productionbatch' => __('warehouse::app.reference_production_batch'),
            'transfer' => __('warehouse::app.reference_transfer'),
            default => $raw ? Str::headline(str_replace('_', ' ', $raw)) : '—',
        };
    }

    private function formatQuantity(float|int|string|null $value): string
    {
        $formatted = rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }
}
