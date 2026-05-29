<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Http\Controllers\Concerns\HandlesWarehouseErrors;
use Modules\Warehouse\Services\StockMovementService;

class WarehouseTransferController extends AccountBaseController
{
    use HandlesWarehouseErrors;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_if(! in_array('warehouse', user_modules()), 403, __('warehouse::app.err_module_not_warehouse'));

            return $next($request);
        });
    }

    public function create()
    {
        $transferPermission = user()->permission('manage_warehouse_transfer');
        abort_if(! in_array($transferPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $warehouseTable = (new Warehouse)->getTable();
        $warehouses = Warehouse::where('status', 'active')
            ->when(Schema::hasColumn($warehouseTable, 'sort_order'), function ($query) {
                $query->orderBy('sort_order')->orderBy('name');
            }, function ($query) {
                $query->orderBy('name');
            })
            ->get();
        $products = Product::select('id', 'name', 'sku')->get();

        $this->pageTitle = 'warehouse::app.transferStock';
        $this->pageIcon = 'ti-layout';
        $this->warehouses = $warehouses;
        $this->products = $products;

        if (request()->ajax()) {
            $html = view('warehouse::transfer.ajax.create', $this->data)->render();

            return response()->json(Reply::dataOnly([
                'status' => 'success',
                'html' => $html,
                'title' => __('warehouse::app.transferStock'),
            ]));
        }

        return view('warehouse::transfer.create', $this->data);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $transferPermission = user()->permission('manage_warehouse_transfer');
        abort_if(! in_array($transferPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $companyId = $this->warehouseCompanyId();
        if (! $companyId) {
            return $this->warehouseFailResponse($request, __('warehouse::app.err_company_context_missing'));
        }

        $request->validate([
            'warehouse_from_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where('company_id', $companyId),
                'different:warehouse_to_id',
            ],
            'warehouse_to_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('company_id', $companyId)],
            'quantity' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ], [
            'warehouse_from_id.required' => __('warehouse::app.validation_source_warehouse_required'),
            'warehouse_from_id.exists' => __('warehouse::app.validation_source_warehouse_invalid_company'),
            'warehouse_from_id.different' => __('warehouse::app.err_transfer_same_warehouse'),
            'warehouse_to_id.required' => __('warehouse::app.validation_destination_warehouse_required'),
            'warehouse_to_id.exists' => __('warehouse::app.validation_destination_warehouse_invalid_company'),
            'product_id.required' => __('warehouse::app.validation_product_required'),
            'product_id.exists' => __('warehouse::app.validation_product_invalid_company'),
            'quantity.required' => __('warehouse::app.validation_quantity_required'),
            'quantity.numeric' => __('warehouse::app.validation_quantity_numeric'),
            'quantity.min' => __('warehouse::app.validation_quantity_positive'),
            'description.max' => __('warehouse::app.validation_description_max'),
        ], [
            'warehouse_from_id' => __('source warehouse'),
            'warehouse_to_id' => __('destination warehouse'),
            'product_id' => __('product'),
            'quantity' => __('quantity'),
            'description' => __('description'),
        ]);

        try {
            app(StockMovementService::class)->recordTransfer([
                'company_id' => $companyId,
                'warehouse_from_id' => (int) $request->warehouse_from_id,
                'warehouse_to_id' => (int) $request->warehouse_to_id,
                'product_id' => (int) $request->product_id,
                'quantity' => (float) $request->quantity,
                'batch_number' => null,
                'expiry_date' => null,
                'reference_type' => 'manual_transfer',
                'reference_id' => user()?->id,
            ]);

            if ($request->ajax()) {
                session()->flash('success', __('warehouse::app.success_warehouse_transfer_saved'));

                return response()->json(Reply::redirect(route('warehouse.stock.index'), 'warehouse::app.success_warehouse_transfer_saved'));
            }

            return redirect()->route('warehouse.stock.index')->with('success', __('warehouse::app.success_warehouse_transfer_saved'));
        } catch (\Throwable $e) {
            return $this->handleWarehouseThrowable($request, 'Warehouse transfer store', $e, $request->except(['_token']));
        }
    }
}
