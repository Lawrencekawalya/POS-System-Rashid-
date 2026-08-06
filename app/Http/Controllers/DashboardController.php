<?php

// namespace App\Http\Controllers;

// use App\Models\Sale;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\DB;
// use App\Models\Product;

// class DashboardController extends Controller
// {
//     // public function index()
//     // {
//     //     $today = now()->toDateString();
//     //     $userId = Auth::id();

//     //     // Sales today (all, including refunded ones)
//     //     $salesToday = Sale::whereDate('created_at', $today)->get();

//     //     // Gross sales
//     //     $grossSales = $salesToday->sum('total_amount');

//     //     // Refund total (value-based, not quantity-based)
//     //     $refundTotal = DB::table('stock_movements')
//     //         ->join('sale_items', function ($join) {
//     //             $join->on('sale_items.sale_id', '=', 'stock_movements.reference_id')
//     //                 ->on('sale_items.product_id', '=', 'stock_movements.product_id');
//     //         })
//     //         ->where('stock_movements.type', 'refund')
//     //         ->whereDate('stock_movements.created_at', $today)
//     //         ->sum(DB::raw('stock_movements.quantity * sale_items.unit_price'));

//     //     // Net sales
//     //     $netSales = $grossSales - $refundTotal;

//     //     // Cash actually received (paid - change)
//     //     $cashReceived = $salesToday->sum(
//     //         fn($sale) =>
//     //         $sale->paid_amount - $sale->change_amount
//     //     );

//     //     // Cash expected in drawer
//     //     $cashExpected = $cashReceived - $refundTotal;

//     //     // Refund count
//     //     // $refundCount = Sale::whereDate('refunded_at', $today)->count();
//     //     $refundCount = DB::table('stock_movements')
//     //         ->where('type', 'refund')
//     //         ->whereDate('created_at', $today)
//     //         ->count();

//     //     // Recent sales (activity feed)
//     //     $recentSales = Sale::with('user')
//     //         ->whereDate('created_at', $today)
//     //         ->latest()
//     //         ->take(10)
//     //         ->get();

//     //     return view('dashboard', compact(
//     //         'grossSales',
//     //         'refundTotal',
//     //         'netSales',
//     //         'cashExpected',
//     //         'refundCount',
//     //         'recentSales'
//     //     ));
//     // }

//     // public function index()
//     // {
//     //     $today = now()->toDateString();

//     //     // 1. Cumulative Revenue (Total lifetime sales)
//     //     $cumulativeRevenue = Sale::sum('total_amount');

//     //     // 2. Today's Sales Data
//     //     $salesToday = Sale::whereDate('created_at', $today)->get();

//     //     // Gross sales
//     //     $grossSales = $salesToday->sum('total_amount');

//     //     // Refund total (value-based)
//     //     $refundTotal = DB::table('stock_movements')
//     //         ->join('sale_items', function ($join) {
//     //             $join->on('sale_items.sale_id', '=', 'stock_movements.reference_id')
//     //                 ->on('sale_items.product_id', '=', 'stock_movements.product_id');
//     //         })
//     //         ->where('stock_movements.type', 'refund')
//     //         ->whereDate('stock_movements.created_at', $today)
//     //         ->sum(DB::raw('stock_movements.quantity * sale_items.unit_price'));

//     //     // Net sales
//     //     $netSales = $grossSales - $refundTotal;

//     //     // Cash received logic
//     //     $cashReceived = $salesToday->sum(
//     //         fn($sale) => $sale->paid_amount - $sale->change_amount
//     //     );

//     //     $cashExpected = $cashReceived - $refundTotal;

//     //     $refundCount = DB::table('stock_movements')
//     //         ->where('type', 'refund')
//     //         ->whereDate('created_at', $today)
//     //         ->count();

//     //     // 3. Low Stock Logic
//     //     // We fetch active products and filter by those whose currentStock() is 5 or less.
//     //     // We take the top 5 most urgent ones to keep the dashboard clean.
//     //     $lowStockProducts = Product::where('is_active', true)
//     //         ->get()
//     //         ->filter(function ($product) {
//     //             return $product->currentStock() <= 5;
//     //         })
//     //         ->take(5);

//     //     // 4. Activity Feed (Newest first)
//     //     $recentSales = Sale::with('user')
//     //         ->whereDate('created_at', $today)
//     //         ->latest()
//     //         ->take(10)
//     //         ->get();

//     //     // 5. Top 5 Best Selling Products Today
//     //     $topProducts = DB::table('sale_items')
//     //         ->join('products', 'sale_items.product_id', '=', 'products.id')
//     //         ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
//     //         ->select(
//     //             'products.name',
//     //             DB::raw('SUM(sale_items.quantity) as total_qty'),
//     //             DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as total_revenue')
//     //         )
//     //         ->whereDate('sales.created_at', $today)
//     //         ->groupBy('products.id', 'products.name')
//     //         ->orderByDesc('total_qty')
//     //         ->take(5)
//     //         ->get();

//     //     return view('dashboard', compact(
//     //         'cumulativeRevenue', 
//     //         'grossSales',
//     //         'refundTotal',
//     //         'netSales',
//     //         'cashExpected',
//     //         'refundCount',
//     //         'recentSales',
//     //         'lowStockProducts', 
//     //         'topProducts'
//     //     ));
//     // }
//     public function index()
//     {
//         $today = now()->toDateString();

//         // 1. Stats
//         $cumulativeRevenue = Sale::sum('total_amount');
//         $salesToday = Sale::whereDate('created_at', $today)->get();
//         $grossSales = $salesToday->sum('total_amount');

//         $refundTotal = DB::table('stock_movements')
//             ->join('sale_items', function ($join) {
//                 $join->on('sale_items.sale_id', '=', 'stock_movements.reference_id')
//                     ->on('sale_items.product_id', '=', 'stock_movements.product_id');
//             })
//             ->where('stock_movements.type', 'refund')
//             ->whereDate('stock_movements.created_at', $today)
//             ->sum(DB::raw('stock_movements.quantity * sale_items.unit_price'));

//         $netSales = $grossSales - $refundTotal;
//         $cashReceived = $salesToday->sum(fn($sale) => $sale->paid_amount - $sale->change_amount);
//         $cashExpected = $cashReceived - $refundTotal;

//         $refundCount = DB::table('stock_movements')
//             ->where('type', 'refund')
//             ->whereDate('created_at', $today)
//             ->count();

//         // 2. FIXED: Low Stock (Using withSum to prevent N+1 queries)
//         // $lowStockProducts = Product::where('is_active', true)
//         //     ->withSum('stockMovements as total_stock', 'quantity')
//         //     ->get()
//         //     ->filter(function ($product) {
//         //         return ($product->total_stock ?? 0) <= 5;
//         //     })
//         //     ->take(5)
//         //     ->values();
//         $lowStockProducts = Product::where('is_active', true)
//             ->get()
//             ->map(function ($product) {
//                 return [
//                     'name' => $product->name,
//                     'stock' => $product->currentStock(),
//                 ];
//             })
//             ->filter(fn($p) => $p['stock'] <= 5)
//             ->take(5)
//             ->values();

//         // dd($lowStockProducts);

//         // 3. Activity Feed
//         // $recentSales = Sale::with('user')
//         //     ->whereDate('created_at', $today)
//         //     ->latest()
//         //     ->take(50)
//         //     ->get();
//         $recentSales = Sale::with('user')
//             ->latest()
//             ->paginate(20);

//         // 4. FIXED: Top Products (Forcing into a predictable format)
//         $topProducts = DB::table('sale_items')
//             ->join('products', 'sale_items.product_id', '=', 'products.id')
//             ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
//             ->select(
//                 'products.name',
//                 DB::raw('SUM(sale_items.quantity) as total_qty'),
//                 DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as total_revenue')
//             )
//             ->whereDate('sales.created_at', $today)
//             ->groupBy('products.id', 'products.name')
//             ->orderByDesc('total_qty')
//             ->take(5)
//             ->get();
//         // ->toArray(); // Converting to array to use array syntax in Blade consistently

//         return view('dashboard', compact(
//             'cumulativeRevenue',
//             'grossSales',
//             'refundTotal',
//             'netSales',
//             'cashExpected',
//             'refundCount',
//             'recentSales',
//             'lowStockProducts',
//             'topProducts'
//         ));
//     }
// }

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Expense; // Import the Expense model
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        // 1. STATS & REVENUE LOGIC
        
        // Cumulative Revenue = All Sales - All CONFIRMED Expenses
        $totalSalesAllTime = Sale::sum('total_amount');
        $totalConfirmedExpenses = Expense::where('status', 'confirmed')->sum('amount');
        $cumulativeRevenue = $totalSalesAllTime - $totalConfirmedExpenses;

        $salesToday = Sale::whereDate('created_at', $today)->get();
        $grossSales = $salesToday->sum('total_amount');

        // Refund total (value-based)
        $refundTotal = DB::table('stock_movements')
            ->join('sale_items', function ($join) {
                $join->on('sale_items.sale_id', '=', 'stock_movements.reference_id')
                    ->on('sale_items.product_id', '=', 'stock_movements.product_id');
            })
            ->where('stock_movements.type', 'refund')
            ->whereDate('stock_movements.created_at', $today)
            ->sum(DB::raw('stock_movements.quantity * sale_items.unit_price'));

        $netSales = $grossSales - $refundTotal;
        
        // Cash Received today (Total paid - change)
        $cashReceivedToday = $salesToday->sum(fn($sale) => $sale->paid_amount - $sale->change_amount);

        // 2. CASH EXPECTED LOGIC
        // Today's Cash Expected = (Cash Sales) - (Refunds) - (Expenses recorded today)
        // This helps the admin see what SHOULD be in the drawer right now.
        $expensesToday = Expense::whereDate('expense_date', $today)
            ->where('payment_method', 'Cash')
            ->sum('amount');

        $cashExpected = $cashReceivedToday - $refundTotal - $expensesToday;

        $refundCount = DB::table('stock_movements')
            ->where('type', 'refund')
            ->whereDate('created_at', $today)
            ->count();

        // 3. LOW STOCK LOGIC
        $lowStockProducts = Product::where('is_active', true)
            ->get()
            ->map(function ($product) {
                return [
                    'name' => $product->name,
                    'stock' => $product->currentStock(),
                ];
            })
            ->filter(fn($p) => $p['stock'] <= 5)
            ->take(5)
            ->values();

        // 4. ACTIVITY FEED
        $recentSales = Sale::with('user')
            ->latest()
            ->paginate(20);

        // 5. TOP PRODUCTS
        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as total_revenue')
            )
            ->whereDate('sales.created_at', $today)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'cumulativeRevenue',
            'grossSales',
            'refundTotal',
            'netSales',
            'cashExpected',
            'refundCount',
            'recentSales',
            'lowStockProducts',
            'topProducts',
            'totalConfirmedExpenses' // Sent to view in case you want to display it
        ));
    }
}