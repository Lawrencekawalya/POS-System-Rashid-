<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Refund payouts are now recorded as negative payments. Record them for refunds
 * made before that change, so cash and revenue figures stay consistent with
 * history. A payout is only what the customer had paid beyond the value of the
 * items they kept; refunds of unpaid (e.g. room) charges pay nothing out.
 */
return new class extends Migration
{
    private const REMARK = 'Refund payout for sale #%d (backfilled)';

    public function up(): void
    {
        $saleIds = DB::table('sale_refunds')->distinct()->pluck('sale_id');

        foreach ($saleIds as $saleId) {
            $sale = DB::table('sales')->find($saleId);

            if (! $sale || DB::table('payments')->where('sale_id', $saleId)->where('amount', '<', 0)->exists()) {
                continue;
            }

            $paid = (float) DB::table('payments')->where('sale_id', $saleId)->sum('amount');
            $refunded = (float) DB::table('sale_refunds')->where('sale_id', $saleId)->sum('amount');
            $payout = round($paid - ((float) $sale->total_amount - $refunded), 2);

            if ($payout <= 0) {
                continue;
            }

            $lastPayment = DB::table('payments')->where('sale_id', $saleId)->orderByDesc('id')->first();
            $lastRefund = DB::table('sale_refunds')->where('sale_id', $saleId)->orderByDesc('id')->first();

            DB::table('payments')->insert([
                'room_id' => $sale->room_id,
                'sale_id' => $saleId,
                'user_id' => $lastRefund->refunded_by ?? $lastPayment->user_id,
                'amount' => -$payout,
                'method' => $lastPayment->method,
                'remarks' => sprintf(self::REMARK, $saleId),
                'created_at' => $lastRefund->created_at,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('payments')
            ->where('amount', '<', 0)
            ->where('remarks', 'like', '%(backfilled)')
            ->delete();
    }
};
