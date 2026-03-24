<?php

namespace Modules\Purchase\DataTables;

use App\DataTables\BaseDataTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Purchase\Entities\PurchaseInventory;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class PurchaseInventoryDataTable extends BaseDataTable
{
    private $deleteInventoryPermission;

    private $editInventoryPermission;

    private $customFieldGroup;

    private $customFieldAliasMap = [];

    public function __construct()
    {
        parent::__construct();
        $this->editInventoryPermission = user()->permission('edit_inventory');
        $this->deleteInventoryPermission = user()->permission('delete_inventory');
        $this->customFieldGroup = (new PurchaseInventory)->getCustomFieldGroupsWithFields();

        if ($this->customFieldGroup && ! empty($this->customFieldGroup->fields)) {
            foreach ($this->customFieldGroup->fields as $field) {
                if ($field->name == 'batch_number') {
                    continue;
                }

                $fieldName = Str::slug($field->name, '_');
                $this->customFieldAliasMap[$fieldName] = 'cf_' . $fieldName . '_' . $field->id;
            }
        }
    }

    /**
     * Build DataTable class.
     *
     * @param  mixed  $query  Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);

        $datatables->addColumn('check', function ($row) {
            return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
        });

        $datatables->editColumn('id', function ($row) {
            return $row->id;
        });

        $datatables->editColumn('date', function ($row) {
            return Carbon::parse($row->date)->translatedFormat($this->company->date_format);
        });

        // --- Tier 1: Core Identification & KPI ---

        // 1. SKU
        $datatables->addColumn('sku', function ($row) {
            $stock = $row->stocks->first();

            return optional($stock)->product->sku ?? '--';
        });

        // 2. Product Name
        $datatables->addColumn('product_name', function ($row) {
            $stock = $row->stocks->first();

            return optional($stock)->product->name ?? '--';
        });

        // 3. Warehouse Name
        $datatables->addColumn('warehouse_name', function ($row) {
            return $row->warehouse?->name ?? '--';
        });

        // 4. Available Quantity (Calculated)
        $datatables->addColumn('available_quantity', function ($row) {
            // Logic: On-Hand - Reserved.
            // Currently Reserved is 0 as per system limitation/form check.
            $stock = $row->stocks->first();
            $onHand = optional($stock)->net_quantity ?? 0;
            $reserved = 0;
            $available = $onHand - $reserved;

            return $available;
        });

        // 5. Ending Inventory (Removed as per user request - using Custom Field instead)
        // $datatables->addColumn('ending_inventory', function ($row) {
        //      return $row->ending_inventory ?? '--';
        // });

        // 6. Unit
        $datatables->addColumn('unit', function ($row) {
            $stock = $row->stocks->first();

            return optional(optional($stock)->product)->unit->unit_type ?? '--';
        });

        // 7. Stock Health / Status
        $datatables->editColumn('status', function ($row) {
            $stock = $row->stocks->first();
            $qty = optional($stock)->net_quantity ?? 0;
            $expDate = $stock && $stock->expiration_date ? Carbon::parse($stock->expiration_date) : null;

            $badges = [];

            // Base Status (Active/Inactive)
            $productStatus = optional(optional($stock)->product)->status ?? 'inactive';
            if ($productStatus == 'active') {
                $badges[] = '<i class="fa fa-circle text-light-green f-10" title="' . __('app.active') . '"></i>';
            } else {
                $badges[] = '<i class="fa fa-circle text-red f-10" title="' . __('app.inactive') . '"></i>';
            }

            // Health Indicators
            if ($qty <= 0) {
                $badges[] = '<span class="badge badge-danger">' . __('app.critical') . '</span>';
            } elseif ($qty < 10) { // Threshold can be dynamic later
                $badges[] = '<span class="badge badge-warning">' . __('app.low') . '</span>';
            } else {
                $badges[] = '<span class="badge badge-success">' . __('app.normal') . '</span>';
            }

            // Expiration Warning
            if ($expDate) {
                if ($expDate->isPast()) {
                    $badges[] = '<span class="badge badge-danger">' . __('app.expired') . '</span>';
                } elseif ($expDate->isFuture() && $expDate->diffInDays(now()) < 30) {
                    $badges[] = '<span class="badge badge-warning">' . __('purchase::modules.inventory.nearExpiryStatus') . '</span>';
                }
            }

            return implode(' ', $badges);
        });

        // --- Tier 2: Drill-Down / Hidden by Default ---
        $maxCfJoins = (int) (config('purchase.inventory_max_custom_field_joins') ?? 0);
        if ($maxCfJoins == 0 || ! isset($this->customFieldAliasMap['reserved_quantity'])) {
            $datatables->addColumn('reserved_quantity', function ($row) {
                return '0'; // Placeholder when custom fields disabled or no reserved_quantity cf
            });
        }

        $datatables->addColumn('inventory_value', function ($row) {
            $stock = $row->stocks->first();
            $qty = optional($stock)->net_quantity ?? 0;
            $price = optional(optional($stock)->product)->purchase_price ?? 0; // Cost Price

            return currency_format($qty * $price, $this->company->currency_id);
        });

        $datatables->addColumn('recent_inbound_date', function ($row) {
            return Carbon::parse($row->date)->translatedFormat($this->company->date_format);
        });

        // --- Tier 3: Details ---

        $datatables->addColumn('batch_number', function ($row) {
            return $row->batch_number_value ?? '--';
        });

        $datatables->addColumn('specification', function ($row) {
            $stock = $row->stocks->first();

            return optional(optional($stock)->product)->description ?? '--';
        });

        $datatables->addColumn('expiration_date', function ($row) {
            $stock = $row->stocks->first();
            $date = optional($stock)->expiration_date;

            return $date ? Carbon::parse($date)->translatedFormat($this->company->date_format) : '--';
        });

        $datatables->addColumn('outbound_quantity', function ($row) {
            return '--';
        });

        if ($this->customFieldGroup && ! empty($this->customFieldGroup->fields)) {
            $handledColumns = [
                'check',
                'id',
                'date',
                'sku',
                'product_name',
                'warehouse_name',
                'available_quantity',
                'unit',
                'status',
                'reserved_quantity',
                'inventory_value',
                'recent_inbound_date',
                'batch_number',
                'specification',
                'expiration_date',
                'outbound_quantity',
                'action',
            ];

            foreach ($this->customFieldGroup->fields as $field) {
                if ($field->name == 'batch_number') {
                    continue;
                }

                $fieldName = Str::slug($field->name, '_');

                if (in_array($fieldName, $handledColumns, true)) {
                    continue;
                }

                $alias = $this->customFieldAliasMap[$fieldName] ?? ('cf_' . $fieldName . '_' . $field->id);

                $datatables->addColumn($fieldName, function ($row) use ($alias) {
                    return $row->{$alias} ?? null;
                });
            }
        }

        // Action
        $datatables->addColumn('action', function ($row) {

            $action = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            if ($this->editInventoryPermission == 'all' || ($this->deleteInventoryPermission == 'added' && user()->id == $row->added_by)) {
                $action .= '<a href="' . route('purchase-inventory.show', [$row->id]) . '" class="dropdown-item openRightModal" data-inventory-id="' . $row->id . '"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';
            }

            if ($this->deleteInventoryPermission == 'all' || ($this->deleteInventoryPermission == 'added' && user()->id == $row->added_by)) {
                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-inventory-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });

        $datatables->addIndexColumn();
        $datatables->smart(false);
        $datatables->setRowId(fn($row) => 'row-' . $row->id);

        $datatables->rawColumns(['check', 'product_name', 'status', 'action', 'inventory_value', 'expiration_date']);

        return $datatables;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(PurchaseInventory $model)
    {
        $request = $this->request();

        $model = $model->select('purchase_inventory_adjustment.*')
            ->join('purchase_stock_adjustments', 'purchase_inventory_adjustment.id', '=', 'purchase_stock_adjustments.inventory_id')
            ->join('products', 'purchase_stock_adjustments.product_id', '=', 'products.id')
            ->addSelect(DB::raw('MAX(purchase_stock_adjustments.batch_number) as batch_number_value'));

        $maxCustomFieldJoins = (int) (config('purchase.inventory_max_custom_field_joins') ?? 0);

        $model->with(['warehouse', 'stocks', 'stocks.product', 'stocks.product.unit']);

        if ($request->status != 'all' && ! is_null($request->status)) {
            $model = $model->where('products.status', '=', $request->status);
        }

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('purchase_stock_adjustments.type', 'like', '%' . request('searchText') . '%')
                    ->orWhere('purchase_stock_adjustments.net_quantity', 'like', '%' . request('searchText') . '%')
                    ->orWhere('purchase_stock_adjustments.reference_number', 'like', '%' . request('searchText') . '%')
                    ->orWhere('purchase_stock_adjustments.description', 'like', '%' . request('searchText') . '%')
                    ->orWhere('products.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('products.sku', 'like', '%' . request('searchText') . '%');
            });
        }

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = Carbon::createFromFormat($this->company->date_format, $request->startDate)->startOfDay();
            $model = $model->where('purchase_inventory_adjustment.created_at', '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->endDate)->endOfDay();
            $model = $model->where('purchase_inventory_adjustment.created_at', '<=', $endDate);
        }

        if ($request->inventoryStatus != 'all' && ! is_null($request->inventoryStatus)) {
            if ($request->inventoryStatus == 'critical') {
                $model->where('purchase_stock_adjustments.net_quantity', '<=', 0);
            } elseif ($request->inventoryStatus == 'low') {
                $model->where('purchase_stock_adjustments.net_quantity', '>', 0)
                    ->where('purchase_stock_adjustments.net_quantity', '<', 10);
            } elseif ($request->inventoryStatus == 'normal') {
                $model->where('purchase_stock_adjustments.net_quantity', '>=', 10);
            } elseif ($request->inventoryStatus == 'expired') {
                $model->whereDate('purchase_stock_adjustments.expiration_date', '<', now());
            } elseif ($request->inventoryStatus == 'near_expiry') {
                $model->whereDate('purchase_stock_adjustments.expiration_date', '>', now())
                    ->whereDate('purchase_stock_adjustments.expiration_date', '<', now()->addDays(30));
            } else {
                // Fallback to product status if it matches 'active'/'inactive'
                $model->where('products.status', '=', $request->inventoryStatus);
            }
        }

        // Limit custom field JOINs (reuse $maxCustomFieldJoins from above)
        $fieldCount = 0;

        if ($this->customFieldGroup && ! empty($this->customFieldGroup->fields)) {
            $addedFieldIds = [];
            $addedAliases = [];
            foreach ($this->customFieldGroup->fields as $field) {
                if ($fieldCount >= $maxCustomFieldJoins) {
                    break;
                }
                if ($field->name == 'batch_number') {
                    continue;
                }

                if (in_array($field->id, $addedFieldIds)) {
                    continue;
                }

                $fieldName = Str::slug($field->name, '_');
                $alias = $this->customFieldAliasMap[$fieldName] ?? ('cf_' . $fieldName . '_' . $field->id);

                // Prevent duplicate column in SELECT when multiple fields map to same alias
                if (in_array($alias, $addedAliases)) {
                    continue;
                }
                $addedFieldIds[] = $field->id;
                $addedAliases[] = $alias;

                // Create a unique alias for each custom field join to avoid collisions
                $tableAlias = 'cf_table_' . $field->id;

                // Join custom_fields_data table for each field
                $model->leftJoin('custom_fields_data as ' . $tableAlias, function ($join) use ($tableAlias, $field) {
                    $join->on('purchase_inventory_adjustment.id', '=', $tableAlias . '.model_id')
                        ->where($tableAlias . '.custom_field_id', '=', $field->id)
                        ->where($tableAlias . '.model', '=', PurchaseInventory::CUSTOM_FIELD_MODEL);
                });

                $model->addSelect(DB::raw('MAX(' . $tableAlias . '.value) as ' . $alias));
                $fieldCount++;
            }
        }

        return $model->groupBy('purchase_inventory_adjustment.id');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->setBuilder('inventory-table-v5', 2)
            ->pageLength(10)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["inventory-table-v5"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                    $(".change-inventory-status").selectpicker();
                }',
            ])
            ->buttons(
                Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]),
                Button::make(['extend' => 'colvis', 'text' => '<i class="fa fa-columns"></i> ' . trans('app.columns')])
            );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $data = [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'visible' => ! in_array('client', user_roles()),
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => showId()],

            // --- Tier 1: Always Visible ---
            __('purchase::modules.reports.sku') => ['data' => 'sku', 'name' => 'sku', 'title' => __('purchase::modules.reports.sku'), 'orderable' => false],
            __('app.product') => ['data' => 'product_name', 'name' => 'product_name', 'title' => __('app.product'), 'orderable' => false],
            __('purchase::modules.inventory.warehouseName') => ['data' => 'warehouse_name', 'name' => 'warehouse_name', 'title' => __('purchase::modules.inventory.warehouseName'), 'orderable' => false],

            // Core KPIs
            __('purchase::modules.product.availableQuantity') => ['data' => 'available_quantity', 'name' => 'available_quantity', 'title' => __('purchase::modules.product.availableQuantity'), 'orderable' => false],
            // __('purchase::modules.inventory.endingInventory') => ['data' => 'ending_inventory', 'name' => 'ending_inventory', 'title' => __('purchase::modules.inventory.endingInventory'), 'orderable' => false],
            __('purchase::modules.inventory.unit') => ['data' => 'unit', 'name' => 'unit', 'title' => __('purchase::modules.inventory.unit'), 'orderable' => false],
            __('purchase::modules.inventory.stockHealth') => ['data' => 'status', 'name' => 'status', 'exportable' => false, 'title' => __('purchase::modules.inventory.stockHealth')],

            // --- Tier 2: Toggle / Selector (Hidden by Default) ---
            __('purchase::modules.inventory.reservedQuantity') => ['data' => 'reserved_quantity', 'name' => 'reserved_quantity', 'title' => __('purchase::modules.inventory.reservedQuantity'), 'visible' => false, 'orderable' => false],
            __('purchase::modules.inventory.inventoryValue') => ['data' => 'inventory_value', 'name' => 'inventory_value', 'title' => __('purchase::modules.inventory.inventoryValue'), 'visible' => false, 'orderable' => false],
            __('purchase::modules.inventory.recentInboundDate') => ['data' => 'recent_inbound_date', 'name' => 'recent_inbound_date', 'title' => __('purchase::modules.inventory.recentInboundDate'), 'visible' => false, 'orderable' => false],

            // --- Tier 3: Details (Hidden by Default) ---
            __('purchase::modules.inventory.batchNumber') => ['data' => 'batch_number', 'name' => 'batch_number', 'title' => __('purchase::modules.inventory.batchNumber'), 'visible' => false, 'orderable' => false],
            __('purchase::modules.inventory.specification') => ['data' => 'specification', 'name' => 'specification', 'title' => __('purchase::modules.inventory.specification'), 'visible' => false, 'orderable' => false],
            __('purchase::modules.inventory.expirationDate') => ['data' => 'expiration_date', 'name' => 'expiration_date', 'title' => __('purchase::modules.inventory.expirationDate'), 'visible' => false, 'orderable' => false],
            __('purchase::modules.inventory.outboundQuantity') => ['data' => 'outbound_quantity', 'name' => 'outbound_quantity', 'title' => __('purchase::modules.inventory.outboundQuantity'), 'visible' => false, 'orderable' => false],
        ];

        $maxCustomFieldJoins = (int) (config('purchase.inventory_max_custom_field_joins') ?? 0);
        if ($maxCustomFieldJoins > 0 && $this->customFieldGroup && ! empty($this->customFieldGroup->fields)) {
            foreach ($this->customFieldGroup->fields as $field) {
                if ($field->name == 'batch_number') {
                    continue;
                }

                $found = false;

                // Check if this field overrides a standard column
                foreach ($data as $key => &$column) {
                    // Normalize names for comparison (convert to lower case and snake case if needed)
                    $columnName = isset($column['name']) ? Str::slug($column['name'], '_') : '';
                    $fieldName = Str::slug($field->name, '_');

                    if ($columnName == $fieldName) {
                        $column['visible'] = ($field->visible == 'true');
                        $column['exportable'] = ($field->export == 1);

                        $alias = $this->customFieldAliasMap[$fieldName] ?? ('cf_' . $fieldName . '_' . $field->id);
                        $column['data'] = $alias;
                        $column['name'] = $alias;

                        $found = true;
                        break;
                    }
                }
                unset($column);

                if (! $found) {
                    $fieldName = Str::slug($field->name, '_');
                    $alias = $this->customFieldAliasMap[$fieldName] ?? $fieldName;
                    $data[$fieldName] = [
                        'data' => $alias,
                        'name' => $alias,
                        'title' => __($field->label),
                        'orderable' => true,
                        'searchable' => true,
                        'visible' => ($field->visible == 'true'),
                        'exportable' => ($field->export == 1),
                    ];
                }
            }
        }

        $data[] = Column::computed('action', __('app.action'))
            ->exportable(false)
            ->printable(false)
            ->orderable(false)
            ->searchable(false)
            ->addClass('text-right pr-20');

        return $data;
    }
}
