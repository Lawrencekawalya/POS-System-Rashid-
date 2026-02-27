<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class TurnoverReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Handle Dates: Default to last 30 days so the report isn't empty on load
        $startDate = $request->start_date ?? now()->subDays(30)->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $from = Carbon::parse($startDate)->startOfDay();
        $to = Carbon::parse($endDate)->endOfDay();

        // 2. Calculate data for all products
        // Removed ->with('category') since it doesn't exist
        $data = Product::all()->map(function ($product) use ($from, $to) {

            // Opening stock (Total movements before the start date)
            $openingStock = DB::table('stock_movements')
                ->where('product_id', $product->id)
                ->where('created_at', '<', $from)
                ->sum('quantity');

            // Closing stock (Total movements up to the end date)
            $closingStock = DB::table('stock_movements')
                ->where('product_id', $product->id)
                ->where('created_at', '<=', $to)
                ->sum('quantity');

            $avgStock = ($openingStock + $closingStock) / 2;

            // Quantity sold in the selected period
            $qtySold = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sale_items.product_id', $product->id)
                ->whereBetween('sales.created_at', [$from, $to])
                ->sum('sale_items.quantity');

            // Turnover Ratio = Sales / Average Stock
            $turnover = $avgStock > 0 ? $qtySold / $avgStock : 0;

            return [
                'product'   => $product,
                'qty_sold'  => (int) $qtySold,
                'avg_stock' => round($avgStock, 2),
                'turnover'  => (float) round($turnover, 2),
            ];
        });

        // 3. SORT: Highest turnover (Fast) to Lowest (Dead)
        $sortedData = $data->sortByDesc('turnover');

        // 4. MANUAL PAGINATION (50 items per page)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 50;
        
        // Slice the collection to get only the items for the current page
        $currentPageItems = $sortedData->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $products = new LengthAwarePaginator(
            $currentPageItems, 
            $sortedData->count(), 
            $perPage, 
            $currentPage, 
            [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );

        return view('reports.turnover', compact('products', 'startDate', 'endDate'));
    }
}