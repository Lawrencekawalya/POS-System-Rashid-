<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    public function index()
    {
        $products = Product::with('stockMovements')
            ->orderBy('name')
            ->get()
            ->map(function ($product) {

                $currentStock = $product->currentStock();

                // Latest purchase cost
                $lastCost = DB::table('purchase_items')
                    ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                    ->where('purchase_items.product_id', $product->id)
                    ->orderByDesc('purchases.purchased_at')
                    ->value('purchase_items.unit_cost');

                $lastCost = $lastCost ?? 0;

                return [
                    'product' => $product,
                    'stock' => $currentStock,
                    'unit_cost' => $lastCost,
                    'value' => $currentStock * $lastCost,
                ];
            });

        $totalValue = $products->sum('value');

        return view('reports.inventory', compact('products', 'totalValue'));
    }
}
