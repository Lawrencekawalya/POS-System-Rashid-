<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TurnoverReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ?? now()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $from = Carbon::parse($startDate)->startOfDay();
        $to = Carbon::parse($endDate)->endOfDay();

        $products = Product::orderBy('name')->get()->map(function ($product) use ($from, $to) {

            // Opening stock
            $openingStock = DB::table('stock_movements')
                ->where('product_id', $product->id)
                ->where('created_at', '<', $from)
                ->sum('quantity');

            // Closing stock
            $closingStock = DB::table('stock_movements')
                ->where('product_id', $product->id)
                ->where('created_at', '<=', $to)
                ->sum('quantity');

            $avgStock = ($openingStock + $closingStock) / 2;

            // Quantity sold in period
            $qtySold = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sale_items.product_id', $product->id)
                ->whereBetween('sales.created_at', [$from, $to])
                ->sum('sale_items.quantity');

            $turnover = $avgStock > 0 ? $qtySold / $avgStock : 0;

            return [
                'product' => $product,
                'qty_sold' => (int) $qtySold,
                'avg_stock' => round($avgStock, 2),
                'turnover' => round($turnover, 2),
            ];
        });

        return view('reports.turnover', compact(
            'products',
            'startDate',
            'endDate'
        ));
    }
}
