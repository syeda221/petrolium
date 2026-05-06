<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usertype = Auth::user()->usertype;
        $userId = Auth::id();

        if ($usertype == 'user') {
            return view('user_panel.dashboard', compact('userId'));
        } elseif ($usertype == 'admin') {

            return view('admin_panel.dashboard');
        } else {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
    }


    public function System_Reports(Request $request)
    {
        $categoryCount = DB::table('categories')->count();
        $subcategoryCount = DB::table('subcategories')->count();
        $productCount = DB::table('products')->count();
        $customerscount = DB::table('customers')->count();

        $totalPurchases = DB::table('purchases')->sum('net_amount');
        $totalPurchaseReturns = DB::table('purchase_returns')->sum('net_amount');
        $totalSales = DB::table('sales')->sum('total_net');
        $totalSalesReturns = DB::table('sales_returns')->sum('total_net');

        $today = Carbon::today();


        // ===== SALES REPORT CHARTS =====
        // ===== Daily (Last 7 Days) =====
        $dailyStart = $today->copy()->subDays(6);

        $dailyData = DB::table('sales')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_net) as total')
            )
            ->whereDate('created_at', '>=', $dailyStart)
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        // Fill missing dates with 0
        $dailyLabels = collect(range(6, 0))->map(fn($i) => $today->copy()->subDays($i)->format('Y-m-d'));
        $dailyData = $dailyLabels->map(fn($date) => $dailyData->get($date, 0));

        // ===== Weekly (This + Last 2 Weeks) =====
        $weeks = collect([2, 1, 0])->map(function ($i) use ($today) {
            $start = $today->copy()->startOfWeek()->subWeeks($i);
            $end = $start->copy()->endOfWeek();
            return ['start' => $start, 'end' => $end];
        });

        $weeklyTotals = DB::table('sales')
            ->select(
                DB::raw('WEEK(created_at,1) as week'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(total_net) as total')
            )
            ->where('created_at', '>=', $weeks->last()['start'])
            ->groupBy('year', 'week')
            ->pluck('total', 'week');

        $weeklyData = $weeks->map(function ($w) use ($weeklyTotals) {
            $weekNum = $w['start']->weekOfYear;
            return $weeklyTotals->get($weekNum, 0);
        });
        $weeklyLabels = ['2 Weeks Ago', 'Last Week', 'This Week'];

        // ===== Monthly (Jan → Current Month) =====
        $currentYear = $today->year;
        $months = range(1, $today->month);

        $monthlyTotals = DB::table('sales')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_net) as total')
            )
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthlyData = collect($months)->map(fn($m) => $monthlyTotals->get($m, 0));
        $monthLabels = collect($months)->map(fn($m) => Carbon::create()->month($m)->format('F'));

        // ===== Final Array =====
        $salesChartStats = [
            'daily' => [
                'categories' => $dailyLabels,
                'series' => [
                    ['name' => 'Sales', 'data' => $dailyData]
                ]
            ],
            'weekly' => [
                'categories' => $weeklyLabels,
                'series' => [
                    ['name' => 'Sales', 'data' => $weeklyData]
                ]
            ],
            'monthly' => [
                'categories' => $monthLabels,
                'series' => [
                    ['name' => 'Sales', 'data' => $monthlyData]
                ]
            ]
        ];
        // ===== PURCHASE CHARTS =====
        // DAILY
        $purchaseDailyLabels = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i)->format('Y-m-d'));
        $purchaseDailySeries = [[
            'name' => 'Purchases',
            'data' => $purchaseDailyLabels->map(function ($date) {
                return DB::table('purchases')
                    ->whereDate('created_at', $date)
                    ->sum('net_amount');
            })
        ]];

        // WEEKLY
        $purchaseWeeklyLabels = ['This Week', 'Last Week', '2 Weeks Ago'];
        $purchaseWeeklySeries = [[
            'name' => 'Purchases',
            'data' => collect([0, 1, 2])->map(function ($i) {
                $start = Carbon::now()->startOfWeek()->subWeeks($i);
                $end = $start->copy()->endOfWeek();
                return DB::table('purchases')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('net_amount');
            })->reverse()->values()
        ]];

        // MONTHLY
        $months = range(1, Carbon::now()->month);
        $purchaseMonthLabels = collect($months)->map(fn($m) => Carbon::create()->month($m)->format('F'));
        $purchaseMonthlySeries = [[
            'name' => 'Purchases',
            'data' => collect($months)->map(function ($month) {
                return DB::table('purchases')
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->sum('net_amount');
            })
        ]];

        $purchaseChartStats = [
            'daily' => [
                'categories' => $purchaseDailyLabels,
                'series' => $purchaseDailySeries
            ],
            'weekly' => [
                'categories' => $purchaseWeeklyLabels,
                'series' => $purchaseWeeklySeries
            ],
            'monthly' => [
                'categories' => $purchaseMonthLabels,
                'series' => $purchaseMonthlySeries
            ]
        ];
        $categoryProductData = DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->select(
                'categories.id',
                'categories.name as category_name',
                DB::raw('COUNT(products.id) as total_products')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_products')
            ->get();

        $categoryProductChart = [
            'categories'   => $categoryProductData->pluck('category_name'),
            'category_ids' => $categoryProductData->pluck('id'), // click ke liye
            'series' => [
                [
                    'name' => 'Total Products',
                    'data' => $categoryProductData->pluck('total_products')
                ]
            ]
        ];

        // ===== LOW STOCK CHART =====
        $lowStockData = DB::table('products')
            ->leftJoin('stocks', 'products.id', '=', 'stocks.product_id')
            ->select(
                'products.id',
                'products.item_code',
                'products.item_name',
                DB::raw('COALESCE(stocks.qty, 0) as qty'),
                'products.alert_quantity'
            )
            ->whereRaw('COALESCE(stocks.qty, 0) <= products.alert_quantity')
            ->get();
        $lowStockChart = [
            'categories' => $lowStockData->pluck('item_name'),
            'series' => [
                ['name' => 'Current Stock', 'data' => $lowStockData->pluck('qty')],
                ['name' => 'Alert Level', 'data' => $lowStockData->pluck('alert_quantity')],
            ]
        ];


        // ===== CATEGORY & SUBCATEGORY CHART =====
        $categorySubData = DB::table('categories')
            ->leftJoin('subcategories', 'categories.id', '=', 'subcategories.category_id')
            ->leftJoin('products', 'subcategories.id', '=', 'products.sub_category_id')
            ->select(
                'categories.name as category_name',
                DB::raw('COUNT(DISTINCT subcategories.id) as sub_count'),
                DB::raw('COUNT(DISTINCT products.id) as product_count')
            )
            ->groupBy('categories.name')
            ->get();

        $categorySubChart = [
            'categories' => $categorySubData->pluck('category_name'),
            'series' => [
                ['name' => 'Subcategories', 'data' => $categorySubData->pluck('sub_count')],
                ['name' => 'Products', 'data' => $categorySubData->pluck('product_count')],
            ]
        ];

        // ===== WAREHOUSE STOCK VALUE REPORT =====
        $warehouseStockData = DB::table('warehouses')
            ->leftJoin('warehouse_stocks', 'warehouses.id', '=', 'warehouse_stocks.warehouse_id')
            ->leftJoin('products', 'warehouse_stocks.product_id', '=', 'products.id')
            ->select(
                'warehouses.id',
                'warehouses.warehouse_name',
                DB::raw('SUM(COALESCE(warehouse_stocks.quantity, 0)) as total_quantity'),
                DB::raw('SUM(COALESCE(warehouse_stocks.quantity, 0) * COALESCE(CAST(products.price as DECIMAL(15,2)), 0)) as total_value')
            )
            ->groupBy('warehouses.id', 'warehouses.warehouse_name')
            ->orderBy('warehouses.warehouse_name')
            ->get();

        $warehouseStockChart = [
            'warehouses' => $warehouseStockData->pluck('warehouse_name'),
            'quantities' => $warehouseStockData->pluck('total_quantity'),
            'values' => $warehouseStockData->pluck('total_value'),
            'series' => [
                ['name' => 'Stock Quantity', 'data' => $warehouseStockData->pluck('total_quantity')],
                ['name' => 'Stock Value (PKR)', 'data' => $warehouseStockData->pluck('total_value')],
            ]
        ];

        return view('admin_panel.system_reports', compact(
            'categoryCount',
            'subcategoryCount',
            'productCount',
            'customerscount',
            'totalPurchases',
            'totalPurchaseReturns',
            'totalSales',
            'totalSalesReturns',
            'salesChartStats',
            'purchaseChartStats',
            'categoryProductChart',
            'lowStockChart',
            'categorySubChart',
            'warehouseStockData',
            'warehouseStockChart'
        ));
    }


    public function categoryProducts(Request $request, $id)
    {
        $search = $request->search;

        $products = DB::table('products')
            ->leftJoin('stocks', 'products.id', '=', 'stocks.product_id')
            ->select(
                'products.id',
                'products.item_name',
                DB::raw('COALESCE(SUM(stocks.qty),0) as stock')
            )
            ->where('products.category_id', $id)
            ->when($search, function ($q) use ($search) {
                $q->where('products.item_name', 'like', "%{$search}%");
            })
            ->groupBy('products.id', 'products.item_name')
            ->orderByDesc('stock')
            ->paginate(100);

        return response()->json($products);
    }
}
