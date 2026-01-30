<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ZReportService
{
    public function summaryForPeriod($startDate, $endDate, $userId = null): array
    {
        $from = Carbon::parse($startDate)->startOfDay();
        $to = Carbon::parse($endDate)->endOfDay();

        // Sales query
        $salesQuery = Sale::whereBetween('created_at', [$from, $to]);

        if ($userId) {
            $salesQuery->where('user_id', $userId);
        }

        $sales = $salesQuery->get();

        // Gross sales
        $grossSales = $sales->sum('total_amount');

        // Refunds (event-based, quantity-aware)
        $refundTotal = DB::table('stock_movements')
            ->join('sale_items', function ($join) {
                $join->on('sale_items.sale_id', '=', 'stock_movements.reference_id')
                    ->on('sale_items.product_id', '=', 'stock_movements.product_id');
            })
            ->where('stock_movements.type', 'refund')
            ->whereBetween('stock_movements.created_at', [$from, $to])
            ->when($userId, function ($q) use ($userId) {
                $q->whereIn('stock_movements.reference_id', function ($sub) use ($userId) {
                    $sub->select('id')
                        ->from('sales')
                        ->where('user_id', $userId);
                });
            })
            ->sum(DB::raw('stock_movements.quantity * sale_items.unit_price'));

        // Net sales
        $netSales = $grossSales - $refundTotal;

        // Cash actually kept (paid − change)
        $cashReceived = $sales->sum(function ($sale) {
            return $sale->paid_amount - $sale->change_amount;
        });

        // Cash expected in drawer
        $cashExpected = $cashReceived - $refundTotal;

        return [
            'grossSales' => $grossSales,
            'refundTotal' => $refundTotal,
            'netSales' => $netSales,
            'cashReceived' => $cashReceived,
            'cashExpected' => $cashExpected,
            'sales' => $sales,
        ];
    }

    /**
     * Convenience helper for a single day
     */
    public function summaryForDate($date, $userId = null): array
    {
        return $this->summaryForPeriod($date, $date, $userId);
    }

    /**
     * Convenience helper used by cash reconciliation
     */
    public function cashExpectedForDate($date, $userId = null): float
    {
        return $this->summaryForDate($date, $userId)['cashExpected'];
    }
}
