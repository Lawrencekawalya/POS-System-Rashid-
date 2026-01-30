<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $userId = Auth::id();

        // Sales today (all, including refunded ones)
        $salesToday = Sale::whereDate('created_at', $today)->get();

        // Gross sales
        $grossSales = $salesToday->sum('total_amount');

        // Refund total (value-based, not quantity-based)
        $refundTotal = DB::table('stock_movements')
            ->join('sale_items', function ($join) {
                $join->on('sale_items.sale_id', '=', 'stock_movements.reference_id')
                    ->on('sale_items.product_id', '=', 'stock_movements.product_id');
            })
            ->where('stock_movements.type', 'refund')
            ->whereDate('stock_movements.created_at', $today)
            ->sum(DB::raw('stock_movements.quantity * sale_items.unit_price'));

        // Net sales
        $netSales = $grossSales - $refundTotal;

        // Cash actually received (paid - change)
        $cashReceived = $salesToday->sum(
            fn($sale) =>
            $sale->paid_amount - $sale->change_amount
        );

        // Cash expected in drawer
        $cashExpected = $cashReceived - $refundTotal;

        // Refund count
        // $refundCount = Sale::whereDate('refunded_at', $today)->count();
        $refundCount = DB::table('stock_movements')
            ->where('type', 'refund')
            ->whereDate('created_at', $today)
            ->count();

        // Recent sales (activity feed)
        $recentSales = Sale::with('user')
            ->whereDate('created_at', $today)
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'grossSales',
            'refundTotal',
            'netSales',
            'cashExpected',
            'refundCount',
            'recentSales'
        ));
    }
}
