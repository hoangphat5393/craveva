<?php

namespace App\DataTables;

use App\Helper\Common;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\OrderCart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Modules\Pricing\Services\PricingService;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ProductsDataTable extends BaseDataTable
{
    private $deleteProductPermission;

    private $editProductPermission;

    public function __construct()
    {
        parent::__construct();
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

        $datatables->addColumn('check', fn($row) => $this->checkBox($row));
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
        $datatables->addColumn('unit_type_name', function ($row) {
            return $row->unit ? $row->unit->unit_type : '';
        });
        $datatables->addColumn('action', function ($row) {

            if (in_array('client', user_roles())) {
                $cartProductIds = OrderCart::where('client_id', user()->id)->pluck('product_id')->toArray();
                $addToCart = '<i class="fa fa-plus mr-1"></i>' . __('app.addToCart');
                if (in_array($row->id, $cartProductIds)) {
                    $addToCart = __('app.addedToCart');
                }

                return '<button type="button" class="btn-secondary rounded f-14 add-product" data-product-id="' . $row->id . '" id="add-to-cart-btn-' . $row->id . '">
                        ' . $addToCart . '
                    </button>';
            }

            $action = '<div class="task_view">
            <a href="' . route('products.show', [$row->id]) . '"
                class="taskView openRightModal text-darkest-grey f-w-500" data-product-id="' . $row->id . '">' . __('app.view') . '</a>

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            if ($this->editProductPermission == 'all' || ($this->editProductPermission == 'added' && user()->id == $row->added_by)) {
                $action .= '<a class="dropdown-item openRightModal" href="' . route('products.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
            }

            if ($this->deleteProductPermission == 'all' || ($this->deleteProductPermission == 'added' && user()->id == $row->added_by)) {
                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-product-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });

        $datatables->editColumn('name', function ($row) {
            $name = $row->name;

            return '<a href="' . route('products.show', [$row->id]) . '" class="openRightModal text-darkest-grey" >' . $name . '</a>';
        });
        $datatables->editColumn('default_image', function ($row) {
            return '<img src="' . $row->image_url . '" class="rounded height-35" />';
        });
        $datatables->editColumn('expiry_date', function ($row) {
            return $row->expiry_date
                ? $row->expiry_date->copy()->timezone(company()->timezone)->format(company()->date_format)
                : '—';
        });

        $datatables->editColumn('allow_purchase', function ($row) {

            if ($row->allow_purchase == 1) {
                $status = '<i class="fa fa-circle mr-1 text-dark-green f-10"></i>' . __('app.allowed');
            } else {
                $status = '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.notAllowed');
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
                    $pricingService = app(PricingService::class);
                    $calculated = $pricingService->calculate($row->id, user()->id, 1);
                    $price = $calculated['unit_price'];
                } catch (\Exception $e) {
                    // Fallback to base price
                }
            }

            if (! is_null($row->taxes)) {
                $totalTax = 0;

                foreach (json_decode($row->taxes) as $tax) {
                    $prodTax = Product::taxbyid($tax)->first();

                    if ($prodTax) {
                        $totalTax = $totalTax + ($price * ($prodTax->rate_percent / 100));
                    }
                }

                return currency_format($price + $totalTax, company()->currency_id);
            }

            return currency_format($price, company()->currency_id);
        });
        $datatables->addIndexColumn();
        $datatables->smart(false);
        $datatables->setRowId(fn($row) => 'row-' . $row->id);

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatables, Product::CUSTOM_FIELD_MODEL);

        $datatables->rawColumns(array_merge(['action', 'price', 'allow_purchase', 'check', 'name', 'default_image'], $customFieldColumns));

        return $datatables;
    }

    /**
     * @return Builder
     */
    public function query(Product $model)
    {
        $request = $this->request();

        $model = $model->with('tax', 'category', 'subCategory', 'unit')->select('id', 'name', 'sku', 'price', 'taxes', 'allow_purchase', 'added_by', 'default_image', 'category_id', 'sub_category_id', 'description', 'product_source', 'brand', 'product_grade', 'expiry_date', 'specification', 'shelf_life_days', 'unit_id');

        if (! is_null($request->category_id) && $request->category_id != 'all' && $request->category_id > 0) {
            $model->where('category_id', $request->category_id);
        }

        if (! is_null($request->unit_type_id) && $request->unit_type_id != 'all') {
            $model->where('unit_id', $request->unit_type_id);
        }

        if (! is_null($request->sub_category_id) && $request->sub_category_id != 'all' && $request->sub_category_id > 0) {
            $model->where('sub_category_id', $request->sub_category_id);
        }

        if ($request->searchText != '') {
            $safeTerm = Common::safeString(request('searchText'));
            $model->where(function ($query) use ($safeTerm) {
                $query->where('products.name', 'like', '%' . $safeTerm . '%')
                    ->orWhere('products.price', 'like', '%' . $safeTerm . '%')
                    ->orWhere('products.sku', 'like', '%' . $safeTerm . '%');
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
                    })
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

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
            __('modules.productCategory.productCategory') => ['data' => 'category', 'name' => 'category', 'title' => __('modules.productCategory.productCategory'), 'visible' => false],
            __('modules.productCategory.productSubCategory') => ['data' => 'sub_category', 'name' => 'sub_category', 'title' => __('modules.productCategory.productSubCategory'), 'visible' => false],
            __('app.description') => ['data' => 'description', 'name' => 'description', 'title' => __('app.description'), 'visible' => false],
            __('app.specification') => ['data' => 'specification', 'name' => 'specification', 'title' => __('app.specification'), 'visible' => false],
            __('app.shelfLifeDays') => ['data' => 'shelf_life_days', 'name' => 'shelf_life_days', 'title' => __('app.shelfLifeDays'), 'visible' => false],
            __('modules.unitType.unitType') => ['data' => 'unit_type_name', 'name' => 'unit_type_name', 'title' => __('modules.unitType.unitType'), 'visible' => false],
            __('app.price') . ' (' . __('app.inclusiveAllTaxes') . ')' => ['data' => 'price', 'name' => 'price', 'title' => __('app.price') . ' (' . __('app.inclusiveAllTaxes') . ')'],
            __('app.productSource') => ['data' => 'product_source', 'name' => 'product_source', 'title' => __('app.productSource'), 'visible' => false],
            __('app.brand') => ['data' => 'brand', 'name' => 'brand', 'title' => __('app.brand'), 'visible' => false],
            __('app.productGrade') => ['data' => 'product_grade', 'name' => 'product_grade', 'title' => __('app.productGrade'), 'visible' => false],
            __('app.expiryDate') => ['data' => 'expiry_date', 'name' => 'expiry_date', 'title' => __('app.expiryDate'), 'visible' => true],
            'allow_purchase_export' => ['data' => 'allow_purchase_export', 'name' => 'allow_purchase_export', 'visible' => false, 'title' => __('app.clientPurchase'), 'exportable' => ! in_array('client', user_roles())],
            __('app.clientPurchase') => ['data' => 'allow_purchase', 'name' => 'allow_purchase', 'visible' => ! in_array('client', user_roles()), 'title' => __('app.clientPurchase'), 'exportable' => false],
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
