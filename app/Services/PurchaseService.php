<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    /**
     * Add stock for a given product.
     *
     * @throws ValidationException
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
                'quantity' => $quantity, // positive = stock in
                'type' => 'purchase',
                'remarks' => $remarks,
            ]);
        });
    }
}
