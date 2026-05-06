<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockTransfer;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse'])
            ->orderBy('created_at', 'desc');

        // 🔹 Apply date filter if provided
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $transfers = $query->get();

        return view('admin_panel.warehouses.stock_transfers.index', compact('transfers'));
    }




    public function create()
    {
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('admin_panel.warehouses.stock_transfers.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        try {
            // 🔥 FIX: Only pair products and quantities that both have values (avoid array misalignment)
            $productIds = $request->product_id ?? [];
            $quantities = $request->quantity ?? [];
            
            $pairedData = [];
            foreach ($productIds as $index => $productId) {
                if (!empty($productId) && isset($quantities[$index]) && $quantities[$index] > 0) {
                    $pairedData[] = [
                        'product_id' => $productId,
                        'quantity'   => $quantities[$index]
                    ];
                }
            }

            if (empty($pairedData)) {
                throw new \Exception("No valid products to transfer");
            }

            $request->validate([
                'transfer_to'  => 'required|in:shop,warehouse',
            ]);

            $fromWarehouse = $request->from_warehouse_id;
            $transferTo    = $request->transfer_to;
            $toWarehouse   = $request->to_warehouse_id;
            $remarks       = $request->remarks;
            $branchId      = auth()->id(); // Get current user's branch_id

            // Extract paired products and quantities
            $products   = array_column($pairedData, 'product_id');
            $quantities = array_column($pairedData, 'quantity');

            foreach ($pairedData as $data) {
                $productId = $data['product_id'];
                $qty = (float) $data['quantity'];

                if ($qty <= 0) {
                    throw new \Exception("Invalid quantity for product ID {$productId}");
                }

                // ---------- SOURCE - Decrement from source ----------
                if ($fromWarehouse !== 'Shop') {
                    // FROM WAREHOUSE
                    $sourceStock = \App\Models\WarehouseStock::firstOrCreate(
                        [
                            'warehouse_id' => $fromWarehouse,
                            'product_id'   => $productId
                        ],
                        [
                            'quantity' => 0,
                            'price'    => 0
                        ]
                    );

                    $sourceStock->quantity -= $qty;
                    $sourceStock->save();
                } else {
                    // FROM SHOP - Include branch_id in search
                    $sourceStock = \App\Models\Stock::firstOrCreate(
                        [
                            'product_id' => $productId,
                            'branch_id'  => $branchId
                        ],
                        ['qty' => 0]
                    );

                    $sourceStock->qty -= $qty;
                    $sourceStock->save();
                }

                // ---------- DESTINATION - Increment to destination ----------
                if ($transferTo === 'warehouse' && $toWarehouse) {
                    // TO WAREHOUSE - Get price from source if available
                    $price = 0;
                    if ($fromWarehouse !== 'Shop') {
                        // Source was warehouse, get its price
                        $price = $sourceStock->price ?? 0;
                    } else {
                        // Source was shop, get product's price
                        $product = \App\Models\Product::find($productId);
                        $price = $product->price ?? 0;
                    }

                    $destStock = \App\Models\WarehouseStock::firstOrCreate(
                        [
                            'warehouse_id' => $toWarehouse,
                            'product_id'   => $productId
                        ],
                        [
                            'quantity' => 0,
                            'price'    => $price
                        ]
                    );

                    $destStock->quantity += $qty;
                    $destStock->save();

                } elseif ($transferTo === 'shop') {
                    // TO SHOP - Include branch_id for proper tracking
                    $shopStock = \App\Models\Stock::firstOrCreate(
                        [
                            'product_id' => $productId,
                            'branch_id'  => $branchId
                        ],
                        ['qty' => 0]
                    );

                    $shopStock->qty += $qty;
                    $shopStock->save();
                }
            }

            $transfer = \App\Models\StockTransfer::create([
                'from_warehouse_id' => $fromWarehouse === 'Shop' ? 0 : $fromWarehouse,
                'transfer_to'       => $transferTo,
                'to_warehouse_id'   => $transferTo === 'warehouse' ? $toWarehouse : null,
                'product_id'        => json_encode($products),
                'quantity'          => json_encode($quantities),
                'remarks'           => $remarks,
            ]);

            return redirect()
                ->route('recipt.warehouse', $transfer->id)
                ->with('success', 'Stock transferred successfully.');
        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }






    public function destroy(StockTransfer $stockTransfer)
    {
        // Optional: reverse the transfer if needed
        return back()->with('error', 'Deleting transfers not allowed.');
    }
    public function getStockQuantity(Request $request)
    {
        $productId   = $request->product_id;
        $warehouseId = $request->warehouse_id; // may be null or "Shop"

        if (!$productId) {
            return response()->json(['quantity' => 0, 'error' => 'Product ID required'], 400);
        }

        // WAREHOUSE CASE - if warehouseId is provided and not "Shop"
        if (!empty($warehouseId) && $warehouseId !== 'Shop') {
            $stock = \App\Models\WarehouseStock::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->first();

            return response()->json([
                'quantity' => $stock ? (float)$stock->quantity : 0,
                'source'   => 'warehouse'
            ]);
        }

        // SHOP CASE - Include branch_id for proper filtering
        $branchId = auth()->id();
        $stock = \App\Models\Stock::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();

        return response()->json([
            'quantity' => $stock ? (float)$stock->qty : 0,
            'source'   => 'shop'
        ]);
    }




    public function receipt($id)
    {
        $transfer = StockTransfer::with(['fromWarehouse', 'toWarehouse'])
            ->findOrFail($id);
        return view('admin_panel.warehouses.stock_transfers.receipt', compact('transfer'));
    }
}
