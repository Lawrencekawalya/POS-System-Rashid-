<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleRefund;
use Carbon\Carbon;

class ZReportService
{
    /**
     * Summarise sales and the cash drawer for a period.
     *
     * Sales figures are based on when the sale/refund happened. Cash figures
     * are based on the payments ledger, so room settlements count on the day the
     * money was received and refunds only count what was actually paid back.
     */
    public function summaryForPeriod($startDate, $endDate): array
    {
        $from = Carbon::parse($startDate)->startOfDay();
        $to = Carbon::parse($endDate)->endOfDay();

        $sales = Sale::whereBetween('created_at', [$from, $to])->get();
        $grossSales = (float) $sales->sum('total_amount');
        $refundTotal = (float) SaleRefund::whereBetween('created_at', [$from, $to])->sum('amount');

        $cashPayments = Payment::whereBetween('created_at', [$from, $to])->where('method', 'cash');
        $cashReceived = (float) (clone $cashPayments)->where('amount', '>', 0)->sum('amount');
        $cashRefunded = (float) -(clone $cashPayments)->where('amount', '<', 0)->sum('amount');

        // Only confirmed expenses: pending ones are unverified and rejected ones
        // mean the money should still be in the drawer.
        $expenses = Expense::with('user')
            ->whereBetween('expense_date', [$from, $to])
            ->where('status', 'confirmed')
            ->get();

        $totalExpenses = (float) $expenses->sum('amount');
        $cashExpenses = (float) $expenses->where('payment_method', 'Cash')->sum('amount');

        return [
            'grossSales' => $grossSales,
            'refundTotal' => $refundTotal,
            'netSales' => $grossSales - $refundTotal,
            'cashReceived' => $cashReceived,
            'cashRefunded' => $cashRefunded,
            'cashExpenses' => $cashExpenses,
            'totalExpenses' => $totalExpenses,
            'cashExpected' => $cashReceived - $cashRefunded - $cashExpenses,
            'sales' => $sales,
            'expenses' => $expenses,
        ];
    }

    public function summaryForDate($date): array
    {
        return $this->summaryForPeriod($date, $date);
    }
}
