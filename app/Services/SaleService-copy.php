<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * Sell a given quantity of a product.
     *
     * @throws ValidationException
     */
    public function sell(Product $product, int $quantity, ?string $remarks = null): void
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be greater than zero.',
            ]);
        }

        DB::transaction(function () use ($product, $quantity, $remarks) {

            $currentStock = $product->currentStock();

            if ($currentStock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock available.',
                ]);
            }

            StockMovement::create([
                'product_id' => $product->id,
                'quantity' => -$quantity,
                'type' => 'sale',
                'remarks' => $remarks,
            ]);
        });
    }
}
