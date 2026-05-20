<?php

namespace Modules\Purchase\DataTables;

use App\DataTables\BaseDataTable;
use App\Enums\ProductType;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Modules\Pricing\Services\PricingService;
use Modules\Purchase\Entities\PurchaseProduct;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class PurchaseProductsDataTable extends BaseDataTable
{
    private $deleteProductPermission;

    private $editProductPermission;

    private $addProductPermission;

    public function __construct()
    {
        parent::__construct();
        $this->addProductPermission = user()->permission('add_product');
        $this->editProductPermission = user()->permission('edit_product');
        $this->deleteProductPermission = user()->permission('delete_product');
    }

    /**
     * Build DataTable class.
     *
     * @param  mixed  $query  Results from query() method.
     * @return DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);

        $datatables->addColumn('check', function ($row) {
            return '<input type="checkbox" class="select-table-row" id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" onclick="dataTableRowCheck('.$row->id.')">';
        });

        $datatables->addColumn('category', function ($row) {
            return ($row->category) ? $row->category->category_name : '';
        });

        $datatables->addColumn('sub_category', function ($row) {
            return ($row->subCategory) ? $row->subCategory->category_name : '';
        });

        $datatables->editColumn('description', function ($row) {
            return strip_tags($row->description);
        });
        $datatables->editColumn('specification', function ($row) {
            return $row->specification ? strip_tags((string) $row->specification) : '';
        });

        $datatables->addColumn('action', function ($row) {

            if (in_array('client', user_roles())) {
                return '<button type="button" class="btn-secondary rounded f-14 add-product" data-product-id="'.$row->id.'">
                        <i class="fa fa-plus mr-1"></i>
                    '.__('app.addToCart').'
                    </button>';
            }

            $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-'.$row->id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-'.$row->id.'" tabindex="0">';

            $action .= '<a href="'.route('purchase-products.show', [$row->id]).'" class="dropdown-item" data-product-id="'.$row->id.'"><i class="fa fa-eye mr-2"></i>'.__('app.view').'</a>';

            if ($this->editProductPermission == 'all' || ($this->editProductPermission == 'added' && user()->id == $row->added_by)) {
                $action .= '<a class="dropdown-item openRightModal" href="'.route('purchase-products.edit', [$row->id]).'">
                                <i class="fa fa-edit mr-2"></i>
                                '.trans('app.edit').'
                            </a>';
            }

            if ($this->deleteProductPermission == 'all' || ($this->deleteProductPermission == 'added' && user()->id == $row->added_by)) {
                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-product-id="'.$row->id.'">
                                <i class="fa fa-trash mr-2"></i>
                                '.trans('app.delete').'
                            </a>';
            }

            if ($this->addProductPermission == 'all' || $this->addProductPermission == 'added') {
                $action .= '<a class="dropdown-item openRightModal" href="'.route('purchase-products.create').'?duplicate_product='.$row->id.'">
                            <i class="fa fa-clone mr-2"></i>
                            '.trans('app.duplicate').'
                        </a>';
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });

        $datatables->editColumn('status', function ($row) {

            if ($this->editProductPermission == 'all' || ($this->editProductPermission == 'added' && user()->id == $row->added_by)) {
                $status = '<select class="form-control select-picker change-product-status" data-product-id="'.$row->id.'">';
                $status .= '<option ';

                if ($row->status == 'active') {
                    $status .= 'selected';
                }

                $status .= ' value="active" data-content="<i class=\'fa fa-circle mr-2 text-light-green\'></i> '.__('app.active').'">'.__('app.active').'</option>';
                $status .= '<option ';

                if ($row->status == 'inactive') {
                    $status .= 'selected';
                }

                $status .= ' value="inactive" data-content="<i class=\'fa fa-circle mr-2 text-red\'></i> '.__('app.inactive').'">'.__('app.inactive').'</option>';

                $status .= '</select>';
            } else {
                if ($row->status == 'active') {
                    $class = 'text-light-green';
                    $status = __('app.active');
                } else {
                    $class = 'text-red';
                    $status = __('app.inactive');
                }

                $status = '<i class="fa fa-circle mr-1 '.$class.' f-10"></i> '.$status;
            }

            return $status;
        });

        $datatables->editColumn('name', function ($row) {
            $name = $row->name;

            // Removed SKU concatenation
            return '<h5 class="mb-0 f-13 text-darkest-grey"><a href="'.route('purchase-products.show', [$row->id]).'" class="text-darkest-grey" >'.$name.'</a></h5>';
        });

        $datatables->editColumn('default_image', function ($row) {
            return '<img src="'.$row->image_url.'" class="border rounded height-35" />';
        });

        $datatables->editColumn('allow_purchase', function ($row) {

            if ($this->editProductPermission == 'all' || ($this->editProductPermission == 'added' && user()->id == $row->added_by)) {
                $status = '<select class="form-control select-picker change-purchase-allow" data-product-id="'.$row->id.'">';
                $status .= '<option ';

                if ($row->allow_purchase == 1) {
                    $status .= 'selected';
                }

                $status .= ' value="1" data-content="<i class=\'fa fa-circle mr-2 text-dark-green\'></i> '.__('app.allowed').'">'.__('app.allowed').'</option>';
                $status .= '<option ';

                if ($row->allow_purchase == 0) {
                    $status .= 'selected';
                }

                $status .= ' value="0" data-content="<i class=\'fa fa-circle mr-2 text-red\'></i> '.__('app.notAllowed').'">'.__('app.notAllowed').'</option>';

                $status .= '</select>';
            } else {
                if ($row->allow_purchase == 1) {
                    $status = '<i class="fa fa-circle mr-1 text-dark-green f-10"></i>'.__('app.allowed').'</label>';
                } else {
                    $status = '<i class="fa fa-circle mr-1 text-red f-10"></i>'.__('app.notAllowed').'</label>';
                }
            }

            return $status;
        });

        $datatables->addColumn('allow_purchase_export', function ($row) {
            return $row->allow_purchase == 1 ? __('app.allowed') : __('app.notAllowed');
        });

        $datatables->editColumn('price', function ($row) {
            $price = $row->price;

            if (in_array('client', user_roles()) && class_exists(PricingService::class)) {
                try {
                    $pricingService = new PricingService;
                    $calculated = $pricingService->calculate($row->id, user()->id, 1);
                    $price = $calculated['unit_price'];
                } catch (\Exception $e) {
                    // Fallback to base price
                }
            }

            if ($price != '') {
                if (! is_null($row->taxes)) {
                    $totalTax = 0;

                    foreach (json_decode($row->taxes) as $tax) {
                        $prodTax = PurchaseProduct::taxbyid($tax)->first();

                        if ($prodTax) {
                            $totalTax = $totalTax + ($price * ($prodTax->rate_percent / 100));
                        }
                    }

                    return currency_format($price + $totalTax);
                }

                return currency_format($price);
            }
        });

        $datatables->editColumn('stock_on_hand', function ($row) {
            if ($row->track_inventory == 1) {
                return (float) ($row->stock_on_hand ?? 0);
            } else {
                return '--';
            }
        });

        $datatables->editColumn('unit_type', function ($row) {
            return $row->unit ? $row->unit->unit_type : '--';
        });

        $datatables->editColumn('product_type', function ($row) {
            $type = $row->type !== null && $row->type !== '' ? (string) $row->type : null;

            return ProductType::labelFor($type);
        });

        $datatables->editColumn('expiry_date', function ($row) {
            return $row->expiry_date
                ? $row->expiry_date->copy()->timezone(company()->timezone)->format(company()->date_format)
                : '—';
        });

        $datatables->addIndexColumn();
        $datatables->smart(false);

        $datatables->setRowId(fn ($row) => 'row-'.$row->id);

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatables, Product::CUSTOM_FIELD_MODEL);

        $datatables->rawColumns(array_merge(['action', 'price', 'allow_purchase', 'check', 'name', 'default_image', 'status'], $customFieldColumns));

        return $datatables;
    }

    /**
     * @return Builder
     */
    public function query(PurchaseProduct $model)
    {
        $request = $this->request();

        $model = $model->with('tax', 'category', 'subCategory', 'inventory', 'inventory.product')
            ->join('unit_types', 'unit_types.id', '=', 'products.unit_id')
            ->select('products.id', 'products.name', 'products.sku', 'products.type', 'products.price', 'products.status', 'products.taxes', 'products.unit_id', 'products.opening_stock', 'products.track_inventory', 'products.allow_purchase', 'products.added_by', 'products.default_image', 'products.category_id', 'products.sub_category_id', 'products.description', 'products.product_source', 'products.brand', 'products.product_grade', 'products.expiry_date', 'products.specification', 'products.shelf_life_days')
            ->withSum('inventory as stock_on_hand', 'net_quantity');

        if (! is_null($request->category_id) && $request->category_id != 'all' && $request->category_id > 0) {
            $model->where('category_id', $request->category_id);
        }

        if (! is_null($request->unit_type_id) && $request->unit_type_id != 'all') {
            $model->where('unit_id', $request->unit_type_id);
        }

        if (! is_null($request->product_type) && $request->product_type != 'all') {
            $model->where('type', $request->product_type);
        }

        if (! is_null($request->sub_category_id) && $request->sub_category_id != 'all' && $request->sub_category_id > 0) {
            $model->where('sub_category_id', $request->sub_category_id);
        }

        if ($request->status != 'all' && ! is_null($request->status)) {
            $model = $model->where('products.status', '=', $request->status);
        }

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('products.name', 'like', '%'.request('searchText').'%')
                    ->orWhere('products.price', 'like', '%'.request('searchText').'%')
                    ->orWhere('products.sku', 'like', '%'.request('searchText').'%');
            });
        }

        if (user()->permission('view_product') == 'added') {
            $model->where('products.added_by', user()->id);
        }

        if (in_array('client', user_roles())) {
            $model->where('products.allow_purchase', 1);
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
        $dataTable = $this->setBuilder('products-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["products-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                    $(".change-product-status").selectpicker();
                    $(".change-purchase-allow").selectpicker();
                }',
            ]);

        $buttons = [
            Button::make([
                'extend' => 'colvis',
                'text' => '<i class="fa fa-columns"></i> '.trans('app.columns'),
                'columns' => ':not(:first):not(:last):not(.not-column-chooser)',
            ]),
        ];

        if (canDataTableExport()) {
            array_unshift($buttons, Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> '.trans('app.exportExcel')]));
        }

        $dataTable->buttons($buttons);

        return $dataTable;
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
            __('modules.productImage') => ['data' => 'default_image', 'name' => 'default_image', 'title' => __('modules.productImage'), 'exportable' => false],
            __('app.sku') => ['data' => 'sku', 'name' => 'sku', 'title' => __('app.sku')],
            __('app.menu.products') => ['data' => 'name', 'name' => 'name', 'title' => __('app.menu.products')],
            __('purchase::modules.product.type') => ['data' => 'product_type', 'name' => 'products.type', 'title' => __('purchase::modules.product.type')],
            __('modules.productCategory.productCategory') => ['data' => 'category', 'name' => 'category', 'title' => __('modules.productCategory.productCategory'), 'visible' => false],
            __('modules.productCategory.productSubCategory') => ['data' => 'sub_category', 'name' => 'sub_category', 'title' => __('modules.productCategory.productSubCategory'), 'visible' => false],
            __('app.description') => ['data' => 'description', 'name' => 'description', 'title' => __('app.description'), 'visible' => false],
            __('app.specification') => ['data' => 'specification', 'name' => 'specification', 'title' => __('app.specification'), 'visible' => false],
            __('app.shelfLifeDays') => ['data' => 'shelf_life_days', 'name' => 'shelf_life_days', 'title' => __('app.shelfLifeDays'), 'visible' => false],
            // Removed duplicate Name column
            __('app.price').' ('.__('app.inclusiveAllTaxes').')' => ['data' => 'price', 'name' => 'price', 'title' => __('app.price').' ('.__('app.inclusiveAllTaxes').')'],
            __('purchase::modules.product.dataTableTotalNetQtyAdjustments') => ['data' => 'stock_on_hand', 'name' => 'stock_on_hand', 'title' => __('purchase::modules.product.dataTableTotalNetQtyAdjustments')],
            __('app.unit_type').' ('.__('modules.unitType.unitType').')' => ['data' => 'unit_type', 'name' => 'unit_type', 'title' => __('modules.unitType.unitType')],
            __('app.productSource') => ['data' => 'product_source', 'name' => 'product_source', 'title' => __('app.productSource'), 'visible' => false],
            __('app.brand') => ['data' => 'brand', 'name' => 'brand', 'title' => __('app.brand'), 'visible' => false],
            __('app.productGrade') => ['data' => 'product_grade', 'name' => 'product_grade', 'title' => __('app.productGrade'), 'visible' => false],
            // Batch / per-line expiry lives under Purchase → Inventory; hide by default to avoid implying warehouse FEFO.
            __('purchase::modules.product.dataTableProductCardExpiry') => ['data' => 'expiry_date', 'name' => 'expiry_date', 'title' => __('purchase::modules.product.dataTableProductCardExpiry'), 'visible' => false],
            'allow_purchase_export' => ['data' => 'allow_purchase_export', 'name' => 'allow_purchase_export', 'visible' => false, 'title' => __('purchase::modules.product.dataTableAllowClientPurchase'), 'exportable' => ! in_array('client', user_roles()), 'className' => 'not-column-chooser'],
            __('purchase::modules.product.dataTableAllowClientPurchase') => ['data' => 'allow_purchase', 'name' => 'allow_purchase', 'visible' => ! in_array('client', user_roles()), 'title' => __('purchase::modules.product.dataTableAllowClientPurchase'), 'exportable' => false],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'exportable' => false, 'title' => __('app.status')],
        ];

        $customFieldMerge = CustomFieldGroup::customFieldsDataMerge(new Product);
        $excludeKeys = ['product_grade', 'product_source', 'brand', 'product-grade', 'product-source'];
        $customFieldMerge = array_diff_key($customFieldMerge, array_flip($excludeKeys));

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];

        return array_merge($data, $customFieldMerge, $action);
    }
}
