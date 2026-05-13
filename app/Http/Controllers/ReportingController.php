<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\CustomerPayment;
use App\Models\ExpenseVoucher;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\VendorPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ReportingController extends Controller
{
    private function buildGlobalStockMaps($startDate) {
        $boughtMap = [];
        $soldMap = [];
        
        $productsList = \App\Models\Product::all(['id', 'initial_stock']);
        foreach ($productsList as $p) {
            $boughtMap[$p->id] = (float) $p->initial_stock;
            $soldMap[$p->id] = 0;
        }
        
        $allPurchases = DB::table('purchase_items')->select('product_id', DB::raw('SUM(qty) as total'))->groupBy('product_id')->get();
        foreach ($allPurchases as $p) {
            if (!isset($boughtMap[$p->product_id])) $boughtMap[$p->product_id] = 0;
            $boughtMap[$p->product_id] += (float)$p->total;
        }
        
        $allInwards = DB::table('inward_gatepass_items')->select('product_id', DB::raw('SUM(qty) as total'))->groupBy('product_id')->get();
        foreach ($allInwards as $i) {
            if (!isset($boughtMap[$i->product_id])) $boughtMap[$i->product_id] = 0;
            $boughtMap[$i->product_id] += (float)$i->total;
        }

        $allPReturns = DB::table('purchase_return_items')->select('product_id', DB::raw('SUM(qty) as total'))->groupBy('product_id')->get();
        foreach ($allPReturns as $pr) {
            if (isset($boughtMap[$pr->product_id])) $boughtMap[$pr->product_id] -= (float)$pr->total;
        }

        $salesBefore = DB::table('sales')->whereDate('created_at', '<', $startDate)->select('product', 'qty')->whereNotNull('product')->get();
        foreach ($salesBefore as $s) {
            $pIds = array_map('trim', explode(',', $s->product));
            $qtys = array_map('trim', explode(',', $s->qty));
            foreach ($pIds as $idx => $pidStr) {
                if (!$pidStr) continue;
                $qNum = (float)($qtys[$idx] ?? 0);
                if (!isset($soldMap[$pidStr])) $soldMap[$pidStr] = 0;
                $soldMap[$pidStr] += $qNum;
            }
        }
        
        $returnsBefore = DB::table('sales_returns')->whereDate('created_at', '<', $startDate)->select('product', 'qty')->get();
        foreach ($returnsBefore as $s) {
            $names = array_map('trim', explode(',', $s->product));
            $qtys = array_map('trim', explode(',', $s->qty));
            foreach ($names as $idx => $nStr) {
                if (!$nStr) continue;
                $prodModel = \App\Models\Product::where('item_name', $nStr)->first();
                if ($prodModel) {
                    $pidStr = $prodModel->id;
                    $qNum = (float)($qtys[$idx] ?? 0);
                    if (!isset($soldMap[$pidStr])) $soldMap[$pidStr] = 0;
                    $soldMap[$pidStr] -= $qNum;
                }
            }
        }

        $groupProduced = DB::table('group_products')->select('product_id', DB::raw('SUM(quantity_produced) as total'))->groupBy('product_id')->get();
        foreach ($groupProduced as $g) {
            if (!isset($boughtMap[$g->product_id])) $boughtMap[$g->product_id] = 0;
            $boughtMap[$g->product_id] += (float)$g->total;
        }
        
        $groupUsed = DB::table('group_product_components')->select('product_id', DB::raw('SUM(quantity_used) as total'))->groupBy('product_id')->get();
        foreach ($groupUsed as $g) {
            if (!isset($boughtMap[$g->product_id])) $boughtMap[$g->product_id] = 0;
            $boughtMap[$g->product_id] -= (float)$g->total;
        }

        return [$boughtMap, $soldMap];
    }

    public function reports_hub()
    {
        return view('admin_panel.reporting.index');
    }

    public function item_stock_report()
    {
        // Pagination for the table
        $products = \App\Models\Product::orderBy('item_name', 'asc')
            ->select('id', 'item_code', 'item_name')
            ->paginate(50);
            
        // All products for the dropdown filter
        $allProducts = \App\Models\Product::orderBy('item_name', 'asc')
            ->select('id', 'item_code', 'item_name')
            ->get();

        return view('admin_panel.reporting.item_stock_report', compact('products', 'allProducts'));
    }

    public function fetchItemStock(Request $request)
    {
        $productId = $request->product_id;
        $startDate = $request->start_date ?? date('Y-m-01');
        $endDate   = $request->end_date ?? date('Y-m-t');

        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime   = $endDate . ' 23:59:59';

        $rows = [];
        $grandTotalValue = 0;

        // 🔹 Base query — include only products created within range
        $productsQuery = Product::query();

        if ($productId && $productId !== 'all') {
            $productsQuery->where('id', $productId);
        }

        $perPage = 50;

        $products = $productsQuery
            ->orderBy('item_name')
            ->paginate($perPage);

        foreach ($products as $product) {
            // 🔹 Purchases in date range (for transaction view)
            $purchaseData = DB::table('purchase_items')
                ->where('product_id', $product->id)
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->select(
                    DB::raw('COALESCE(SUM(qty),0) as total_qty'),
                    DB::raw('COALESCE(SUM(line_total),0) as total_amount')
                )
                ->first();

            // 🔹 All-time average unit purchase price (no date filter) for stock value
            $allTimePurchase = DB::table('purchase_items')
                ->where('product_id', $product->id)
                ->select(
                    DB::raw('COALESCE(SUM(qty),0) as total_qty'),
                    DB::raw('COALESCE(SUM(line_total),0) as total_amount')
                )
                ->first();
            $avgUnitPrice = ($allTimePurchase->total_qty > 0)
                ? ($allTimePurchase->total_amount / $allTimePurchase->total_qty)
                : (float) ($product->wholesale_price ?? 0);

            // 🔹 Inward Quantity (from inward_gatepasses) in date range
            $inwardData = DB::table('inward_gatepasses')
                ->join('inward_gatepass_items', 'inward_gatepasses.id', '=', 'inward_gatepass_items.inward_gatepass_id')
                ->where('inward_gatepass_items.product_id', $product->id)
                ->whereBetween('inward_gatepasses.gatepass_date', [$startDate, $endDate])
                ->select(DB::raw('COALESCE(SUM(inward_gatepass_items.qty),0) as total_inward_qty'))
                ->first();

            // 🔹 Sales in date range (for transaction view)
            $sold = 0.0;
            $saleAmount = 0.0;
            $sales = DB::table('sales')
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->select('product', 'qty', 'per_total')
                ->whereNotNull('product')
                ->get();

            foreach ($sales as $s) {
                $pIds = array_map('trim', explode(',', $s->product));
                $qtys  = array_map('trim', explode(',', $s->qty));
                $totals = array_map('trim', explode(',', $s->per_total));

                foreach ($pIds as $idx => $pid) {
                    if ($pid == $product->id && isset($qtys[$idx])) {
                        $sold += floatval($qtys[$idx]);
                        $saleAmount += isset($totals[$idx]) ? floatval($totals[$idx]) : 0;
                    }
                }
            }

            // 🔹 Group Product Production (Stock Added) in date range
            $producedData = DB::table('group_products')
                ->where('product_id', $product->id)
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->select(DB::raw('COALESCE(SUM(quantity_produced),0) as total_produced'))
                ->first();
            $producedQty = (float) $producedData->total_produced;

            // 🔹 Used as Component in Group Product (Stock Deducted) in date range
            $usedData = DB::table('group_product_components')
                ->where('product_id', $product->id)
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->select(DB::raw('COALESCE(SUM(quantity_used),0) as total_used'))
                ->first();
            $usedQty = (float) $usedData->total_used;

            // 🔹 Purchase Returns in date range
            $purchaseReturnData = DB::table('purchase_return_items')
                ->join('purchase_returns', 'purchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
                ->where('purchase_return_items.product_id', $product->id)
                ->whereBetween('purchase_returns.created_at', [$startDateTime, $endDateTime])
                ->select(
                    DB::raw('COALESCE(SUM(purchase_return_items.qty),0) as total_return_qty'),
                    DB::raw('COALESCE(SUM(purchase_return_items.line_total),0) as total_return_amount')
                )
                ->first();

            // 🔹 Sale Returns in date range
            $saleReturnData = DB::table('sales_returns')
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->select('product', 'qty', 'per_total')
                ->get();

            $saleReturnQty = 0;
            $saleReturnAmount = 0;

            foreach ($saleReturnData as $sr) {
                $names = array_map('trim', explode(',', $sr->product));
                $qtys  = array_map('trim', explode(',', $sr->qty));
                $totals = array_map('trim', explode(',', $sr->per_total));

                foreach ($names as $idx => $name) {
                    if ($name === $product->item_name && isset($qtys[$idx])) {
                        $saleReturnQty += floatval($qtys[$idx]);
                        $saleReturnAmount += isset($totals[$idx]) ? floatval($totals[$idx]) : 0;
                    }
                }
            }

            // 🔹 Calculate balance from TRANSACTIONS in date range (correct logic)
            $balance = 
                ($product->initial_stock ?? 0)
                + ($inwardData->total_inward_qty ?? 0)
                + ($purchaseData->total_qty ?? 0)
                + $producedQty
                - ($purchaseReturnData->total_return_qty ?? 0)
                - ($sold ?? 0)
                - $usedQty
                + ($saleReturnQty ?? 0);

            // 🔹 Calculate stock value using calculated balance
            $stockValue = $balance * $avgUnitPrice;
            $grandTotalValue += $stockValue;

            // Check if this is a group product
            $isGroupProduct = DB::table('group_products')
                ->where('product_id', $product->id)
                ->exists();

            $rows[] = [
                'id' => $product->id,
                'date' => date('Y-m-d', strtotime($product->created_at)),
                'item_code' => $product->item_code,
                'item_name' => $product->item_name,
                'is_group_product' => $isGroupProduct,
                'initial_stock' => (float) ($product->initial_stock ?? 0),
                'inward_qty' => (float) ($inwardData->total_inward_qty ?? 0),
                'purchased' => (float) $purchaseData->total_qty,
                'purchase_return' => (float) $purchaseReturnData->total_return_qty,
                'sold' => (float) $sold,
                'sale_return' => (float) $saleReturnQty,
                'group_produced' => $producedQty,
                'group_used' => $usedQty,
                'purchase_price' => (float) ($balance * $avgUnitPrice),
                'balance' => $balance,  // 🔥 Now shows ACTUAL current stock
            ];
        }

        return response()->json([
            'data' => $rows,
            'grand_total' => $grandTotalValue,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ]
        ]);
    }

    // =====================================================================
    // 📊 FINANCIAL SUMMARY DASHBOARD
    // =====================================================================

    public function financialSummary()
    {
        return view('admin_panel.reporting.financial_summary');
    }

    public function fetchFinancialSummary()
    {
        // ── 1. Customer Dues (Total Sales - Total Received) ──────────────
        $totalSales = DB::table('sales')->sum('total_net');
        $totalSaleReturns = DB::table('sales_returns')->sum('total_net');

        // Cash collected directly on sale
        $totalCashOnSale = DB::table('sales')->selectRaw('SUM(cash + card) as total')->value('total') ?? 0;

        // Customer payments (minus adjustments)
        $custPaymentsIn   = DB::table('customer_payments')->where('adjustment_type', '!=', 'plus')->sum('amount');
        $custPaymentsPlus = DB::table('customer_payments')->where('adjustment_type', 'plus')->sum('amount');

        // Receipt Vouchers for customers
        $custRVs = DB::table('receipts_vouchers')->where('type', 'customer')->sum('total_amount');

        // Payment Vouchers for customers (debit on customer = increases due)
        $custPVs = DB::table('payment_vouchers')->where('type', 'customer')->sum('total_amount');

        $customerDues = $totalSales
            - $totalSaleReturns
            - $totalCashOnSale
            - $custPaymentsIn
            + $custPaymentsPlus
            - $custRVs
            + $custPVs;

        // Opening balance dues
        $openingCustomerDues = DB::table('customers')->sum('opening_balance');
        $customerDues += $openingCustomerDues;

        // ── 2. Vendor Dues (Total Purchases - Total Paid) ─────────────────
        $totalPurchases     = DB::table('purchases')->sum('net_amount');
        $totalPurchReturns  = DB::table('purchase_returns')->sum('net_amount');
        $totalPaidOnPurch   = DB::table('purchases')->sum('paid_amount');

        $vendorPaymentsOut  = DB::table('vendor_payments')->sum('amount');

        // Payment Vouchers for vendors (buying from them = credit)
        $vendorPVs = DB::table('payment_vouchers')->where('type', 'vendor')->sum('total_amount');
        $vendorRVs = DB::table('receipts_vouchers')->where('type', 'vendor')->sum('total_amount');

        $vendorOpeningBalance = DB::table('vendors')->sum('opening_balance');

        $vendorDues = $totalPurchases
            - $totalPurchReturns
            - $totalPaidOnPurch
            - $vendorPaymentsOut
            - $vendorRVs
            + $vendorRVs
            + $vendorOpeningBalance;

        // ── 3. Cash/Bank Balances from Chart of Accounts ─────────────────
        // Account opening_balance is kept up-to-date by SimpleFinanceController
        $accounts = DB::table('accounts')
            ->join('account_heads', 'accounts.head_id', '=', 'account_heads.id')
            ->select(
                'accounts.id',
                'accounts.title',
                'accounts.opening_balance',
                'account_heads.name as head_name'
            )
            ->where('accounts.status', 1)
            ->get();

        $accountBalances = [];
        $totalAccountBalance = 0;

        foreach ($accounts as $acc) {
            $balance = (float)($acc->opening_balance ?? 0);
            $accountBalances[] = [
                'id'      => $acc->id,
                'title'   => $acc->title,
                'head'    => $acc->head_name,
                'balance' => round($balance, 2),
            ];
            $totalAccountBalance += $balance;
        }


        // ── 4. Stock Value ────────────────────────────────────────────────
        $products = DB::table('products')->get(['id', 'initial_stock', 'wholesale_price']);
        $totalStockValue = 0;

        foreach ($products as $prod) {
            $purchData = DB::table('purchase_items')
                ->where('product_id', $prod->id)
                ->selectRaw('COALESCE(SUM(qty),0) as tq, COALESCE(SUM(line_total),0) as ta')
                ->first();

            $inwardQty = DB::table('inward_gatepass_items')
                ->where('product_id', $prod->id)
                ->sum('qty');

            $soldQty = 0;
            $salesRows = DB::table('sales')->select('product', 'qty')->whereNotNull('product')->get();
            foreach ($salesRows as $s) {
                $pIds = array_map('trim', explode(',', $s->product));
                $qtys = array_map('trim', explode(',', $s->qty));
                foreach ($pIds as $idx => $pid) {
                    if ($pid == $prod->id) $soldQty += (float)($qtys[$idx] ?? 0);
                }
            }

            $saleReturnQty = 0;
            $srRows = DB::table('sales_returns')->select('product', 'qty')->get();
            foreach ($srRows as $sr) {
                $names = array_map('trim', explode(',', $sr->product));
                $qtys  = array_map('trim', explode(',', $sr->qty));
                $prodRow = DB::table('products')->where('id', $prod->id)->value('item_name');
                foreach ($names as $idx => $n) {
                    if ($n === $prodRow) $saleReturnQty += (float)($qtys[$idx] ?? 0);
                }
            }

            $purchReturnQty = DB::table('purchase_return_items')
                ->join('purchase_returns', 'purchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
                ->where('purchase_return_items.product_id', $prod->id)
                ->sum('purchase_return_items.qty');

            $producedQty = DB::table('group_products')->where('product_id', $prod->id)->sum('quantity_produced');
            $usedQty     = DB::table('group_product_components')->where('product_id', $prod->id)->sum('quantity_used');

            $balance = (float)($prod->initial_stock ?? 0)
                + (float)($purchData->tq ?? 0)
                + (float)$inwardQty
                + (float)$producedQty
                - (float)$purchReturnQty
                - (float)$soldQty
                - (float)$usedQty
                + (float)$saleReturnQty;

            $avgPrice = ($purchData->tq > 0)
                ? ($purchData->ta / $purchData->tq)
                : (float)($prod->wholesale_price ?? 0);

            $totalStockValue += $balance * $avgPrice;
        }

        // ── 5. Monthly Sales & Expenses (last 6 months) ──────────────────
        $monthlySales = DB::table('sales')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_net) as total")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyPurchases = DB::table('purchases')
            ->selectRaw("DATE_FORMAT(purchase_date, '%Y-%m') as month, SUM(net_amount) as total")
            ->where('purchase_date', '>=', now()->subMonths(6)->format('Y-m-d'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyExpenses = DB::table('expense_vouchers')
            ->selectRaw("DATE_FORMAT(entry_date, '%Y-%m') as month, SUM(total_amount) as total")
            ->where('entry_date', '>=', now()->subMonths(6)->toDateString())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $totalExpenses = DB::table('expense_vouchers')->sum('total_amount');

        return response()->json([
            'customer_dues'       => round($customerDues, 2),
            'vendor_dues'         => round($vendorDues, 2),
            'total_account_balance' => round($totalAccountBalance, 2),
            'total_stock_value'   => round($totalStockValue, 2),
            'total_expenses'      => round($totalExpenses, 2),
            'account_balances'    => $accountBalances,
            'monthly_sales'       => $monthlySales,
            'monthly_purchases'   => $monthlyPurchases,
            'monthly_expenses'    => $monthlyExpenses,
        ]);
    }


    public function purchase_report()
    {
        return view('admin_panel.reporting.purchase_report');
    }


    public function fetchPurchaseReport(Request $request)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        /* ================= NORMAL PURCHASE ================= */
        $purchaseQuery = DB::table('purchases')
            ->leftJoin('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->leftJoin('products', 'purchase_items.product_id', '=', 'products.id')
            ->leftJoin('vendors', 'purchases.vendor_id', '=', 'vendors.id')
            ->select(
                DB::raw("'purchase' as source_type"),
                'purchases.purchase_date as purchase_date',
                'purchases.invoice_no',
                'vendors.name as vendor_name',
                'products.item_code',
                'products.item_name',
                'purchase_items.qty',
                'purchase_items.unit',
                'purchase_items.price',
                'purchase_items.item_discount',
                'purchase_items.line_total',
                'purchases.subtotal',
                'purchases.discount',
                'purchases.extra_cost',
                'purchases.net_amount',
                'purchases.paid_amount',
                'purchases.due_amount'
            );

        if ($startDate && $endDate) {
            $purchaseQuery->whereBetween('purchases.purchase_date', [$startDate, $endDate]);
        }

        /* ================= INWARD AS PURCHASE ================= */
        $inwardQuery = DB::table('inward_gatepasses')
            ->leftJoin('inward_gatepass_items', 'inward_gatepasses.id', '=', 'inward_gatepass_items.inward_gatepass_id')
            ->leftJoin('products', 'inward_gatepass_items.product_id', '=', 'products.id')
            ->leftJoin('vendors', 'inward_gatepasses.vendor_id', '=', 'vendors.id')
            ->where('inward_gatepasses.status', 'linked')
            ->where('inward_gatepasses.bill_status', 'billed')
            ->select(
                DB::raw("'inward' as source_type"),
                'inward_gatepasses.gatepass_date as purchase_date',
                'inward_gatepasses.invoice_no',
                'vendors.name as vendor_name',
                'products.item_code',
                'products.item_name',
                'inward_gatepass_items.qty',
                DB::raw('products.unit_id as unit'),   // ✅ FIX
                'products.wholesale_price as price',
                'inward_gatepass_items.discount_value as item_discount',
                DB::raw('(products.wholesale_price * inward_gatepass_items.qty) as line_total'),
                'inward_gatepasses.subtotal',
                'inward_gatepasses.discount',
                'inward_gatepasses.extra_cost',
                'inward_gatepasses.net_amount',
                'inward_gatepasses.paid_amount',
                'inward_gatepasses.due_amount'
            );


        if ($startDate && $endDate) {
            $inwardQuery->whereBetween('inward_gatepasses.gatepass_date', [$startDate, $endDate]);
        }

        /* ================= UNION ================= */
        $data = $purchaseQuery
            ->unionAll($inwardQuery)
            ->orderBy('purchase_date', 'desc')
            ->get();
        return response()->json([
            'data' => $data
        ]);
    }



    public function sale_report()
    {
        return view('admin_panel.reporting.sale_report');
    }

    public function fetchsaleReport(Request $request)
    {
        if ($request->ajax()) {
            $start = $request->start_date;
            $end = $request->end_date;

            $query = DB::table('sales')
                ->leftJoin('customers', 'sales.customer', '=', 'customers.id')
                ->select(
                    'sales.id',
                    'sales.reference',
                    'sales.product',
                    'sales.product_code',
                    'sales.brand',
                    'sales.unit',
                    'sales.per_price',
                    'sales.per_discount',
                    'sales.qty',
                    'sales.per_total',
                    'sales.total_net',
                    'sales.created_at',
                    'customers.customer_name'
                );

            if ($start && $end) {
                $query->whereBetween(DB::raw('DATE(sales.created_at)'), [$start, $end]);
            }

            $sales = $query->orderBy('sales.created_at', 'asc')->get();

            foreach ($sales as $sale) {
                // --- Fetch Product Names using IDs ---
                if (!empty($sale->product)) {
                    $productIds = explode(',', $sale->product);
                    $products = DB::table('products')
                        ->whereIn('id', $productIds)
                        ->pluck('item_name')
                        ->toArray();
                    $sale->product_names = implode(', ', $products);
                } else {
                    $sale->product_names = '-';
                }

                // --- Merge Sale Returns ---
                $returns = DB::table('sales_returns')
                    ->where('sale_id', $sale->id)
                    ->get();
                $sale->returns = $returns;
            }


            return response()->json($sales);
        }

        return view('admin_panel.reporting.sale_report');
    }



    public function sale_report_category()
    {
        $categories = Category::select('id', 'name')->get();
        return view('admin_panel.reporting.sale_report_category', compact('categories'));
    }

    public function fetchsalecategoryReport(Request $request)
    {
        if ($request->ajax()) {

            $start      = $request->start_date;
            $end        = $request->end_date;
            $categoryId = $request->category_id;

            // ================== BASE SALES QUERY ==================
            $sales = DB::table('sales')
                ->leftJoin('customers', 'sales.customer', '=', 'customers.id')
                ->select(
                    'sales.id',
                    'sales.reference',
                    'sales.product',
                    'sales.product_code',
                    'sales.brand',
                    'sales.unit',
                    'sales.per_price',
                    'sales.per_discount',
                    'sales.qty',
                    'sales.per_total',
                    'sales.total_net',
                    'sales.created_at',
                    'customers.customer_name'
                )
                ->when($start && $end, function ($q) use ($start, $end) {
                    $q->whereBetween(DB::raw('DATE(sales.created_at)'), [$start, $end]);
                })
                ->orderBy('sales.created_at', 'asc')
                ->get();

            $finalSales = [];

            // ================== LOOP SALES ==================
            foreach ($sales as $sale) {

                if (empty($sale->product)) {
                    continue;
                }

                // Convert CSV → Arrays
                $productIds = explode(',', $sale->product);
                $qtyArr     = explode(',', $sale->qty);
                $priceArr   = explode(',', $sale->per_price);
                $totalArr   = explode(',', $sale->per_total);

                // ================== PRODUCTS QUERY ==================
                $products = DB::table('products')
                    ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                    ->whereIn('products.id', $productIds)
                    ->when($categoryId, function ($q) use ($categoryId) {
                        $q->where('products.category_id', $categoryId);
                    })
                    ->select(
                        'products.id',
                        'products.item_name',
                        'categories.name as category_name'
                    )
                    ->get();

                // Skip if no product matched category
                if ($products->isEmpty()) {
                    continue;
                }

                // ================== MATCH VALUES ==================
                $matchedQty   = [];
                $matchedPrice = [];
                $matchedTotal = [];

                foreach ($products as $product) {
                    $index = array_search($product->id, $productIds);

                    if ($index !== false) {
                        $matchedQty[]   = (float) ($qtyArr[$index] ?? 0);
                        $matchedPrice[] = (float) ($priceArr[$index] ?? 0);
                        $matchedTotal[] = (float) ($totalArr[$index] ?? 0);
                    }
                }

                // ================== ASSIGN FILTERED DATA ==================
                $sale->product_names  = $products->pluck('item_name')->implode(', ');
                $sale->categories     = $products->pluck('category_name')->implode(', ');

                $sale->filtered_qty   = implode(', ', $matchedQty);
                $sale->filtered_price = implode(', ', $matchedPrice);
                $sale->filtered_total = implode(', ', $matchedTotal);

                // IMPORTANT: make sure number is numeric
                $sale->filtered_net   = array_sum($matchedTotal);

                // ================== SALE RETURNS ==================
                $sale->returns = DB::table('sales_returns')
                    ->where('sale_id', $sale->id)
                    ->get();

                $finalSales[] = $sale;
            }

            return response()->json($finalSales);
        }

        return view('admin_panel.reporting.sale_report_category');
    }

    public function customer_ledger_report()
    {
        $customers = DB::table('customers')
            ->where(function ($q) {
                $q->where('customer_type', '!=', 'Dual Party')
                  ->orWhereNull('customer_type');
            })
            ->whereNotIn('customer_id', function ($q) {
                $q->select('customer_id')->from('customers')->where('customer_id', 'LIKE', 'VC-%');
            })
            ->select('id', 'customer_name')
            ->get();

        return view('admin_panel.reporting.customer_ledger_report', compact('customers'));
    }

    public function dual_party_ledger_report()
    {
        $dualParties = DB::table('customers')
            ->where('customer_type', 'Dual Party')
            ->orWhere('customer_id', 'LIKE', 'VC-%')
            ->select('id', 'customer_name')
            ->get();

        return view('admin_panel.reporting.dual_party_ledger_report', compact('dualParties'));
    }

    public function fetch_dual_party_ledger(Request $request)
    {
        $customerId = $request->customer_id;
        $start = $request->start_date;
        $end = $request->end_date . ' 23:59:59';
        
        $customer = \App\Models\Customer::find($customerId);
        if (!$customer) return response()->json(['error' => 'Customer not found'], 404);

        $vendor = \App\Models\Vendor::where('name', $customer->customer_name)->first();

        // Standardize Opening Balance (Dr is positive, Cr is negative)
        $opening = ($customer->opening_balance ?? 0) - ($vendor ? ($vendor->opening_balance ?? 0) : 0);

        // Fetch CUSTOMER side transactions
        $sales = DB::table('sales')
            ->where('customer', $customer->id)
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->map(function ($s) {
                return [
                    'date' => $s->created_at,
                    'invoice' => 'INV-' . $s->id,
                    'reference' => 'Sale to Party' . (($s->cash + $s->card > 0) ? ' (Paid: '.$s->cash.')' : ''),
                    'description' => 'Sale',
                    'debit' => $s->total_net,
                    'credit' => (float) ($s->cash + $s->card),
                ];
            });

        $customerPayments = DB::table('customer_payments')
            ->where('customer_id', $customer->id)
            ->whereBetween('payment_date', [$start, $end])
            ->get()
            ->map(function ($p) {
                $isPlus = $p->adjustment_type === 'plus'; // Plus = Debit
                return [
                    'date' => $p->payment_date . ' 23:59:59',
                    'invoice' => '-',
                    'reference' => '-',
                    'description' => 'Cust Rec/Adj: ' . ($p->note ?? ''),
                    'debit' => $isPlus ? (float) $p->amount : 0,
                    'credit' => $isPlus ? 0 : (float) $p->amount,
                ];
            });

        $saleReturns = DB::table('sales_returns')
            ->where('customer', $customer->id)
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->map(function ($r) {
                return [
                    'date' => $r->created_at,
                    'invoice' => 'RET-' . $r->id,
                    'reference' => '-',
                    'description' => 'Sale Return (Ref: ' . $r->sale_id . ')',
                    'debit' => 0,
                    'credit' => (float) $r->total_net,
                ];
            });

        // Fetch VENDOR side transactions
        $purchases = collect([]);
        $purchaseReturns = collect([]);
        $vendorPayments = collect([]);

        if ($vendor) {
            $purchases = \Illuminate\Support\Facades\DB::table('purchases')
                ->where('vendor_id', $vendor->id)
                ->whereBetween('purchase_date', [$start, $end])
                ->get()
                ->map(function ($p) {
                    return [
                        'date' => $p->purchase_date . ' 23:59:59',
                        'invoice' => $p->invoice_no,
                        'reference' => '-',
                        'description' => 'Purchase from Party',
                        'debit' => 0,
                        'credit' => $p->net_amount, // Liability increases
                    ];
                });

            $purchaseReturns = \Illuminate\Support\Facades\DB::table('purchase_returns')
                ->where('vendor_id', $vendor->id)
                ->whereBetween('return_date', [$start, $end])
                ->get()
                ->map(function ($r) {
                    return [
                        'date' => $r->return_date . ' 23:59:59',
                        'invoice' => $r->return_invoice,
                        'reference' => '-',
                        'description' => 'Purchase Return',
                        'debit' => $r->net_amount, // Reduces liability
                        'credit' => 0,
                    ];
                });

            $vendorPayments = \Illuminate\Support\Facades\DB::table('vendor_payments')
                ->where('vendor_id', $vendor->id)
                ->whereBetween('payment_date', [$start, $end])
                ->get()
                ->map(function ($v) {
                    return [
                        'date' => $v->payment_date . ' 23:59:59',
                        'invoice' => '-',
                        'reference' => '-',
                        'description' => 'Vendor Pmt/Adj: ' . ($v->note ?? ''),
                        'debit' => $v->amount, // We paid them, reduces liability
                        'credit' => 0,
                    ];
                });
        }

        $tvs = DB::table('transfer_vouchers')
            ->where(function($q) use ($customer, $vendor) {
                $q->where('source_party_type', 'customer')->where('source_party_id', $customer->id)
                  ->orWhere('destination_party_type', 'customer')->where('destination_party_id', $customer->id)
                  ->orWhere('customer_id', $customer->id);
                if ($vendor) {
                    $q->orWhere('source_party_type', 'vendor')->where('source_party_id', $vendor->id)
                      ->orWhere('destination_party_type', 'vendor')->where('destination_party_id', $vendor->id)
                      ->orWhere('vendor_id', $vendor->id);
                }
            })
            ->whereBetween('transfer_date', [$start, $end])
            ->get()
            ->flatMap(function ($tv) use ($customer, $vendor) {
                $impacts = [];
                
                // 1. Check if Customer side is affected
                if (($tv->source_party_type == 'customer' && $tv->source_party_id == $customer->id) || ($tv->customer_id == $customer->id && !$tv->source_party_type)) {
                    $impacts[] = [
                        'date' => $tv->transfer_date . ' 23:59:59',
                        'invoice' => $tv->tvid ?? '-',
                        'reference' => '-',
                        'description' => 'Transfer Voucher (Source): ' . ($tv->remarks ?? 'Balance Transferred'),
                        'debit' => 0,
                        'credit' => (float) $tv->amount,
                    ];
                } elseif ($tv->destination_party_type == 'customer' && $tv->destination_party_id == $customer->id) {
                    $impacts[] = [
                        'date' => $tv->transfer_date . ' 23:59:59',
                        'invoice' => $tv->tvid ?? '-',
                        'reference' => '-',
                        'description' => 'Transfer Voucher (Dest): ' . ($tv->remarks ?? 'Balance Transferred'),
                        'debit' => (float) $tv->amount,
                        'credit' => 0,
                    ];
                }

                // 2. Check if Vendor side is affected
                if ($vendor) {
                    if ($tv->source_party_type == 'vendor' && $tv->source_party_id == $vendor->id) {
                        $impacts[] = [
                            'date' => $tv->transfer_date . ' 23:59:59',
                            'invoice' => $tv->tvid ?? '-',
                            'reference' => '-',
                            'description' => 'Transfer Voucher (Source): ' . ($tv->remarks ?? 'Balance Transferred'),
                            'debit' => (float) $tv->amount,
                            'credit' => 0,
                        ];
                    } elseif (($tv->destination_party_type == 'vendor' && $tv->destination_party_id == $vendor->id) || ($tv->vendor_id == $vendor->id && !$tv->destination_party_type)) {
                        $impacts[] = [
                            'date' => $tv->transfer_date . ' 23:59:59',
                            'invoice' => $tv->tvid ?? '-',
                            'reference' => '-',
                            'description' => 'Transfer Voucher (Dest): ' . ($tv->remarks ?? 'Balance Transferred'),
                            'debit' => 0,
                            'credit' => (float) $tv->amount,
                        ];
                    }
                }

                return $impacts;
            });

        $otherIncomes = DB::table('other_incomes')
            ->where(function($q) use ($customer, $vendor) {
                $q->where('party_type', 'customer')->where('customer_id', $customer->id);
                if ($vendor) {
                    $q->orWhere('party_type', 'vendor')->where('vendor_id', $vendor->id);
                }
            })
            ->whereBetween('date', [$start, $end])
            ->get()
            ->map(function ($inc) {
                return [
                    'date' => $inc->date . ' 23:59:59',
                    'invoice' => '-',
                    'reference' => '-',
                    'description' => 'Other Income (Deposit): ' . $inc->title . ' (' . ($inc->remarks ?? '') . ')',
                    'debit' => (float) $inc->amount,
                    'credit' => 0,
                ];
            });

        $transactions = $sales
            ->merge($customerPayments)
            ->merge($saleReturns)
            ->merge($purchases)
            ->merge($purchaseReturns)
            ->merge($vendorPayments)
            ->merge($tvs)
            ->merge($otherIncomes)
            ->sortBy('date') // Sort by Date
            ->values()
            ->all();

        // Calculate running balance
        $balance = $opening;
        foreach ($transactions as $key => $t) {
            $balance = $balance + $t['debit'] - $t['credit'];
            $transactions[$key]['balance'] = $balance;
        }

        return response()->json([
            'customer' => $customer,
            'opening_balance' => $opening,
            'transactions' => $transactions,
        ]);
    }

    public function fetch_customer_ledger(Request $request)
    {
        $customerId = $request->customer_id;
        $start = $request->start_date;
        $end = $request->end_date;
        $customer = DB::table('customers')->where('id', $customerId)->first();

        $opening = $customer->opening_balance ?? 0;

        // Sales = Debit
        $end = $end . ' 23:59:59';

        $sales = DB::table('sales')
            ->where('customer', $customerId)
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->map(function ($s) {
                return [
                    'date' => $s->created_at,
                    'sort_type' => 1, // 🔥 SALE FIRST
                    'invoice' => 'INV-' . $s->id,
                    'reference' => $s->reference,
                    'description' => 'To Sale A/c' . (($s->cash + $s->card > 0) ? ' (Payment Received: Rs.' . ($s->cash + $s->card) . ')' : ''),
                    'debit' => $s->total_net,
                    'credit' => (float) ($s->cash + $s->card),
                ];
            });
        $payments = DB::table('customer_payments')
            ->where('customer_id', $customerId)
            ->whereBetween('payment_date', [$start, $end])
            ->get()
            ->map(function ($p) {
                // 🔥 Check adjustment type: plus = debit (increases balance), minus = credit (decreases balance)
                $isPlus = $p->adjustment_type === 'plus';
                return [
                    'date' => $p->payment_date . ' 23:59:59',
                    'sort_type' => 2, // RECOVERY/ADJUSTMENT AFTER SALE
                    'invoice' => '-',
                    'reference' => '-',
                    'description' => ($isPlus ? '[+] Adjustment: ' : '[-] Payment: ') . ($p->note ?? ($isPlus ? 'Balance Increased' : 'Payment Received')),
                    'debit' => $isPlus ? (float) $p->amount : 0,
                    'credit' => $isPlus ? 0 : (float) $p->amount,
                ];
            });

        $returns = DB::table('sales_returns')
            ->where('customer', $customerId)
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->map(function ($r) {
                return [
                    'date' => $r->created_at,
                    'sort_type' => 3, // 🔥 RETURNS AFTER PAYMENTS
                    'invoice' => 'RET-' . $r->id,
                    'reference' => $r->reference ?? '-',
                    'description' => 'Sale Return (Ref: ' . $r->sale_id . ')',
                    'debit' => 0,
                    'credit' => (float) $r->total_net,
                ];
            });

        $rvs = DB::table('receipts_vouchers')
            ->where('type', 'customer')
            ->where('party_id', $customerId)
            ->whereBetween('receipt_date', [$start, $end])
            ->get()
            ->map(function ($rv) {
                return [
                    'date' => $rv->receipt_date . ' 23:59:59',
                    'sort_type' => 2, // SAME AS RECOVERY
                    'invoice' => $rv->rvid ?? '-',
                    'reference' => '-',
                    'description' => 'Receipt Voucher: ' . ($rv->remarks ?? 'N/A'),
                    'debit' => 0,
                    'credit' => (float) $rv->total_amount,
                ];
            });

        $pvs = DB::table('payment_vouchers')
            ->where('type', 'customer')
            ->where('party_id', $customerId)
            ->whereBetween('receipt_date', [$start, $end])
            ->get()
            ->map(function ($pv) {
                return [
                    'date' => $pv->receipt_date . ' 23:59:59',
                    'sort_type' => 4, // AFTER RETURNS
                    'invoice' => $pv->pvid ?? '-',
                    'reference' => '-',
                    'description' => 'Payment Voucher: ' . ($pv->remarks ?? 'N/A'),
                    'debit' => (float) $pv->total_amount,
                    'credit' => 0,
                ];
            });

        $tvs = DB::table('transfer_vouchers')
            ->where(function($q) use ($customerId) {
                $q->where('source_party_type', 'customer')->where('source_party_id', $customerId)
                  ->orWhere('destination_party_type', 'customer')->where('destination_party_id', $customerId)
                  ->orWhere('customer_id', $customerId); // Fallback for old records
            })
            ->whereBetween('transfer_date', [$start, $end])
            ->get()
            ->map(function ($tv) use ($customerId) {
                $isSource = ($tv->source_party_type == 'customer' && $tv->source_party_id == $customerId) || ($tv->customer_id == $customerId && !$tv->source_party_type);
                
                return [
                    'date' => $tv->transfer_date . ' 23:59:59',
                    'sort_type' => 2,
                    'invoice' => $tv->tvid ?? '-',
                    'reference' => '-',
                    'description' => 'Transfer Voucher: ' . ($tv->remarks ?? 'Balance Transferred'),
                    'debit' => $isSource ? 0 : (float) $tv->amount, // Source = Credit (reduces), Destination = Debit (increases)
                    'credit' => $isSource ? (float) $tv->amount : 0,
                ];
            });

        $otherIncomes = DB::table('other_incomes')
            ->where('party_type', 'customer')
            ->where('customer_id', $customerId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->map(function ($inc) {
                return [
                    'date' => $inc->date . ' 23:59:59',
                    'sort_type' => 2,
                    'invoice' => '-',
                    'reference' => '-',
                    'description' => 'Other Income (Deposit): ' . $inc->title . ' (' . ($inc->remarks ?? '') . ')',
                    'debit' => (float) $inc->amount,
                    'credit' => 0,
                ];
            });

        $transactions = $sales
            ->merge($payments)
            ->merge($returns)
            ->merge($rvs)
            ->merge($pvs)
            ->merge($tvs)
            ->merge($otherIncomes)
            ->sort(function ($a, $b) {

                // 1️⃣ Date compare
                if (strtotime($a['date']) !== strtotime($b['date'])) {
                    return strtotime($a['date']) <=> strtotime($b['date']);
                }

                // 2️⃣ Same date → SALE FIRST
                return $a['sort_type'] <=> $b['sort_type'];
            })
            ->values()
            ->all();

        // Running balance calculation
        $balance = $opening;
        foreach ($transactions as $key => $t) {
            $balance = $balance + $t['debit'] - $t['credit'];
            $transactions[$key]['balance'] = $balance;
        }

        return response()->json([
            'customer' => $customer,
            'opening_balance' => $opening,
            'transactions' => $transactions,
        ]);
    }

    public function vendor_ledger_report()
    {
        $dualPartyNames = DB::table('customers')
            ->where('customer_type', 'Dual Party')
            ->orWhere('customer_id', 'LIKE', 'VC-%')
            ->pluck('customer_name');
            
        $vendors = DB::table('vendors')
            ->whereNotIn('name', $dualPartyNames)
            ->select('id', 'name')
            ->get();

        return view('admin_panel.reporting.vendor_ledger_report', compact('vendors'));
    }

    public function fetch_vendor_ledger(Request $request)
    {
        $vendorId = $request->vendor_id;
        $start = $request->start_date;
        $end = $request->end_date . ' 23:59:59';

        $vendor = DB::table('vendors')->where('id', $vendorId)->first();
        $opening = $vendor->opening_balance ?? 0;
        // 🔹 1. Purchases → Debit (we owe vendor)
        $purchases = DB::table('purchases')
            ->where('vendor_id', $vendorId)
            ->whereBetween('purchase_date', [$start, $end])
            ->get()
            ->map(function ($p) {
                return [
                    'date' => $p->purchase_date,
                    'invoice' => $p->invoice_no,
                    'description' => 'Purchase Invoice',
                    'debit' => $p->net_amount,
                    'credit' => 0,
                ];
            });

        // 🔹 2. Purchase Returns → Credit (reduces vendor balance)
        $returns = DB::table('purchase_returns')
            ->where('vendor_id', $vendorId)
            ->whereBetween('return_date', [$start, $end])
            ->get()
            ->map(function ($r) {
                return [
                    'date' => $r->return_date,
                    'invoice' => $r->return_invoice,
                    'description' => 'Purchase Return',
                    'debit' => 0,
                    'credit' => $r->net_amount,
                ];
            });

        // 🔹 3. Vendor Payments → Credit (we paid vendor)
        $payments = DB::table('vendor_payments')
            ->where('vendor_id', $vendorId)
            ->whereBetween('payment_date', [$start, $end])
            ->get()
            ->map(function ($v) {
                // adjustment_type 'plus' means Payment In (Receipt from Vendor), 'minus' means Payment Out
                $isPlus = isset($v->adjustment_type) && $v->adjustment_type === 'plus';
                return [
                    'date' => $v->payment_date,
                    'invoice' => '-',
                    'description' => ($isPlus ? '[+] Receipt: ' : '[-] Payment: ') . ($v->note ?? ''),
                    'debit' => $isPlus ? $v->amount : 0,
                    'credit' => $isPlus ? 0 : $v->amount,
                ];
            });

        // 🔹 Merge all
        $transactions = $purchases->merge($returns)->merge($payments)->sortBy('date')->values()->all();

        // 🔹 Running Balance Calculation (Debit increases, Credit decreases)
       $opening = (float) str_replace(',', '', $vendor->opening_balance ?? 0);

        $tvs = DB::table('transfer_vouchers')
            ->where(function($q) use ($vendorId) {
                $q->where('source_party_type', 'vendor')->where('source_party_id', $vendorId)
                  ->orWhere('destination_party_type', 'vendor')->where('destination_party_id', $vendorId)
                  ->orWhere('vendor_id', $vendorId); // Fallback for old records
            })
            ->whereBetween('transfer_date', [$start, $end])
            ->get()
            ->map(function ($tv) use ($vendorId) {
                $isSource = ($tv->source_party_type == 'vendor' && $tv->source_party_id == $vendorId);
                // Note: old records (without source_party_type) where vendor_id matched were DESTINATION (Minus)
                if (!$tv->source_party_type && $tv->vendor_id == $vendorId) {
                    $isSource = false;
                }

                return [
                    'date' => $tv->transfer_date . ' 23:59:59',
                    'invoice' => $tv->tvid ?? '-',
                    'description' => 'Transfer Voucher: ' . ($tv->remarks ?? 'Balance Transferred'),
                    'debit' => $isSource ? (float) $tv->amount : 0, // Source = Debit (increases), Destination = Credit (decreases)
                    'credit' => $isSource ? 0 : (float) $tv->amount,
                ];
            });

        $otherIncomes = DB::table('other_incomes')
            ->where('party_type', 'vendor')
            ->where('vendor_id', $vendorId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->map(function ($inc) {
                return [
                    'date' => $inc->date . ' 23:59:59',
                    'invoice' => '-',
                    'description' => 'Other Income (Deposit): ' . $inc->title . ' (' . ($inc->remarks ?? '') . ')',
                    'debit' => 0,
                    'credit' => (float) $inc->amount,
                ];
            });

        $transactions = $purchases
            ->merge($returns)
            ->merge($payments)
            ->merge($tvs)
            ->merge($otherIncomes)
            ->sortBy('date')
            ->values()
            ->all();

        $balance = $opening;

        foreach ($transactions as $key => $t) {

            $debit  = (float) ($t['debit']  ?? 0);
            $credit = (float) ($t['credit'] ?? 0);

            $balance = $balance + $debit - $credit;

            $transactions[$key]['balance'] = round($balance, 2);
        }

        return response()->json([
            'vendor' => $vendor,
            'opening_balance' => $opening,
            'transactions' => $transactions,
        ]);
    }

    public function party_balance_report()
    {
        $customers = DB::table('customers')->select('id', 'customer_name')->get();
        $vendors = DB::table('vendors')->select('id', 'name')->get();
        return view('admin_panel.reporting.party_balance_report', compact('customers', 'vendors'));
    }

    public function fetch_party_balances(Request $request)
    {
        $type = $request->type; // customer, vendor, dual, all
        $partyId = $request->party_id; // specific ID or 'all'

        $results = [];

        // 1. Fetch Customers
        if ($type == 'customer' || $type == 'all' || $type == 'dual') {
            $query = DB::table('customers');
            if ($type == 'dual') {
                $query->where('customer_type', 'Dual Party');
            }
            if ($partyId != 'all' && ($type == 'customer' || $type == 'dual')) {
                $query->where('id', $partyId);
            }
            
            $customers = $query->get();
            foreach ($customers as $c) {
                $balance = $this->calculateCustomerBalance($c->id);
                
                // If it's dual, also fetch vendor side
                $vendorSide = 0;
                if ($c->customer_type == 'Dual Party') {
                    $v = DB::table('vendors')->where('name', $c->customer_name)->first();
                    if ($v) {
                        $vendorSide = $this->calculateVendorBalance($v->id);
                    }
                }

                $results[] = [
                    'name' => $c->customer_name,
                    'type' => $c->customer_type == 'Dual Party' ? 'Dual Party' : 'Customer',
                    'mobile' => $c->mobile,
                    'address' => $c->address,
                    'balance' => $balance + $vendorSide, // For dual party, balance is net
                    'side' => ($balance + $vendorSide) >= 0 ? 'Dr' : 'Cr',
                ];
            }
        }

        // 2. Fetch Vendors (only if type is vendor or all, and NOT already added as dual)
        if ($type == 'vendor' || $type == 'all') {
            $query = DB::table('vendors');
            
            // Exclude vendors who are already part of a Dual Party (to avoid double counting)
            $dualNames = DB::table('customers')->where('customer_type', 'Dual Party')->pluck('customer_name')->toArray();
            $query->whereNotIn('name', $dualNames);

            if ($partyId != 'all' && $type == 'vendor') {
                $query->where('id', $partyId);
            }

            $vendors = $query->get();
            foreach ($vendors as $v) {
                $balance = $this->calculateVendorBalance($v->id);
                $results[] = [
                    'name' => $v->name,
                    'type' => 'Vendor',
                    'mobile' => $v->phone,
                    'address' => $v->address,
                    'balance' => $balance,
                    'side' => $balance >= 0 ? 'Dr' : 'Cr',
                ];
            }
        }

        return response()->json([
            'results' => $results,
            'total' => collect($results)->sum('balance'),
        ]);
    }

    private function calculateCustomerBalance($customerId)
    {
        $customer = DB::table('customers')->where('id', $customerId)->first();
        if (!$customer) return 0;

        $opening = (float)($customer->opening_balance ?? 0);

        // Sales (Debit increases)
        $sales = DB::table('sales')->where('customer', $customerId)->sum(DB::raw('total_net - (cash + card)'));
        
        // Payments (Credit decreases)
        $payments = DB::table('customer_payments')->where('customer_id', $customerId)->sum('amount');
        
        // Returns (Credit decreases)
        $returns = DB::table('sales_returns')->where('customer', $customerId)->sum('total_net');

        // Transfer Vouchers
        $tvs = DB::table('transfer_vouchers')
            ->where(function($q) use ($customerId) {
                $q->where('source_party_type', 'customer')->where('source_party_id', $customerId)
                  ->orWhere('destination_party_type', 'customer')->where('destination_party_id', $customerId)
                  ->orWhere('customer_id', $customerId);
            })->get();

        $tvImpact = 0;
        foreach($tvs as $tv) {
            $isSource = ($tv->source_party_type == 'customer' && $tv->source_party_id == $customerId) || ($tv->customer_id == $customerId && !$tv->source_party_type);
            if ($isSource) {
                $tvImpact -= $tv->amount; // Credit
            } else {
                $tvImpact += $tv->amount; // Debit
            }
        }

        return $opening + $sales - $payments - $returns + $tvImpact;
    }

    private function calculateVendorBalance($vendorId)
    {
        $vendor = DB::table('vendors')->where('id', $vendorId)->first();
        if (!$vendor) return 0;

        $opening = (float)($vendor->opening_balance ?? 0);

        // Purchases (Debit increases)
        $purchases = DB::table('purchases')->where('vendor_id', $vendorId)->sum('net_amount');
        
        // Payments (Payment Out reduces balance, Payment In increases balance)
        $paymentsOut = DB::table('vendor_payments')
            ->where('vendor_id', $vendorId)
            ->where(function($q) {
                $q->where('adjustment_type', 'minus')->orWhereNull('adjustment_type');
            })
            ->sum('amount');
            
        $paymentsIn = DB::table('vendor_payments')
            ->where('vendor_id', $vendorId)
            ->where('adjustment_type', 'plus')
            ->sum('amount');
            
        $netPayments = $paymentsOut - $paymentsIn;
        
        // Returns (Credit decreases)
        $returns = DB::table('purchase_returns')->where('vendor_id', $vendorId)->sum('net_amount');

        // Transfer Vouchers
        $tvs = DB::table('transfer_vouchers')
            ->where(function($q) use ($vendorId) {
                $q->where('source_party_type', 'vendor')->where('source_party_id', $vendorId)
                  ->orWhere('destination_party_type', 'vendor')->where('destination_party_id', $vendorId)
                  ->orWhere('vendor_id', $vendorId);
            })->get();

        $tvImpact = 0;
        foreach($tvs as $tv) {
            $isSource = ($tv->source_party_type == 'vendor' && $tv->source_party_id == $vendorId);
            if (!$tv->source_party_type && $tv->vendor_id == $vendorId) $isSource = false;

            if ($isSource) {
                $tvImpact += $tv->amount; // Debit (Increase liability)
            } else {
                $tvImpact -= $tv->amount; // Credit (Decrease liability)
            }
        }

        return $opening + $purchases - $netPayments - $returns + $tvImpact;
    }

    public function cashbook()
    {
        $today = Carbon::today()->toDateString();

        /* ================= RECEIPTS ================= */

        $receipts = [];

        // Walk-in Sales
        $walkinSales = Sale::whereDate('created_at', $today)
            ->where('customer', 'Walk-in Customer')
            ->get();

        foreach ($walkinSales as $sale) {
            $receipts[] = [
                'title'  => 'Walk-in Sale',
                'ref'    => 'Invoice #' . $sale->invoice_no,
                'amount' => $sale->total_net,
            ];
        }

        // Customer Recoveries (Cash)
        $customerRecoveries = CustomerPayment::with('customer')
            ->whereDate('payment_date', $today)
            ->where('payment_method', 'Cash')
            ->get();

        foreach ($customerRecoveries as $pay) {
            $receipts[] = [
                'title'  => 'Customer Recovery',
                'ref'    => $pay->customer->name ?? '-',
                'amount' => $pay->amount,
            ];
        }

        $totalReceipts = collect($receipts)->sum('amount');

        /* ================= PAYMENTS ================= */

        $payments = [];

        // Vendor Payments
        $vendorPayments = VendorPayment::with('vendor')
            ->whereDate('payment_date', $today)
            ->where('payment_method', 'Cash')
            ->get();

        foreach ($vendorPayments as $pay) {
            $payments[] = [
                'title'  => 'Vendor Payment',
                'ref'    => $pay->vendor->name ?? '-',
                'amount' => $pay->amount,
            ];
        }

        // Expense Vouchers
        $expenseVouchers = ExpenseVoucher::whereDate('entry_date', $today)->get();

        foreach ($expenseVouchers as $exp) {
            $payments[] = [
                'title'  => 'Expense',
                'ref'    => $exp->expense_title ?? 'Voucher #' . $exp->id,
                'amount' => $exp->total_amount,
            ];
        }

        $totalPayments = collect($payments)->sum('amount');

        /* ================= BALANCE ================= */

        $openingBalance = 0;
        $closingBalance = $openingBalance + $totalReceipts - $totalPayments;

        // IMPORTANT for blade loop
        $maxRows = max(count($receipts), count($payments));

        return view('admin_panel.reporting.CashBook', compact(
            'receipts',
            'payments',
            'maxRows',
            'totalReceipts',
            'totalPayments',
            'openingBalance',
            'closingBalance'
        ));
    }
    
    public function simple_cash_book(Request $request)
    {
        $selectedDate = $request->get('date', now()->toDateString());

        // 1. OPENING BALANCE (Previous closed day)
        $previousDay = DB::table('day_closings')
            ->where('date', '<', $selectedDate)
            ->where('is_closed', true)
            ->orderBy('date', 'desc')
            ->first();

        // If no previous day exists, check if there's an unclosed current record
        if (!$previousDay) {
            $opening = DB::table('day_closings')->where('date', $selectedDate)->value('opening_balance') ?? 0;
        } else {
            $opening = $previousDay->closing_balance;
        }

        // 2. RECEIPTS (Cash In)
        // A. Sales Cash (Actual cash paid during sale, not total bill)
        $salesReceipts = DB::table('sales')
            ->whereDate('created_at', $selectedDate)
            ->get(['cash', 'invoice_no', 'customer'])
            ->map(function($s) {
                return [
                    'title' => 'Sale Cash Receive',
                    'ref' => 'INV-' . ($s->invoice_no ?? $s->id),
                    'party' => $s->customer,
                    'amount' => (float) str_replace(',', '', $s->cash ?: 0)
                ];
            });

        // B. Customer Payments (Recoveries = minus, Refunds/Gave Cash = plus)
        $rawCustomerPayments = DB::table('customer_payments')
            ->whereDate('payment_date', $selectedDate)
            ->leftJoin('customers', 'customer_payments.customer_id', '=', 'customers.id')
            ->get(['customer_payments.*', 'customers.customer_name']);

        // 1. Recoveries (Cash In: minus)
        $customerRecoveries = $rawCustomerPayments->where('adjustment_type', 'minus')->map(function($p) {
            return [
                'title' => 'Customer Recovery (Cash In)',
                'ref' => $p->note ?? 'Receipt',
                'party' => $p->customer_name,
                'amount' => (float) $p->amount
            ];
        });

        // 2. Refunds/Gave Cash (Cash Out: plus)
        $customerPaymentsToCustomer = $rawCustomerPayments->where('adjustment_type', 'plus')->map(function($p) {
            return [
                'title' => 'Cash Paid to Customer',
                'ref' => $p->note ?? 'Payment',
                'party' => $p->customer_name,
                'amount' => (float) $p->amount
            ];
        });

        // C. Receipt Vouchers
        $receiptVouchers = DB::table('receipts_vouchers')
            ->whereDate('receipt_date', $selectedDate)
            ->get(['total_amount', 'rvid', 'remarks'])
            ->map(function($rv) {
                return [
                    'title' => 'Receipt Voucher',
                    'ref' => $rv->rvid,
                    'party' => $rv->remarks ?? '-',
                    'amount' => (float) $rv->total_amount
                ];
            });

        // D. Sales Returns (Cash Refunds)
        $salesReturns = DB::table('sales_returns')
            ->leftJoin('sales', 'sales_returns.sale_id', '=', 'sales.id')
            ->whereDate('sales_returns.created_at', $selectedDate)
            ->get(['sales_returns.cash', 'sales_returns.return_note', 'sales.customer', 'sales_returns.id'])
            ->map(function($sr) {
                return [
                    'title' => 'Sale Return (Cash)',
                    'ref' => 'RET-' . ($sr->id ?? ''),
                    'party' => $sr->customer ?? '-',
                    'amount' => (float) str_replace(',', '', $sr->cash ?: 0)  // Positive because it will be in Payments section
                ];
            })
            ->filter(function($item) {
                return $item['amount'] != 0;  // Only show if there's actual cash refund
            });

        // E. Vendor Receipts (Cash In: plus)
        $vendorReceipts = DB::table('vendor_payments')
            ->whereDate('payment_date', $selectedDate)
            ->where('adjustment_type', 'plus')
            ->leftJoin('vendors', 'vendor_payments.vendor_id', '=', 'vendors.id')
            ->get(['vendor_payments.amount', 'vendor_payments.note', 'vendors.name'])
            ->map(function($vp) {
                return [
                    'title' => 'Vendor Receipt (Cash In)',
                    'ref' => $vp->note ?? 'Receipt',
                    'party' => $vp->name,
                    'amount' => (float) $vp->amount
                ];
            });

        $allReceipts = $salesReceipts->merge($customerRecoveries)->merge($receiptVouchers)->merge($vendorReceipts);
        $totalIn = $allReceipts->sum('amount');

        // 3. PAYMENTS (Cash Out)
        // A. Vendor Payments (Cash Out: minus or null)
        $vendorPayments = DB::table('vendor_payments')
            ->whereDate('payment_date', $selectedDate)
            ->where(function($q) {
                $q->where('adjustment_type', 'minus')->orWhereNull('adjustment_type');
            })
            ->leftJoin('vendors', 'vendor_payments.vendor_id', '=', 'vendors.id')
            ->get(['vendor_payments.amount', 'vendor_payments.note', 'vendors.name'])
            ->map(function($vp) {
                return [
                    'title' => 'Vendor Payment (Cash Out)',
                    'ref' => $vp->note ?? 'Payment',
                    'party' => $vp->name,
                    'amount' => (float) $vp->amount
                ];
            });

        // B. Expense Vouchers
        $expenseVouchers = DB::table('expense_vouchers')
            ->whereDate('entry_date', $selectedDate)
            ->get(['total_amount', 'evid', 'remarks'])
            ->map(function($ev) {
                return [
                    'title' => 'Expense',
                    'ref' => $ev->evid,
                    'party' => $ev->remarks ?? '-',
                    'amount' => (float) $ev->total_amount
                ];
            });

        // C. Payment Vouchers
        $paymentVouchers = DB::table('payment_vouchers')
            ->whereDate('receipt_date', $selectedDate)
            ->get(['total_amount', 'pvid', 'remarks'])
            ->map(function($pv) {
                return [
                    'title' => 'Payment Voucher',
                    'ref' => $pv->pvid,
                    'party' => $pv->remarks ?? '-',
                    'amount' => (float) $pv->total_amount
                ];
            });

        $allPayments = $vendorPayments->merge($expenseVouchers)->merge($paymentVouchers)->merge($customerPaymentsToCustomer)->merge($salesReturns);
        $totalOut = $allPayments->sum('amount');

        // 4. CLOSING
        $closing = $opening + $totalIn - $totalOut;

        // Check if day is already closed
        $dayClosed = DB::table('day_closings')
            ->where('date', $selectedDate)
            ->where('is_closed', true)
            ->exists();

        return view('admin_panel.reporting.simple_cash_book', compact(
            'selectedDate',
            'opening',
            'allReceipts',
            'allPayments',
            'totalIn',
            'totalOut',
            'closing',
            'dayClosed'
        ));
    }

    public function close_day_cash(Request $request)
    {
        $date = $request->date;
        $totalIn = (float) $request->total_in;
        $totalOut = (float) $request->total_out;
        $opening = (float) $request->opening;
        $closing = $opening + $totalIn - $totalOut;

        DB::beginTransaction();
        try {
            DB::table('day_closings')->updateOrInsert(
                ['date' => $date],
                [
                    'opening_balance' => $opening,
                    'total_in' => $totalIn,
                    'total_out' => $totalOut,
                    'closing_balance' => $closing,
                    'is_closed' => true,
                    'closed_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // Auto-open next day with this closing balance
            $nextDay = \Carbon\Carbon::parse($date)->addDay()->toDateString();
            DB::table('day_closings')->updateOrInsert(
                ['date' => $nextDay],
                [
                    'opening_balance' => $closing,
                    'is_closed' => false,
                    'updated_at' => now()
                ]
            );

            DB::commit();
            return back()->with('success', 'Day Closed Successfully! Closing Balance: Rs. ' . number_format($closing, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error closing day: ' . $e->getMessage());
        }
    }

    public function profit_loss_report(Request $request)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;
        $filter    = $request->filter;

        if ($filter == 'daily') {
            $startDate = Carbon::today()->toDateString();
            $endDate   = Carbon::today()->toDateString();
        } elseif ($filter == 'weekly') {
            $startDate = Carbon::now()->startOfWeek()->toDateString();
            $endDate   = Carbon::now()->endOfWeek()->toDateString();
        } elseif (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate   = Carbon::now()->endOfMonth()->toDateString();
        }

        list($boughtMap, $soldMap) = $this->buildGlobalStockMaps($startDate);

        // --- SALES PROFIT ---
        $sales = Sale::whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
        $totalSalesProfit = 0;
        $totalSalesAmount = 0;
        $totalExtraDiscount = 0;
        $totalLabourCharges = 0;
        $productWiseProfit = [];

        foreach ($sales as $sale) {
            $totalSalesAmount += (float)$sale->total_bill_amount;
            $totalExtraDiscount += (float)$sale->total_extradiscount;
            $totalLabourCharges += (float)($sale->labour_charges ?? 0);

            $productIds = explode(',', $sale->product);
            $qtys       = explode(',', $sale->qty);
            $prices     = explode(',', $sale->per_price);

            foreach ($productIds as $index => $pid) {
                $qty   = (float)($qtys[$index] ?? 0);
                $price = (float)($prices[$index] ?? 0);
                if ($qty <= 0) continue;

                $product = Product::find($pid);
                
                // Fetch actual purchase price around the time of sale
                $latestPurchase = DB::table('purchase_items')
                    ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                    ->where('purchase_items.product_id', $pid)
                    ->whereDate('purchases.purchase_date', '<=', \Carbon\Carbon::parse($sale->created_at)->toDateString())
                    ->orderBy('purchases.purchase_date', 'desc')
                    ->orderBy('purchase_items.id', 'desc')
                    ->value('purchase_items.price');
                
                if ($latestPurchase) {
                    $purchaseCost = (float) $latestPurchase;
                } else {
                    $purchaseCost = 0;
                }

                $availableForCosting = ($boughtMap[$pid] ?? 0) - ($soldMap[$pid] ?? 0);
                
                if ($availableForCosting > 0) {
                    $qtyWithCost = min($qty, $availableForCosting);
                    $qtyFree = $qty - $qtyWithCost;
                } else {
                    $qtyWithCost = 0;
                    $qtyFree = $qty;
                }
                
                if (!isset($soldMap[$pid])) $soldMap[$pid] = 0;
                $soldMap[$pid] += $qty;

                $itemProfit = ($price - $purchaseCost) * $qtyWithCost + ($price - 0) * $qtyFree;
                $totalSalesProfit += $itemProfit;
                
                $productName = $product ? $product->item_name : 'Unknown Product (ID: '.$pid.')';
                if (!isset($productWiseProfit[$productName])) {
                    $productWiseProfit[$productName] = ['qty' => 0, 'profit' => 0];
                }
                $productWiseProfit[$productName]['qty'] += $qty;
                $productWiseProfit[$productName]['profit'] += $itemProfit;
            }
        }

        // --- RETURNS LOSS ---
        $returns = SalesReturn::whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
        $totalReturnLoss = 0;
        $totalReturnAmount = 0;

        foreach ($returns as $ret) {
            $totalReturnAmount += (float)$ret->total_bill_amount;
            $productNames = explode(',', $ret->product);
            $qtys         = explode(',', $ret->qty);
            $prices       = explode(',', $ret->per_price);

            foreach ($productNames as $index => $pname) {
                $qty   = (float)($qtys[$index] ?? 0);
                $price = (float)($prices[$index] ?? 0);
                if ($qty <= 0) continue;

                // Find by name since returns might store names
                $product = Product::where('item_name', $pname)->first();
                
                if ($product) {
                    $latestPurchase = DB::table('purchase_items')
                        ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                        ->where('purchase_items.product_id', $product->id)
                        ->whereDate('purchases.purchase_date', '<=', \Carbon\Carbon::parse($ret->created_at)->toDateString())
                        ->orderBy('purchases.purchase_date', 'desc')
                        ->orderBy('purchase_items.id', 'desc')
                        ->value('purchase_items.price');
                        
                    $cost = $latestPurchase ? (float) $latestPurchase : 0;
                } else {
                    $cost = 0;
                }

                $itemLoss = ($price - $cost) * $qty;
                $totalReturnLoss += $itemLoss;
                
                $pNameKey = $product ? $product->item_name : $pname;
                if (!isset($productWiseProfit[$pNameKey])) {
                    $productWiseProfit[$pNameKey] = ['qty' => 0, 'profit' => 0];
                }
                $productWiseProfit[$pNameKey]['qty'] -= $qty;
                $productWiseProfit[$pNameKey]['profit'] -= $itemLoss;
            }
        }

        $netSalesProfit = ($totalSalesProfit - $totalExtraDiscount + $totalLabourCharges) - $totalReturnLoss;

        // --- EXPENSES ---
        $expenses = ExpenseVoucher::whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
        $totalExpenseAmount = 0;
        foreach ($expenses as $exp) {
            $amounts = $exp->amount;
            if (is_array($amounts)) {
                $totalExpenseAmount += array_sum($amounts);
            } else {
                $decoded = json_decode($amounts, true);
                if (is_array($decoded)) $totalExpenseAmount += array_sum($decoded);
            }
        }

        $finalNetProfit = $netSalesProfit - $totalExpenseAmount;

        return view('admin_panel.reporting.profit_loss', compact(
            'startDate', 'endDate', 'filter',
            'totalSalesAmount', 'totalExtraDiscount', 'totalLabourCharges',
            'totalSalesProfit', 'totalReturnLoss', 'netSalesProfit', 
            'totalExpenseAmount', 'finalNetProfit', 'totalReturnAmount',
            'productWiseProfit'
        ));
    }

    public function customer_wise_profit(Request $request)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate   = Carbon::now()->endOfMonth()->toDateString();
        }

        list($boughtMap, $soldMap) = $this->buildGlobalStockMaps($startDate);

        // --- SALES INFO ---
        $sales = Sale::whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();

        $customerWiseData = [];

        foreach ($sales as $sale) {
            $customerId = $sale->customer;
            $customerName = 'Unknown';
            if (is_numeric($customerId)) {
                $customer = \App\Models\Customer::find($customerId);
                $customerName = $customer ? $customer->customer_name : 'Walk-in / Unknown';
            } else {
                $customerName = $customerId ?: 'Walk-in';
            }

            if (!isset($customerWiseData[$customerName])) {
                $customerWiseData[$customerName] = [
                    'total_sales' => 0,
                    'total_profit' => 0,
                    'total_items_qty' => 0
                ];
            }

            // Calculate profit for this sale
            $saleProfit = 0;
            $saleQty = 0;

            $productIds = explode(',', $sale->product);
            $qtys       = explode(',', $sale->qty);
            $prices     = explode(',', $sale->per_price);

            foreach ($productIds as $index => $pid) {
                $qty   = (float)($qtys[$index] ?? 0);
                $price = (float)($prices[$index] ?? 0);
                if ($qty <= 0) continue;

                $saleQty += $qty;

                $product = Product::find($pid);
                
                // Fetch actual purchase price
                $latestPurchase = DB::table('purchase_items')
                    ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                    ->where('purchase_items.product_id', $pid)
                    ->whereDate('purchases.purchase_date', '<=', \Carbon\Carbon::parse($sale->created_at)->toDateString())
                    ->orderBy('purchases.purchase_date', 'desc')
                    ->orderBy('purchase_items.id', 'desc')
                    ->value('purchase_items.price');
                
                if ($latestPurchase) {
                    $purchaseCost = (float) $latestPurchase;
                } else {
                    $purchaseCost = 0;
                }

                $availableForCosting = ($boughtMap[$pid] ?? 0) - ($soldMap[$pid] ?? 0);
                
                if ($availableForCosting > 0) {
                    $qtyWithCost = min($qty, $availableForCosting);
                    $qtyFree = $qty - $qtyWithCost;
                } else {
                    $qtyWithCost = 0;
                    $qtyFree = $qty;
                }
                
                if (!isset($soldMap[$pid])) $soldMap[$pid] = 0;
                $soldMap[$pid] += $qty;

                $itemProfit = ($price - $purchaseCost) * $qtyWithCost + ($price - 0) * $qtyFree;
                $saleProfit += $itemProfit;
            }

            // Apply global discounts/extra costs from the sale to the profit (simplified attribution)
            $extraDiscount = (float)$sale->total_extradiscount;
            $labourCharges = (float)($sale->labour_charges ?? 0);

            $netSaleProfit = $saleProfit - $extraDiscount + $labourCharges;

            $customerWiseData[$customerName]['total_sales'] += (float)$sale->total_bill_amount;
            $customerWiseData[$customerName]['total_profit'] += $netSaleProfit;
            $customerWiseData[$customerName]['total_items_qty'] += $saleQty;
        }

        // Consider returns to subtract from the profit and sales
        $returns = SalesReturn::whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();

        foreach ($returns as $ret) {
            $customerId = $ret->customer;
            $customerName = 'Unknown';
            if (is_numeric($customerId)) {
                $customer = \App\Models\Customer::find($customerId);
                $customerName = $customer ? $customer->customer_name : 'Walk-in / Unknown';
            } else {
                $customerName = $customerId ?: 'Walk-in';
            }

            if (!isset($customerWiseData[$customerName])) {
                $customerWiseData[$customerName] = [
                    'total_sales' => 0,
                    'total_profit' => 0,
                    'total_items_qty' => 0
                ];
            }

            $returnLoss = 0;
            $returnQty = 0;

            $productNames = explode(',', $ret->product);
            $qtys         = explode(',', $ret->qty);
            $prices       = explode(',', $ret->per_price);

            foreach ($productNames as $index => $pname) {
                $qty   = (float)($qtys[$index] ?? 0);
                $price = (float)($prices[$index] ?? 0);
                if ($qty <= 0) continue;

                $returnQty += $qty;

                $product = Product::where('item_name', $pname)->first();
                
                if ($product) {
                    $latestPurchase = DB::table('purchase_items')
                        ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                        ->where('purchase_items.product_id', $product->id)
                        ->whereDate('purchases.purchase_date', '<=', \Carbon\Carbon::parse($ret->created_at)->toDateString())
                        ->orderBy('purchases.purchase_date', 'desc')
                        ->orderBy('purchase_items.id', 'desc')
                        ->value('purchase_items.price');
                        
                    $cost = $latestPurchase ? (float) $latestPurchase : 0;
                } else {
                    $cost = 0;
                }

                $itemLoss = ($price - $cost) * $qty;
                $returnLoss += $itemLoss;
            }

            $customerWiseData[$customerName]['total_sales'] -= (float)$ret->total_bill_amount;
            $customerWiseData[$customerName]['total_profit'] -= $returnLoss;
            $customerWiseData[$customerName]['total_items_qty'] -= $returnQty;
        }

        return view('admin_panel.reporting.customer_wise_profit', compact(
            'startDate', 'endDate', 'customerWiseData'
        ));
    }

    public function party_wise_sale(Request $request)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;
        $customerId = $request->customer_id;

        if (!$startDate || !$endDate) {
            $startDate = \Carbon\Carbon::now()->startOfMonth()->toDateString();
            $endDate   = \Carbon\Carbon::now()->endOfMonth()->toDateString();
        }

        $query = \App\Models\Sale::whereBetween(\Illuminate\Support\Facades\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        
        if ($customerId && $customerId !== 'all') {
            $query->where('customer', $customerId);
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        $reportData = [];

        foreach ($sales as $sale) {
            $cId = $sale->customer;
            $customerName = 'Unknown';
            if (is_numeric($cId)) {
                $customer = \App\Models\Customer::find($cId);
                $customerName = $customer ? $customer->customer_name : 'Walk-in / Unknown';
            } else {
                $customerName = $cId ?: 'Walk-in';
            }

            $productIds = explode(',', $sale->product);
            $qtys       = explode(',', $sale->qty);
            $prices     = explode(',', $sale->per_price);
            $totals     = explode(',', $sale->per_total);

            foreach ($productIds as $index => $pid) {
                $qty   = (float)($qtys[$index] ?? 0);
                if ($qty <= 0) continue;
                $price = (float)($prices[$index] ?? 0);
                $total = (float)($totals[$index] ?? 0);
                
                $product = \App\Models\Product::find($pid);
                $productName = $product ? $product->item_name : "Unknown Item (ID: $pid)";

                // Group by customer, then by product
                if (!isset($reportData[$customerName])) {
                    $reportData[$customerName] = [];
                }

                if (!isset($reportData[$customerName][$productName])) {
                    $reportData[$customerName][$productName] = [
                        'qty' => 0,
                        'total_amount' => 0
                    ];
                }

                $reportData[$customerName][$productName]['qty'] += $qty;
                $reportData[$customerName][$productName]['total_amount'] += $total;
            }
        }

        $allCustomers = \App\Models\Customer::all();

        return view('admin_panel.reporting.party_wise_sale', compact('startDate', 'endDate', 'customerId', 'allCustomers', 'reportData'));
    }

    public function expense_report(Request $request)
    {
        $start = $request->start_date ?? date('Y-m-d');
        $end   = $request->end_date ?? date('Y-m-d');

        $expenses = \App\Models\ExpenseVoucher::whereBetween('entry_date', [$start, $end])
            ->orderBy('id', 'desc')
            ->get();

        foreach ($expenses as $v) {
            $accounts = json_decode($v->row_account_id, true) ?? [];
            $amounts  = json_decode($v->amount, true) ?? [];
            
            $details = [];
            foreach ($accounts as $idx => $accId) {
                $category = DB::table('expense_categories')->where('id', $accId)->value('title');
                $amt = $amounts[$idx] ?? 0;
                $details[] = ($category ?? 'Unknown') . ': ' . number_format($amt, 2);
            }
            $v->category_details = implode(', ', $details);

            // Source Account
            $v->source_account = DB::table('accounts')->where('id', $v->party_id)->value('title') ?? 'Unknown';
        }

        if ($request->ajax()) {
            return response()->json($expenses);
        }

        return view('admin_panel.reporting.expense_report', compact('expenses', 'start', 'end'));
    }
}
