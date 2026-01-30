<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProfitabilityReportController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get()->map(function ($product) {

            // Revenue & quantity sold
            $salesData = DB::table('sale_items')
                ->where('product_id', $product->id)
                ->selectRaw('
                    SUM(quantity) as qty_sold,
                    SUM(subtotal) as revenue
                ')
                ->first();

            $qtySold = (int) ($salesData->qty_sold ?? 0);
            $revenue = (float) ($salesData->revenue ?? 0);

            // Last purchase cost
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
        });

        return view('reports.profitability', compact('products'));
    }
}
