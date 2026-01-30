<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function refund(Sale $sale, int $userId, string $reason): void
    {
        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'refund_reason' => 'Refund reason is required.',
            ]);
        }

        DB::transaction(function () use ($sale, $userId, $reason) {

            $sale = Sale::where('id', $sale->id)
                ->lockForUpdate()
                ->first();

            if ($sale->isRefunded()) {
                throw ValidationException::withMessages([
                    'sale' => 'This sale has already been refunded.',
                ]);
            }

            $sale->load('items');

            foreach ($sale->items as $item) {
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'type' => 'refund',
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'remarks' => 'Refund of sale #' . $sale->id,
                ]);
            }

            $sale->update([
                'refunded_at' => now(),
                'refunded_by' => $userId,
                'refund_reason' => $reason,
            ]);
        });
    }
}
