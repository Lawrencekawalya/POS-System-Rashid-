<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\MenuItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * Create a sale with multiple items.
     */
    public function createSale(int $userId, array $items, float $paidAmount, ?int $roomId = null, string $paymentMethod = 'cash'): Sale
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Sale must contain at least one item.',
            ]);
        }

        return DB::transaction(function () use ($userId, $items, $paidAmount, $roomId, $paymentMethod) {

            $totalAmount = 0;
            $processedItems = [];

            // 1️⃣ Load items and validate stock
            foreach ($items as $item) {
                $type = $item['item_type'] ?? 'product';
                $quantity = (int) $item['quantity'];

                if ($type === 'product') {
                    $product = Product::findOrFail($item['product_id']);
                    if ($product->currentStock() < $quantity) {
                        throw ValidationException::withMessages([
                            'stock' => "Insufficient stock for {$product->name}.",
                        ]);
                    }
                    $processedItems[] = [
                        'type' => 'product', 'model' => $product, 'quantity' => $quantity, 'price' => $product->selling_price
                    ];
                    $totalAmount += $product->selling_price * $quantity;
                } else {
                    $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                    $processedItems[] = [
                        'type' => 'menu', 'model' => $menuItem, 'quantity' => $quantity, 'price' => $menuItem->price
                    ];
                    $totalAmount += $menuItem->price * $quantity;
                }
            }

            // Determine status
            $paymentStatus = 'paid';
            if ($paymentMethod === 'room') {
                $paymentStatus = 'unpaid';
                $paidAmount = 0; // Guest isn't paying now
            } elseif ($paidAmount < $totalAmount) {
                $paymentStatus = $paidAmount > 0 ? 'partial' : 'unpaid';
            }

            // 2️⃣ Create Sale
            $sale = Sale::create([
                'user_id' => $userId,
                'room_id' => $roomId,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => max(0, $paidAmount - $totalAmount),
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
            ]);

            // 3️⃣ Create Items & Stock Movements
            foreach ($processedItems as $pItem) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_type' => $pItem['type'],
                    'product_id' => $pItem['type'] === 'product' ? $pItem['model']->id : null,
                    'menu_item_id' => $pItem['type'] === 'menu' ? $pItem['model']->id : null,
                    'quantity' => $pItem['quantity'],
                    'unit_price' => $pItem['price'],
                    'subtotal' => $pItem['price'] * $pItem['quantity'],
                ]);

                if ($pItem['type'] === 'product') {
                    StockMovement::create([
                        'product_id' => $pItem['model']->id,
                        'quantity' => -$pItem['quantity'],
                        'type' => 'sale',
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                    ]);
                }
            }

            // 4️⃣ Record the Payment if money was received
            if ($paidAmount > 0) {
                \App\Models\Payment::create([
                    'sale_id' => $sale->id,
                    'user_id' => $userId,
                    'amount' => min($paidAmount, $totalAmount), // Record only up to the sale total
                    'method' => $paymentMethod === 'room' ? 'cash' : $paymentMethod,
                ]);
            }

            return $sale;
        });
    }
}
