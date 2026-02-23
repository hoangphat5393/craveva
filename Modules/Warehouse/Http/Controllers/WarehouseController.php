<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Warehouse\Entities\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::orderBy('id', 'desc')->paginate(10);
        return view('warehouse::index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('warehouse::create');
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
            Log::error('Warehouse Create Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong! ' . $e->getMessage());
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        return view('warehouse::show', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        return view('warehouse::edit', compact('warehouse'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
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

            return redirect()->route('warehouse.index')->with('success', 'Warehouse updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse Update Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong! ' . $e->getMessage());
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
            Log::error('Warehouse Delete Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong! ' . $e->getMessage());
        }
    }
}
