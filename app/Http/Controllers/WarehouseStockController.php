<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class WarehouseStockController extends Controller
{
    public function index(Request $request)
    {
        $branchId  = auth()->id();
        $type      = $request->stock_type ?? 'all';

        /* ======================================================
       1️⃣ PRODUCTS (BASE = 3754)
    ====================================================== */
        $products = Product::with('unit','brand')->get();
        
        /* ======================================================
       2️⃣ SHOP STOCK (CURRENT - NO DATE FILTER)
    ====================================================== */
        $shopSums = DB::table('stocks')
            ->select('product_id', DB::raw('SUM(qty) as shop_qty'))
            ->where('branch_id', $branchId)
            ->groupBy('product_id')
            ->pluck('shop_qty', 'product_id')
            ->toArray();

        /* ======================================================
       3️⃣ WAREHOUSE STOCK (CURRENT - NO DATE FILTER)
    ====================================================== */
        $warehouseRows = WarehouseStock::with('warehouse')->get();

        $warehouseQtyByProduct = [];
        $warehouseByProduct    = [];

        foreach ($warehouseRows as $ws) {
            $pid = $ws->product_id;

            if (!isset($warehouseQtyByProduct[$pid])) {
                $warehouseQtyByProduct[$pid] = 0;
            }

            $warehouseQtyByProduct[$pid] += (float) $ws->quantity;

            if (!isset($warehouseByProduct[$pid])) {
                $warehouseByProduct[$pid] = $ws->warehouse;
            }
        }

        /* ======================================================
       4️⃣ FINAL STOCK COLLECTION
    ====================================================== */
        $stocks = collect();

        foreach ($products as $product) {

            $pid = $product->id;

            $shopQty      = $shopSums[$pid] ?? 0;
            $warehouseQty = $warehouseQtyByProduct[$pid] ?? 0;

            // 🔴 FILTER LOGIC
            if ($type === 'shop' && $shopQty == 0) {
                continue;
            }

            if ($type === 'warehouse' && $warehouseQty == 0) {
                continue;
            }

            $row = new WarehouseStock();

            $row->product_id = $pid;
            $row->product    = $product;

            $row->warehouse      = $warehouseByProduct[$pid] ?? null;
            $row->warehouse_id   = $warehouseByProduct[$pid]->id ?? null;

            $row->shop_stock      = $shopQty;
            $row->warehouse_stock = $warehouseQty;
            $row->total_stock     = $shopQty + $warehouseQty;

            $row->quantity   = $warehouseQty;
            $row->remarks    = ($warehouseQty == 0 && $shopQty > 0) ? 'Shop Only' : null;
            $row->created_at = now();

            $stocks->push($row);
        }

        return view('admin_panel.warehouses.warehouse_stocks.index',
            compact('stocks')
        );
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('admin_panel.warehouses.warehouse_stocks.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required|integer|min:0'
        ]);

        WarehouseStock::create($request->all());
        return redirect()->route('warehouse_stocks.index')->with('success', 'Stock added successfully.');
    }

    public function edit(WarehouseStock $warehouseStock)
    {
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('admin_panel.warehouses.warehouse_stocks.edit', compact('warehouseStock', 'warehouses', 'products'));
    }

    public function update(Request $request, WarehouseStock $warehouseStock)
    {
        $request->validate([
            'warehouse_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required|integer|min:0'
        ]);

        $warehouseStock->update($request->all());
        return redirect()->route('warehouse_stocks.index')->with('success', 'Stock updated successfully.');
    }

    public function destroy(WarehouseStock $warehouseStock)
    {
        $warehouseStock->delete();
        return redirect()->route('warehouse_stocks.index')->with('success', 'Stock deleted successfully.');
    }
}
