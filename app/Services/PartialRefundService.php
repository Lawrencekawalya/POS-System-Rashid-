<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleRefund;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PartialRefundService
{
    public function refund(
        Sale $sale,
        int $userId,
        array $items, // sale_item_id => quantity
        string $reason
    ): void {
        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'refund_reason' => 'Refund reason is required.',
            ]);
        }

        DB::transaction(function () use ($sale, $userId, $items, $reason) {

            $sale = Sale::where('id', $sale->id)
                ->lockForUpdate()
                ->first();

            $sale->load('items', 'refunds');
            $refundedAmount = (float) $sale->refunded_amount;

            foreach ($items as $saleItemId => $qty) {
                if ($qty <= 0) {
                    continue;
                }

                $saleItem = $sale->items->firstWhere('id', (int) $saleItemId);

                if (! $saleItem) {
                    throw ValidationException::withMessages([
                        'refund' => 'Invalid product for this sale.',
                    ]);
                }

                $alreadyRefunded = $sale->refundedQuantityForSaleItem($saleItem->id);
                $remaining = $saleItem->quantity - $alreadyRefunded;

                if ($qty > $remaining) {
                    throw ValidationException::withMessages([
                        'refund' => 'Refund quantity exceeds remaining quantity.',
                    ]);
                }

                $amount = $qty * $saleItem->unit_price;
                $refundedAmount += $amount;

                // Record refund
                SaleRefund::create([
                    'sale_id' => $sale->id,
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id ?? $saleItem->stock_product_id,
                    'quantity' => $qty,
                    'amount' => $amount,
                    'refunded_by' => $userId,
                    'reason' => $reason,
                ]);

                if ($saleItem->product_id) {
                    StockMovement::create([
                        'product_id' => $saleItem->product_id,
                        'quantity' => $qty,
                        'type' => 'refund',
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'remarks' => 'Refund of sale #'.$sale->id,
                    ]);
                }

                if ($saleItem->stock_product_id) {
                    StockMovement::create([
                        'product_id' => $saleItem->stock_product_id,
                        'quantity' => $saleItem->stock_quantity * $qty,
                        'type' => 'refund',
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'remarks' => 'Fresh-cut portions restored for refund of sale #'.$sale->id,
                    ]);
                }
            }

            $this->recordPayout($sale, $userId, $refundedAmount);

            // Mark sale fully refunded if applicable
            if ($sale->isFullyRefunded()) {
                $sale->update([
                    'refunded_amount' => $refundedAmount,
                    'refunded_at' => now(),
                    'refunded_by' => $userId,
                    'refund_reason' => $reason,
                    'payment_status' => 'refunded',
                ]);
            } else {
                $remainingDue = (float) $sale->total_amount - (float) $sale->paid_amount - $refundedAmount;

                $sale->update([
                    'refunded_amount' => $refundedAmount,
                    'payment_status' => $remainingDue <= 0 ? 'paid' : ((float) $sale->paid_amount > 0 ? 'partial' : 'unpaid'),
                ]);
            }
        });
    }

    /**
     * Pay back money the customer no longer owes.
     *
     * The customer only gets back what they paid beyond the value of the items
     * they keep, so refunding an unpaid room charge pays nothing out. Payouts
     * are recorded as negative payments, using the methods the customer paid
     * with, most recent first.
     */
    private function recordPayout(Sale $sale, int $userId, float $refundedAmount): void
    {
        $payments = $sale->payments()->orderByDesc('id')->get();
        $amountDue = (float) $sale->total_amount - $refundedAmount;
        $payout = round((float) $payments->sum('amount') - $amountDue, 2);

        if ($payout <= 0) {
            return;
        }

        $netByMethod = $payments->groupBy('method')->map(fn ($group) => (float) $group->sum('amount'));

        foreach ($payments->pluck('method')->unique() as $method) {
            $allocation = min($payout, $netByMethod[$method]);

            if ($allocation <= 0) {
                continue;
            }

            Payment::create([
                'room_id' => $sale->room_id,
                'sale_id' => $sale->id,
                'user_id' => $userId,
                'amount' => -$allocation,
                'method' => $method,
                'remarks' => 'Refund payout for sale #'.$sale->id,
            ]);

            $payout = round($payout - $allocation, 2);

            if ($payout <= 0) {
                break;
            }
        }
    }
}
