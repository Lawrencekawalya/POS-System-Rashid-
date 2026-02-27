<?php

// namespace App\Http\Controllers;

// use App\Models\Product;
// use Illuminate\Support\Facades\DB;

// class ProfitabilityReportController extends Controller
// {
//     public function index()
//     {
//         $products = Product::orderBy('name')->get()->map(function ($product) {

//             // Revenue & quantity sold
//             $salesData = DB::table('sale_items')
//                 ->where('product_id', $product->id)
//                 ->selectRaw('
//                     SUM(quantity) as qty_sold,
//                     SUM(subtotal) as revenue
//                 ')
//                 ->first();

//             $qtySold = (int) ($salesData->qty_sold ?? 0);
//             $revenue = (float) ($salesData->revenue ?? 0);

//             // Last purchase cost
//             $lastCost = DB::table('purchase_items')
//                 ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
//                 ->where('purchase_items.product_id', $product->id)
//                 ->orderByDesc('purchases.purchased_at')
//                 ->value('purchase_items.unit_cost') ?? 0;

//             $cogs = $qtySold * $lastCost;
//             $profit = $revenue - $cogs;
//             $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

//             return [
//                 'product' => $product,
//                 'qty_sold' => $qtySold,
//                 'revenue' => $revenue,
//                 'cogs' => $cogs,
//                 'profit' => $profit,
//                 'margin' => $margin,
//             ];
//         });

//         return view('reports.profitability', compact('products'));
//     }
// }

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request; // Added this
use Illuminate\Support\Facades\DB;

class ProfitabilityReportController extends Controller
{
    public function index(Request $request) // Added Request $request
    {
        // 1. Get dates from the filter or default to the current month
        $start = $request->get('start_date', now()->startOfMonth()->toDateString());
        $end = $request->get('end_date', now()->toDateString());

        $products = Product::all()->map(function ($product) use ($start, $end) {

            // 2. Filter Revenue & quantity sold by the date range
            // We join 'sales' because the date is usually stored there, not in sale_items
            $salesData = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sale_items.product_id', $product->id)
                ->whereBetween('sales.created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->selectRaw('
                    SUM(quantity) as qty_sold,
                    SUM(subtotal) as revenue
                ')
                ->first();

            $qtySold = (int) ($salesData->qty_sold ?? 0);
            $revenue = (float) ($salesData->revenue ?? 0);

            // Last purchase cost (keeping your existing logic)
            $lastCost = DB::table('purchase_items')
                ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                ->where('purchase_items.product_id', $product->id)
                ->orderByDesc('purchases.purchased_at')
                ->value('purchase_items.unit_cost') ?? 0;

            $cogs = $qtySold * $lastCost;
            $profit = $revenue - $cogs;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            return [
                'product' => $product,
                'qty_sold' => $qtySold,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'profit' => $profit,
                'margin' => $margin,
            ];
        })
        ->filter(fn($p) => $p['qty_sold'] > 0) // Only show products that actually sold
        ->sortByDesc('profit'); // Show biggest earners first!

        return view('reports.profitability', compact('products'));
    }
}