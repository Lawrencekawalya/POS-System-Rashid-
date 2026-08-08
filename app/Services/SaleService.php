<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Payment;
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
     */
    public function createSale(
        int $userId,
        array $items,
        float $paidAmount,
        ?int $roomId = null,
        string $paymentMethod = 'cash',
        bool $useItemPrices = false
    ): Sale {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Sale must contain at least one item.',
            ]);
        }

        if ($paymentMethod === 'room' && ! $roomId) {
            throw ValidationException::withMessages([
                'room_id' => 'A room is required when charging a sale to a room.',
            ]);
        }

        return DB::transaction(function () use ($userId, $items, $paidAmount, $roomId, $paymentMethod, $useItemPrices) {

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
                    $price = $useItemPrices ? (float) $item['unit_price'] : (float) $product->selling_price;
                    $processedItems[] = [
                        'type' => 'product', 'model' => $product, 'quantity' => $quantity, 'price' => $price,
                    ];
                    $totalAmount += $price * $quantity;
                } else {
                    $menuItem = MenuItem::with('stockProduct')->findOrFail($item['menu_item_id']);
                    $price = $useItemPrices ? (float) $item['unit_price'] : (float) $menuItem->price;

                    $stockQuantity = $menuItem->stockProduct
                        ? $menuItem->stock_quantity * $quantity
                        : null;

                    if ($menuItem->stockProduct && $menuItem->stockProduct->currentStock() < $stockQuantity) {
                        throw ValidationException::withMessages([
                            'stock' => "Insufficient fresh-cut portions for {$menuItem->name}.",
                        ]);
                    }

                    $processedItems[] = [
                        'type' => 'menu',
                        'model' => $menuItem,
                        'quantity' => $quantity,
                        'price' => $price,
                        'stock_product' => $menuItem->stockProduct,
                        'stock_quantity' => $menuItem->stockProduct ? $menuItem->stock_quantity : null,
                    ];
                    $totalAmount += $price * $quantity;
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
                    'stock_product_id' => $pItem['type'] === 'menu' ? $pItem['stock_product']?->id : null,
                    'stock_quantity' => $pItem['type'] === 'menu' ? $pItem['stock_quantity'] : null,
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

                if ($pItem['type'] === 'menu' && $pItem['stock_product']) {
                    StockMovement::create([
                        'product_id' => $pItem['stock_product']->id,
                        'quantity' => -($pItem['stock_quantity'] * $pItem['quantity']),
                        'type' => 'sale',
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'remarks' => "Fresh-cut portions used by {$pItem['model']->name}",
                    ]);
                }
            }

            // 4️⃣ Record the Payment if money was received
            if ($paidAmount > 0) {
                Payment::create([
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
