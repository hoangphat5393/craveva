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

class WarehouseTransferController extends Controller
{
    public function create()
    {
        $warehouses = Warehouse::where('status', 'active')->get();
        $products = Product::select('id', 'name', 'sku')->get();

        return view('warehouse::transfer.create', compact('warehouses', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'warehouse_from_id' => 'required|exists:warehouses,id|different:warehouse_to_id',
            'warehouse_to_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $fromId = $request->warehouse_from_id;
            $toId = $request->warehouse_to_id;
            $productId = $request->product_id;
            $quantity = $request->quantity;

            // Check Source Stock
            $sourceStock = WarehouseProductStock::where('warehouse_id', $fromId)
                ->where('product_id', $productId)
                ->first();

            if (!$sourceStock || $sourceStock->quantity < $quantity) {
                return back()->with('error', 'Insufficient stock in source warehouse.');
            }

            // Decrement Source
            $sourceStock->quantity -= $quantity;
            $sourceStock->save();

            // Increment Destination
            $destStock = WarehouseProductStock::firstOrCreate(
                ['warehouse_id' => $toId, 'product_id' => $productId],
                ['quantity' => 0]
            );
            $destStock->quantity += $quantity;
            $destStock->save();

            // Record Movement
            StockMovement::create([
                'company_id' => auth()->user()->company_id ?? 1,
                'product_id' => $productId,
                'warehouse_from_id' => $fromId,
                'warehouse_to_id' => $toId,
                'movement_type' => 'transfer',
                'quantity' => $quantity,
                'reference_type' => 'manual_transfer',
                'reference_id' => auth()->id(),
                'description' => $request->description,
            ]);

            DB::commit();

            return redirect()->route('warehouse.stock.index')->with('success', 'Stock transferred successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock Transfer Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong! ' . $e->getMessage());
        }
    }
}
