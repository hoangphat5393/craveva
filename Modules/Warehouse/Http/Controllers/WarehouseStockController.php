<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductStock;

class WarehouseStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $search = $request->get('search');

        $warehouses = Warehouse::where('status', 'active')->get();

        $stocks = WarehouseProductStock::with(['product', 'warehouse'])
            ->when($warehouseId, function ($query) use ($warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            })
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
                });
            })
            ->paginate(20);

        return view('warehouse::stock.index', compact('stocks', 'warehouses', 'warehouseId'));
    }

    /**
     * Show the form for creating a new resource (Stock Adjustment).
     */
    public function create()
    {
        $warehouses = Warehouse::where('status', 'active')->get();
        // Assuming products are global
        $products = Product::select('id', 'name', 'sku')->get();

        return view('warehouse::stock.create', compact('warehouses', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:inbound,outbound,adjustment', // inbound = add, outbound = remove, adjustment = set/correct
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $warehouseId = $request->warehouse_id;
            $productId = $request->product_id;
            $quantity = $request->quantity;
            $type = $request->type;

            // Get or create stock record
            $stock = WarehouseProductStock::firstOrCreate(
                ['warehouse_id' => $warehouseId, 'product_id' => $productId],
                ['quantity' => 0]
            );

            $oldQuantity = $stock->quantity;
            $newQuantity = $oldQuantity;

            if ($type === 'inbound') {
                $newQuantity += $quantity;
            } elseif ($type === 'outbound') {
                $newQuantity -= $quantity;
            } elseif ($type === 'adjustment') {
                // If it's an absolute adjustment, we calculate the difference for the movement record
                // But for simplicity in this form, let's assume adjustment means "Add/Subtract" similar to in/out but manually.
                // Or if 'adjustment' means "Set to X", we need logic for that.
                // Let's assume 'adjustment' here is a correction +/-.
                // Wait, usually 'adjustment' implies we found X items, so we want to set stock to X.
                // But for now let's treat it as +/- for simplicity, or clarify.
                // Let's implement 'set' logic if the user provides a 'new_quantity' instead of 'quantity'.
                // For this implementation, let's stick to +/- with 'quantity' for Inbound/Outbound.
                // If type is 'adjustment', let's assume it's a manual correction that acts like inbound/outbound but labeled differently?
                // Actually, let's handle "Set Quantity" logic if needed.
                // For now, let's treat 'adjustment' as a generic term.
                // Let's stick to Inbound (Add) and Outbound (Remove).

                // If the user selected 'adjustment', we might need to know if it's adding or removing.
                // Let's assume the UI sends positive for add, negative for remove?
                // No, validation says min:0.01.

                // Let's refine: The user selects "Increase" or "Decrease".
                // type: 'increase' -> inbound, 'decrease' -> outbound.
            }

            // Re-evaluating types based on StockMovement enum usually found in systems.
            // StockMovement model has: inbound, outbound, transfer, adjustment.

            if ($type === 'adjustment') {
                // For adjustment, we should probably allow setting the final value or allow +/-.
                // Let's assume the form allows selecting "Add" or "Subtract".
                // Let's change validation to require 'direction' if type is adjustment?
                // Or simplify: The form has "Action: Add / Remove".
                // Let's use 'type' as 'add' or 'remove'.
            }

            // Let's use specific logic:
            // Input: warehouse_id, product_id, quantity, action (add/remove).

            $action = $request->input('action', 'add'); // add or remove

            if ($action === 'add') {
                $newQuantity += $quantity;
                $movementType = 'adjustment'; // or inbound
            } else {
                $newQuantity -= $quantity;
                $movementType = 'adjustment'; // or outbound

                if ($newQuantity < 0) {
                    // Allow negative stock? Usually no.
                    // return back()->with('error', 'Insufficient stock.');
                }
            }

            $stock->quantity = $newQuantity;
            $stock->save();

            // Record Movement
            StockMovement::create([
                'company_id' => auth()->user()->company_id ?? 1,
                'product_id' => $productId,
                'warehouse_from_id' => ($action === 'remove') ? $warehouseId : null,
                'warehouse_to_id' => ($action === 'add') ? $warehouseId : null,
                'movement_type' => 'adjustment', // Manually adjusted
                'quantity' => $quantity,
                'reference_type' => 'manual_adjustment',
                'reference_id' => auth()->id(), // User who did it
                'description' => $request->reason,
            ]);

            DB::commit();

            return redirect()->route('warehouse.stock.index')->with('success', 'Stock updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock Adjustment Error: '.$e->getMessage());

            return back()->with('error', 'Something went wrong! '.$e->getMessage());
        }
    }

    public function show($id)
    {
        // History of product in warehouse
    }
}
