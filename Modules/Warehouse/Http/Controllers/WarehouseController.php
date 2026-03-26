<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Models\StockMovement;
use App\Traits\ImportExcel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Imports\WarehouseImport;
use Modules\Warehouse\Jobs\ImportWarehouseChunkJob;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Http\Controllers\Concerns\HandlesWarehouseErrors;

class WarehouseController extends AccountBaseController
{
    use HandlesWarehouseErrors, ImportExcel;

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

        $allowedSortColumns = ['id', 'name', 'code', 'address', 'status', 'is_default'];
        $sortBy = $request->get('sort_by');
        $sortDir = strtolower((string) $request->get('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $hasColumnSort = in_array($sortBy, $allowedSortColumns, true);

        $perPage = (int) $request->get('per_page', 25);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

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

        $this->pageTitle = 'warehouse::app.warehouses';
        $this->pageIcon = 'ti-layout';
        $this->warehouseSortBy = $hasColumnSort ? $sortBy : null;
        $this->warehouseSortDir = $sortDir;
        $this->warehousePerPage = $perPage;
        $this->warehouses = $query->paginate($perPage)->withQueryString();

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
     * Show import warehouse screen.
     */
    public function importWarehouse()
    {
        $addPermission = user()->permission('add_warehouses');
        abort_if(! in_array($addPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $this->pageTitle = __('app.importExcel') . ' ' . __('warehouse::app.warehouse');
        $this->view = 'warehouse::ajax.import';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return response()->json(Reply::dataOnly([
                'status' => 'success',
                'html' => $html,
                'title' => $this->pageTitle,
            ]));
        }

        return view('warehouse::import', $this->data);
    }

    /**
     * Handle uploaded import file and show mapping.
     */
    public function importStore(ImportRequest $request)
    {
        $addPermission = user()->permission('add_warehouses');
        abort_if(! in_array($addPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $result = $this->importFileProcess($request, WarehouseImport::class);

        if ($result === 'abort') {
            return Reply::error(__('messages.abortAction'));
        }

        $this->data['originalImportFilename'] = $request->import_file->getClientOriginalName();
        $view = view('warehouse::ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    /**
     * Dispatch import jobs in chunks.
     */
    public function importProcess(ImportProcessRequest $request)
    {
        $addPermission = user()->permission('add_warehouses');
        abort_if(! in_array($addPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $chunkSize = $request->filled('chunk_size') ? (int) $request->chunk_size : 100;
        $batch = $this->importJobProcessChunked($request, WarehouseImport::class, ImportWarehouseChunkJob::class, $chunkSize);
        $batchId = data_get($batch, 'id');
        if ($batchId) {
            Cache::put('import_metrics_' . $batchId, [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'skipped_missing_required' => 0,
                'invalid_status' => 0,
            ], now()->addHours(12));
        }

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
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
     * Quick change warehouse status from index table.
     */
    public function changeStatus(Request $request): JsonResponse
    {
        $editPermission = user()->permission('edit_warehouses');
        abort_if(! in_array($editPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $validated = $request->validate([
            'warehouseId' => 'required|integer|exists:warehouses,id',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $warehouse = Warehouse::findOrFail((int) $validated['warehouseId']);
            $warehouse->status = $validated['status'];
            $warehouse->save();

            return response()->json(Reply::success(__('messages.updateSuccess')));
        } catch (\Throwable $e) {
            $response = $this->handleWarehouseThrowable($request, 'Warehouse changeStatus', $e, $validated);

            if ($response instanceof JsonResponse) {
                return $response;
            }

            return response()->json(Reply::error(__('warehouse::app.err_unexpected_try_again')), 422);
        }
    }

    public function applyQuickAction(Request $request): JsonResponse
    {
        $request->validate([
            'action_type' => 'required|in:change-status,delete',
            'row_ids' => 'required|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $rowIds = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) $request->row_ids)))));
        if ($rowIds === []) {
            return response()->json(Reply::error(__('messages.selectItem')), 422);
        }

        if ($request->action_type === 'change-status') {
            $editPermission = user()->permission('edit_warehouses');
            abort_if(! in_array($editPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

            $status = $request->status;
            if (! in_array($status, ['active', 'inactive'], true)) {
                return response()->json(Reply::error(__('messages.invalidData')), 422);
            }

            Warehouse::whereIn('id', $rowIds)->update(['status' => $status]);

            return response()->json(Reply::success(__('messages.updateSuccess')));
        }

        $deletePermission = user()->permission('delete_warehouses');
        abort_if(! in_array($deletePermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $warehouses = Warehouse::whereIn('id', $rowIds)->get();
        if ($warehouses->isEmpty()) {
            return response()->json(Reply::error(__('messages.invalidData')), 422);
        }

        foreach ($warehouses as $warehouse) {
            $blockMessage = $this->deleteBlockedMessage($warehouse);
            if ($blockMessage !== null) {
                return response()->json(Reply::error($warehouse->name . ': ' . $blockMessage), 422);
            }
        }

        Warehouse::whereIn('id', $rowIds)->delete();

        return response()->json(Reply::success(__('messages.deleteSuccess')));
    }

    private function deleteBlockedMessage(Warehouse $warehouse): ?string
    {
        if ($warehouse->batches()->where('quantity', '>', 0)->exists()) {
            return __('warehouse::app.err_delete_warehouse_has_batch_stock');
        }

        if ($warehouse->stocks()->where('quantity', '>', 0)->exists()) {
            return __('warehouse::app.err_delete_warehouse_has_stock');
        }

        $hasMovements = StockMovement::query()
            ->where(function ($q) use ($warehouse) {
                $q->where('warehouse_from_id', $warehouse->id)
                    ->orWhere('warehouse_to_id', $warehouse->id);
            })
            ->exists();

        if ($hasMovements) {
            return __('warehouse::app.err_delete_warehouse_has_movements');
        }

        if ($warehouse->reservations()->where('status', 'active')->where('reserved_quantity', '>', 0)->exists()) {
            return __('warehouse::app.err_delete_warehouse_has_reservations');
        }

        return null;
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

            $blockMessage = $this->deleteBlockedMessage($warehouse);
            if ($blockMessage !== null) {
                return back()->with('error', $blockMessage);
            }

            $warehouse->delete();

            return redirect()->route('warehouse.index')->with('success', __('warehouse::app.success_warehouse_deleted'));
        } catch (\Throwable $e) {
            return $this->handleWarehouseThrowable(request(), 'Warehouse destroy', $e, ['warehouse_id' => $id]);
        }
    }
}
