<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    /**
     * Simple stock-in for a single product (kept for manual adjustments).
     */
    public function purchase(Product $product, int $quantity, ?string $remarks = null): void
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be greater than zero.',
            ]);
        }

        DB::transaction(function () use ($product, $quantity, $remarks) {
            StockMovement::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'type' => 'purchase',
                'remarks' => $remarks,
            ]);
        });
    }

    /**
     * Record a full purchase (header + items + stock movements).
     *
     * @param int   $userId
     * @param array $items [ ['product_id' => int, 'quantity' => int, 'unit_cost' => float] ]
     */
    public function recordPurchase(
        int $userId,
        array $items,
        ?string $supplierName = null,
        ?string $referenceNo = null,
        ?string $remarks = null
    ): Purchase {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Purchase must contain at least one item.',
            ]);
        }

        return DB::transaction(function () use ($userId, $items, $supplierName, $referenceNo, $remarks) {
            $totalCost = 0;

            // Validate & calculate
            foreach ($items as $item) {
                if ($item['quantity'] <= 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Quantity must be greater than zero.',
                    ]);
                }

                if ($item['unit_cost'] < 0) {
                    throw ValidationException::withMessages([
                        'unit_cost' => 'Unit cost cannot be negative.',
                    ]);
                }

                $totalCost += $item['quantity'] * $item['unit_cost'];
            }

            // Create purchase header
            $purchase = Purchase::create([
                'user_id' => $userId,
                'supplier_name' => $supplierName,
                'reference_no' => $referenceNo,
                'total_cost' => $totalCost,
                'purchased_at' => now(),
            ]);

            // Create items + stock movements
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $item['quantity'] * $item['unit_cost'],
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'], // stock IN
                    'type' => 'purchase',
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'remarks' => $remarks ?? 'Purchase #' . $purchase->id,
                ]);
            }

            return $purchase;
        });
    }
}
