<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Warehouse\Entities\Warehouse;

class WarehouseController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_403(! in_array('warehouse', user_modules()));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Warehouse::query()->orderByDesc('id');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = '%'.$request->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('code', 'like', $term)
                    ->orWhere('address', 'like', $term);
            });
        }

        $this->pageTitle = 'warehouse::app.allWarehouses';
        $this->pageIcon = 'ti-layout';
        $this->warehouses = $query->paginate(10)->withQueryString();

        return view('warehouse::index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->pageTitle = 'warehouse::app.createTitle';
        $this->pageIcon = 'ti-layout';

        return view('warehouse::create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            DB::beginTransaction();

            $warehouse = Warehouse::create([
                'company_id' => auth()->user()->company_id ?? 1, // Fallback if no company context yet
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

            return redirect()->route('warehouse.index')->with('success', 'Warehouse created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse Create Error: '.$e->getMessage());

            return back()->with('error', 'Something went wrong! '.$e->getMessage());
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
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
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code,'.$id,
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

            return redirect()->route('warehouse.index')->with('success', 'Warehouse updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse Update Error: '.$e->getMessage());

            return back()->with('error', 'Something went wrong! '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);

            // Check if warehouse has stock or transactions before deleting
            if ($warehouse->stocks()->where('quantity', '>', 0)->exists()) {
                return back()->with('error', 'Cannot delete warehouse with existing stock.');
            }

            $warehouse->delete();

            return redirect()->route('warehouse.index')->with('success', 'Warehouse deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Warehouse Delete Error: '.$e->getMessage());

            return back()->with('error', 'Something went wrong! '.$e->getMessage());
        }
    }
}
