<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleRefund;
use Illuminate\Http\Request;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    // public function zReport(Request $request)
    // {
    //     $date = $request->date ?? now()->toDateString();

    //     $sales = Sale::whereDate('created_at', $date)->get();

    //     $refunds = SaleRefund::whereDate('created_at', $date)->get();

    //     $grossSales = $sales->sum('total_amount');
    //     $refundTotal = $refunds->sum('amount');
    //     $netSales = $grossSales - $refundTotal;

    //     $cashReceived = $sales->sum('paid_amount') - $refundTotal;

    //     return view('reports.z-report', compact(
    //         'date',
    //         'grossSales',
    //         'refundTotal',
    //         'netSales',
    //         'cashReceived',
    //         'sales',
    //         'refunds'
    //     ));
    // }
    public function zReport(Request $request)
    {
        $startDate = $request->start_date ?? now()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $from = Carbon::parse($startDate)->startOfDay();
        $to = Carbon::parse($endDate)->endOfDay();

        // Sales in period
        $sales = Sale::whereBetween('created_at', [$from, $to])->get();

        // Gross sales (what was sold)
        $grossSales = $sales->sum('total_amount');

        // Refund amount (derived from refund events)
        $refundTotal = DB::table('stock_movements')
            ->join('sale_items', function ($join) {
                $join->on('sale_items.sale_id', '=', 'stock_movements.reference_id')
                    ->on('sale_items.product_id', '=', 'stock_movements.product_id');
            })
            ->where('stock_movements.type', 'refund')
            ->whereBetween('stock_movements.created_at', [$from, $to])
            ->sum(DB::raw('stock_movements.quantity * sale_items.unit_price'));

        // Net sales (real revenue)
        $netSales = $grossSales - $refundTotal;

        // Cash received (actual money collected)
        // $cashReceived = $sales->sum('paid_amount');
        $cashReceived = $sales->sum(function ($sale) {
            return $sale->paid_amount - $sale->change_amount;
        });

        // Cash expected in drawer
        $cashExpected = $cashReceived - $refundTotal;

        return view('reports.z-report', compact(
            'startDate',
            'endDate',
            'grossSales',
            'refundTotal',
            'netSales',
            'cashExpected',
            'cashReceived',
            'sales'
        ));
    }
    // public function zReport(Request $request)
    // {
    //     $startDate = $request->start_date ?? now()->toDateString();
    //     $endDate = $request->end_date ?? now()->toDateString();

    //     $sales = Sale::whereBetween('created_at', [
    //         $startDate . ' 00:00:00',
    //         $endDate . ' 23:59:59',
    //     ])->get();

    //     $refunds = SaleRefund::whereBetween('created_at', [
    //         $startDate . ' 00:00:00',
    //         $endDate . ' 23:59:59',
    //     ])->get();

    //     $grossSales = $sales->sum('total_amount');
    //     $refundTotal = $refunds->sum('amount');
    //     $netSales = $grossSales - $refundTotal;
    //     $cashReceived = $sales->sum('paid_amount') - $refundTotal;

    //     return view('reports.z-report', compact(
    //         'startDate',
    //         'endDate',
    //         'grossSales',
    //         'refundTotal',
    //         'netSales',
    //         'cashReceived',
    //         'sales',
    //         'refunds'
    //     ));
    // }
}
