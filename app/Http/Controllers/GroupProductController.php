<?php

namespace App\Http\Controllers;

use App\Models\GroupProduct;
use App\Models\GroupProductComponent;
use App\Models\Product;
use App\Models\Stock;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GroupProductController extends Controller
{
    /**
     * Calculate product's available stock from all transactions
     */
    private function calculateAvailableStock($productId, $itemCode)
    {
        $today = date('Y-m-d');
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';
        
        $product = Product::find($productId);
        if (!$product) return 0;

        $purchaseData = DB::table('purchase_items')
            ->where('product_id', $productId)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->select(DB::raw('COALESCE(SUM(qty),0) as total_qty'))
            ->first();

        $inwardData = DB::table('inward_gatepasses')
            ->join('inward_gatepass_items', 'inward_gatepasses.id', '=', 'inward_gatepass_items.inward_gatepass_id')
            ->where('inward_gatepass_items.product_id', $productId)
            ->whereBetween('inward_gatepasses.gatepass_date', [$startDate, $endDate])
            ->select(DB::raw('COALESCE(SUM(inward_gatepass_items.qty),0) as total_inward_qty'))
            ->first();

        $sales = DB::table('sales')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->select('product_code', 'qty')
            ->whereNotNull('product_code')
            ->get();

        $sold = 0.0;
        foreach ($sales as $s) {
            $codes = array_map('trim', explode(',', $s->product_code));
            $qtys  = array_map('trim', explode(',', $s->qty));
            foreach ($codes as $idx => $code) {
                if ($code === $itemCode && isset($qtys[$idx])) {
                    $sold += floatval($qtys[$idx]);
                }
            }
        }

        $producedData = DB::table('group_products')
            ->where('product_id', $productId)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->select(DB::raw('COALESCE(SUM(quantity_produced),0) as total_produced'))
            ->first();
        $producedQty = (float) $producedData->total_produced;

        $usedData = DB::table('group_product_components')
            ->where('product_id', $productId)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->select(DB::raw('COALESCE(SUM(quantity_used),0) as total_used'))
            ->first();
        $usedQty = (float) $usedData->total_used;

        $purchaseReturnData = DB::table('purchase_return_items')
            ->join('purchase_returns', 'purchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
            ->where('purchase_return_items.product_id', $productId)
            ->whereBetween('purchase_returns.created_at', [$startDateTime, $endDateTime])
            ->select(DB::raw('COALESCE(SUM(purchase_return_items.qty),0) as total_return_qty'))
            ->first();

        $saleReturnData = DB::table('sales_returns')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->select('product_code', 'qty')
            ->get();

        $saleReturnQty = 0;
        foreach ($saleReturnData as $sr) {
            $codes = array_map('trim', explode(',', $sr->product_code));
            $qtys  = array_map('trim', explode(',', $sr->qty));
            foreach ($codes as $idx => $code) {
                if ($code === $itemCode && isset($qtys[$idx])) {
                    $saleReturnQty += floatval($qtys[$idx]);
                }
            }
        }

        $balance = 
            ($product->initial_stock ?? 0)
            + ($inwardData->total_inward_qty ?? 0)
            + ($purchaseData->total_qty ?? 0)
            + $producedQty
            - ($purchaseReturnData->total_return_qty ?? 0)
            - ($sold ?? 0)
            - $usedQty
            + ($saleReturnQty ?? 0);

        return max(0, $balance);
    }

    public function index()
    {
        $groupProducts = GroupProduct::with('components.product', 'creator')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin_panel.group_products.index', compact('groupProducts'));
    }

    public function create()
    {
        // Get all products
        $products = Product::orderBy('item_name')->get();

        $products = $products->map(function($product) {
            $calculated = $this->calculateAvailableStock($product->id, $product->item_code);
            $product->calculated_stock = $calculated;
            return $product;
        })
        ->filter(function($product) {
            // Only include products with calculated stock > 0
            return $product->calculated_stock > 0;
        })
        ->values();
        
        return view('admin_panel.group_products.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'quantity_produced' => 'required|integer|min:1',
            'sale_price' => 'required|numeric|min:0',
            'components' => 'required|array|min:1',
            'components.*.product_id' => 'required|exists:products,id',
            'components.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $totalCost = 0;

            // Validate stock availability first - using CALCULATED stock from all transactions
            foreach ($request->components as $component) {
                $product = Product::findOrFail($component['product_id']);
                $calculatedStock = $this->calculateAvailableStock($product->id, $product->item_code);
                
                if ($calculatedStock < $component['quantity']) {
                    return back()->withErrors([
                        'stock_error' => "Insufficient stock for {$product->item_name}. Available: {$calculatedStock}, Requested: {$component['quantity']}"
                    ])->withInput();
                }
            }

            // Calculate total cost
            foreach ($request->components as $component) {
                $product = Product::findOrFail($component['product_id']);
                // Get latest purchase price from purchase_items
                $latestPurchase = PurchaseItem::where('product_id', $product->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                $unitCost = $latestPurchase ? $latestPurchase->price : ($product->price ?? 0);
                $totalCost += $unitCost * $component['quantity'];
            }

            $costPerUnit = $request->quantity_produced > 0 
                ? $totalCost / $request->quantity_produced 
                : 0;

            // Create a Product entry for this group product so it can be sold
            $newProduct = Product::create([
                'creater_id' => Auth::id(),
                'category_id' => null,
                'sub_category_id' => null,
                'item_code' => 'GP-' . time(),
                'item_name' => $request->product_name,
                'price' => $request->sale_price,
                'alert_quantity' => 0,
            ]);

            // Create Stock entry for the product
            $defaultWarehouse = \DB::table('warehouses')->first();
            $defaultBranch = \DB::table('branches')->first();
            
            if ($defaultWarehouse && $defaultBranch) {
                Stock::create([
                    'branch_id' => $defaultBranch->id,
                    'warehouse_id' => $defaultWarehouse->id,
                    'product_id' => $newProduct->id,
                    'qty' => $request->quantity_produced,
                    'reserved_qty' => 0,
                ]);
            }

            // Create group product
            $groupProduct = GroupProduct::create([
                'product_id' => $newProduct->id,
                'product_name' => $request->product_name,
                'description' => $request->description,
                'quantity_produced' => $request->quantity_produced,
                'total_cost' => $totalCost,
                'cost_per_unit' => $costPerUnit,
                'sale_price' => $request->sale_price,
                'current_stock' => $request->quantity_produced,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            // Process components and deduct stock
            foreach ($request->components as $component) {
                $product = Product::findOrFail($component['product_id']);
                // Get latest purchase price from purchase_items
                $latestPurchase = PurchaseItem::where('product_id', $product->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                $unitCost = $latestPurchase ? $latestPurchase->price : ($product->price ?? 0);
                $componentTotalCost = $unitCost * $component['quantity'];

                // Create component record
                GroupProductComponent::create([
                    'group_product_id' => $groupProduct->id,
                    'product_id' => $product->id,
                    'quantity_used' => $component['quantity'],
                    'unit_cost' => $unitCost,
                    'total_cost' => $componentTotalCost,
                ]);

                // Deduct stock from source product (shop first, then warehouse)
                $quantityToDeduct = $component['quantity'];
                
                // Deduct from shop stock first
                $shopStock = Stock::where('product_id', $product->id)->first();
                if ($shopStock && $shopStock->qty > 0) {
                    $shopDeduct = min($shopStock->qty, $quantityToDeduct);
                    $shopStock->decrement('qty', $shopDeduct);
                    $quantityToDeduct -= $shopDeduct;
                }
                
                // If still need to deduct, deduct from warehouse stock
                if ($quantityToDeduct > 0) {
                    $warehouseStocks = DB::table('warehouse_stocks')
                        ->where('product_id', $product->id)
                        ->orderBy('id')
                        ->get();
                    
                    foreach ($warehouseStocks as $ws) {
                        if ($quantityToDeduct <= 0) break;
                        
                        $warehouseDeduct = min($ws->quantity, $quantityToDeduct);
                        DB::table('warehouse_stocks')
                            ->where('id', $ws->id)
                            ->decrement('quantity', $warehouseDeduct);
                        
                        $quantityToDeduct -= $warehouseDeduct;
                    }
                }
            }

            DB::commit();

            return redirect()->route('group-products.index')
                ->with('success', 'Group Product created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create group product: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show($id)
    {
        $groupProduct = GroupProduct::with('components.product', 'creator')->findOrFail($id);
        return view('admin_panel.group_products.show', compact('groupProduct'));
    }

    public function toggleStatus($id)
    {
        $groupProduct = GroupProduct::findOrFail($id);
        $groupProduct->update(['is_active' => !$groupProduct->is_active]);

        return back()->with('success', 'Status updated successfully!');
    }

    public function destroy($id)
    {
        $groupProduct = GroupProduct::findOrFail($id);
        
        if ($groupProduct->current_stock > 0) {
            return back()->withErrors(['error' => 'Cannot delete group product with remaining stock.']);
        }

        $groupProduct->delete();
        return redirect()->route('group-products.index')
            ->with('success', 'Group Product deleted successfully!');
    }

    // Get product stock quantity for AJAX
    public function getProductStock($id)
    {
        $stock = Stock::where('product_id', $id)->first();
        return response()->json([
            'quantity' => $stock ? $stock->qty : 0
        ]);
    }
}
