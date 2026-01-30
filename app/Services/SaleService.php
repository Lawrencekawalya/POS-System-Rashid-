<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * Create a sale with multiple items.
     *
     * @param int $userId
     * @param array $items
     * @param float $paidAmount
     * @return Sale
     * @throws ValidationException
     */
    public function createSale(int $userId, array $items, float $paidAmount): Sale
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Sale must contain at least one item.',
            ]);
        }

        return DB::transaction(function () use ($userId, $items, $paidAmount) {

            $totalAmount = 0;
            $products = [];

            // 1️⃣ Load products and validate stock
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Quantity must be greater than zero.',
                    ]);
                }

                if ($product->currentStock() < $quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for {$product->name}.",
                    ]);
                }

                $products[] = [$product, $quantity];
                $totalAmount += $product->selling_price * $quantity;
            }

            if ($paidAmount < $totalAmount) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Paid amount is less than total.',
                ]);
            }

            // 2️⃣ Create Sale (receipt)
            $sale = Sale::create([
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $paidAmount - $totalAmount,
            ]);

            // 3️⃣ Create SaleItems + StockMovements
            foreach ($products as [$product, $quantity]) {

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->selling_price,
                    'subtotal' => $product->selling_price * $quantity,
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'quantity' => -$quantity,
                    'type' => 'sale',
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                ]);
            }

            return $sale;
        });
    }
}
