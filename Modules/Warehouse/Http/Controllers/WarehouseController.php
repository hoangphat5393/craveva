<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Http\Controllers\Concerns\HandlesWarehouseErrors;

class WarehouseController extends AccountBaseController
{
    use HandlesWarehouseErrors;

    private function hasSortOrderColumn(): bool
    {
        return Schema::hasColumn((new Warehouse)->getTable(), 'sort_order');
    }

    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_if(! in_array('warehouse', user_modules()), 403, __('warehouse::app.err_module_not_warehouse'));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $viewPermission = user()->permission('view_warehouses');
        abort_if(! in_array($viewPermission, ['all', 'added', 'owned', 'both'], true), 403, __('warehouse::app.err_permission_denied'));

        $allowedSortColumns = ['name', 'code', 'address', 'status', 'is_default'];
        $sortBy = $request->get('sort_by');
        $sortDir = strtolower((string) $request->get('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $hasColumnSort = in_array($sortBy, $allowedSortColumns, true);

        $query = Warehouse::query();

        if ($hasColumnSort) {
            $query->orderBy($sortBy, $sortDir)->orderBy('id');
        } else {
            $query->orderByDesc('id');
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('code', 'like', $term)
                    ->orWhere('address', 'like', $term);
            });
        }

        $this->pageTitle = 'warehouse::app.allWarehouses';
        $this->pageIcon = 'ti-layout';
        $this->warehouseSortBy = $hasColumnSort ? $sortBy : null;
        $this->warehouseSortDir = $sortDir;
        $this->warehouses = $query->paginate(20)->withQueryString();

        return view('warehouse::index', $this->data);
    }

    /**
     * Persist display order for warehouses (same company). Drag-and-drop on the index table.
     */
    public function updateOrder(Request $request): JsonResponse
    {
        if (! $this->hasSortOrderColumn()) {
            return response()->json(Reply::error(__('messages.invalidData')));
        }

        $editPermission = user()->permission('edit_warehouses');
        abort_if(! in_array($editPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $request->validate([
            'order' => 'required|array|min:1',
            'order.*' => 'integer|exists:warehouses,id',
        ]);

        $companyId = $this->warehouseCompanyId();
        if (! $companyId) {
            return response()->json(Reply::error(__('warehouse::app.err_company_context_missing')), 422);
        }

        $ids = array_values(array_map('intval', $request->input('order', [])));
        if ($ids === [] || count(array_unique($ids)) !== count($ids)) {
            return response()->json(Reply::error(__('messages.invalidData')));
        }

        $allIds = Warehouse::query()
            ->where('company_id', (int) $companyId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($ids === [] || count($ids) !== count($allIds) || count(array_diff($allIds, $ids)) > 0) {
            return response()->json(Reply::error(__('messages.invalidData')));
        }

        try {
            DB::transaction(function () use ($ids) {
                foreach ($ids as $index => $id) {
                    Warehouse::where('id', $id)->update(['sort_order' => $index]);
                }
            });

            return response()->json(Reply::success(__('messages.updateSuccess')));
        } catch (\Throwable $e) {
            return $this->handleWarehouseThrowable($request, 'Warehouse updateOrder', $e, [
                'order' => $request->input('order'),
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $addPermission = user()->permission('add_warehouses');
        abort_if(! in_array($addPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $this->pageTitle = 'warehouse::app.createTitle';
        $this->pageIcon = 'ti-layout';

        if (request()->ajax()) {
            $html = view('warehouse::ajax.create', $this->data)->render();

            return response()->json(Reply::dataOnly([
                'status' => 'success',
                'html' => $html,
                'title' => __('warehouse::app.createTitle'),
            ]));
        }

        return view('warehouse::create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $addPermission = user()->permission('add_warehouses');
        abort_if(! in_array($addPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $companyId = $this->warehouseCompanyId();
        if (! $companyId) {
            return $this->warehouseFailResponse($request, __('warehouse::app.err_company_context_missing'));
        }

        try {
            DB::beginTransaction();

            $warehouseData = [
                'company_id' => $companyId,
                'name' => $request->name,
                'code' => $request->code,
                'address' => $request->address,
                'description' => $request->description,
                'status' => $request->status,
                'is_default' => $request->has('is_default'),
            ];

            if ($this->hasSortOrderColumn()) {
                $nextSort = (int) (Warehouse::where('company_id', (int) $companyId)->max('sort_order') ?? -1) + 1;
                $warehouseData['sort_order'] = $nextSort;
            }

            $warehouse = Warehouse::create($warehouseData);

            if ($request->has('is_default') && $request->is_default) {
                Warehouse::where('id', '!=', $warehouse->id)
                    ->where('company_id', $warehouse->company_id)
                    ->update(['is_default' => false]);
            }

            DB::commit();

            if ($request->ajax()) {
                session()->flash('success', __('messages.recordSaved'));

                return response()->json(Reply::redirect(route('warehouse.index')));
            }

            return redirect()->route('warehouse.index')->with('success', __('messages.recordSaved'));
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->handleWarehouseThrowable($request, 'Warehouse store', $e, $request->except(['_token']));
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_warehouses');
        abort_if(! in_array($viewPermission, ['all', 'added', 'owned', 'both'], true), 403, __('warehouse::app.err_permission_denied'));

        $this->warehouse = Warehouse::findOrFail($id);
        $this->pageTitle = 'warehouse::app.warehouse';
        $this->pageIcon = 'ti-layout';

        return view('warehouse::show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $editPermission = user()->permission('edit_warehouses');
        abort_if(! in_array($editPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $this->warehouse = Warehouse::findOrFail($id);
        $this->pageTitle = 'warehouse::app.editTitle';
        $this->pageIcon = 'ti-layout';

        return view('warehouse::edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $editPermission = user()->permission('edit_warehouses');
        abort_if(! in_array($editPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code,' . $id,
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            DB::beginTransaction();

            $warehouse = Warehouse::findOrFail($id);

            $warehouse->update([
                'name' => $request->name,
                'code' => $request->code,
                'address' => $request->address,
                'description' => $request->description,
                'status' => $request->status,
                'is_default' => $request->has('is_default'),
            ]);

            if ($request->has('is_default') && $request->is_default) {
                // Set other warehouses to not default
                Warehouse::where('id', '!=', $warehouse->id)
                    ->where('company_id', $warehouse->company_id)
                    ->update(['is_default' => false]);
            }

            DB::commit();

            return redirect()->route('warehouse.index')->with('success', __('warehouse::app.success_warehouse_updated'));
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->handleWarehouseThrowable($request, 'Warehouse update', $e, $request->except(['_token']));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_warehouses');
        abort_if(! in_array($deletePermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        try {
            $warehouse = Warehouse::findOrFail($id);

            if ($warehouse->batches()->where('quantity', '>', 0)->exists()) {
                return back()->with('error', __('warehouse::app.err_delete_warehouse_has_batch_stock'));
            }

            if ($warehouse->stocks()->where('quantity', '>', 0)->exists()) {
                return back()->with('error', __('warehouse::app.err_delete_warehouse_has_stock'));
            }

            $hasMovements = StockMovement::query()
                ->where(function ($q) use ($id) {
                    $q->where('warehouse_from_id', $id)->orWhere('warehouse_to_id', $id);
                })
                ->exists();

            if ($hasMovements) {
                return back()->with('error', __('warehouse::app.err_delete_warehouse_has_movements'));
            }

            if ($warehouse->reservations()->where('status', 'active')->where('reserved_quantity', '>', 0)->exists()) {
                return back()->with('error', __('warehouse::app.err_delete_warehouse_has_reservations'));
            }

            $warehouse->delete();

            return redirect()->route('warehouse.index')->with('success', __('warehouse::app.success_warehouse_deleted'));
        } catch (\Throwable $e) {
            return $this->handleWarehouseThrowable(request(), 'Warehouse destroy', $e, ['warehouse_id' => $id]);
        }
    }
}
