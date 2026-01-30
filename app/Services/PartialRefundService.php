<?php

namespace App\Services;

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
        array $items, // product_id => quantity
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

            foreach ($items as $productId => $qty) {
                if ($qty <= 0)
                    continue;

                $saleItem = $sale->items->firstWhere('product_id', $productId);

                if (!$saleItem) {
                    throw ValidationException::withMessages([
                        'refund' => 'Invalid product for this sale.',
                    ]);
                }

                $alreadyRefunded = $sale->refundedQuantityFor($productId);
                $remaining = $saleItem->quantity - $alreadyRefunded;

                if ($qty > $remaining) {
                    throw ValidationException::withMessages([
                        'refund' => 'Refund quantity exceeds remaining quantity.',
                    ]);
                }

                $amount = $qty * $saleItem->unit_price;

                // Record refund
                SaleRefund::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'amount' => $amount,
                    'refunded_by' => $userId,
                    'reason' => $reason,
                ]);

                // Restore stock
                StockMovement::create([
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'type' => 'refund',
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'remarks' => 'Partial refund of sale #' . $sale->id,
                ]);
            }

            // Mark sale fully refunded if applicable
            if ($sale->isFullyRefunded()) {
                $sale->update([
                    'refunded_at' => now(),
                    'refunded_by' => $userId,
                    'refund_reason' => $reason,
                ]);
            }
        });
    }
}
