<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Services\StockMovementService;

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
            $companyId = auth()->user()->company_id ?? null;

            app(StockMovementService::class)->recordTransfer([
                'company_id' => $companyId,
                'warehouse_from_id' => (int) $request->warehouse_from_id,
                'warehouse_to_id' => (int) $request->warehouse_to_id,
                'product_id' => (int) $request->product_id,
                'quantity' => (float) $request->quantity,
                'batch_number' => null,
                'expiry_date' => null,
                'reference_type' => 'manual_transfer',
                'reference_id' => auth()->id(),
            ]);

            return redirect()->route('warehouse.stock.index')->with('success', 'Stock transferred successfully.');
        } catch (\Throwable $e) {
            Log::error('Stock Transfer Error: ' . $e->getMessage());

            return back()->with('error', 'Something went wrong! ' . $e->getMessage());
        }
    }
}
