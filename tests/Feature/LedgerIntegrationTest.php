<?php

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Room;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\PartialRefundService;
use App\Services\RefundService;
use App\Services\SaleService;

function ledgerUser(): User
{
    return User::factory()->create(['role' => 'cashier']);
}

function ledgerProduct(): Product
{
    return Product::create([
        'name' => 'Test Soda',
        'barcode' => 'SKU-'.fake()->unique()->numerify('#####'),
        'unit_type' => 'bottle',
        'cost_price' => 1000,
        'selling_price' => 2000,
        'is_active' => true,
    ]);
}

test('bill checkout creates the sale, payment, and product stock movement', function () {
    $user = ledgerUser();
    $room = Room::create(['name' => 'A1']);
    $product = ledgerProduct();
    StockMovement::create(['product_id' => $product->id, 'quantity' => 4, 'type' => 'purchase']);

    $bill = Bill::create(['room_id' => $room->id, 'status' => 'open', 'total' => 4000]);
    BillItem::create([
        'bill_id' => $bill->id,
        'item_type' => 'product',
        'item_id' => $product->id,
        'quantity' => 2,
        'price' => 2000,
        'subtotal' => 4000,
        'source' => 'bar',
        'status' => 'served',
    ]);

    $this->actingAs($user)
        ->post(route('bills.checkout'), ['bill_id' => $bill->id, 'payment_type' => 'cash'])
        ->assertRedirect();

    $sale = Sale::firstOrFail();
    expect($sale->room_id)->toBe($room->id)
        ->and((float) $sale->total_amount)->toBe(4000.0)
        ->and((float) $sale->paid_amount)->toBe(4000.0);

    $this->assertDatabaseHas('payments', ['sale_id' => $sale->id, 'amount' => 4000]);
    $this->assertDatabaseHas('bills', ['id' => $bill->id, 'status' => 'closed', 'sale_id' => $sale->id]);
    expect($product->fresh()->currentStock())->toBe(2);
});

test('room settlement allocates payments to each room sale and rejects another room sale', function () {
    $user = ledgerUser();
    $room = Room::create(['name' => 'A1']);
    $otherRoom = Room::create(['name' => 'A2']);

    $firstSale = Sale::create([
        'user_id' => $user->id, 'room_id' => $room->id, 'total_amount' => 1000,
        'paid_amount' => 0, 'change_amount' => 0, 'payment_status' => 'unpaid', 'payment_method' => 'room',
    ]);
    $secondSale = Sale::create([
        'user_id' => $user->id, 'room_id' => $room->id, 'total_amount' => 500,
        'paid_amount' => 0, 'change_amount' => 0, 'payment_status' => 'unpaid', 'payment_method' => 'room',
    ]);
    $otherSale = Sale::create([
        'user_id' => $user->id, 'room_id' => $otherRoom->id, 'total_amount' => 500,
        'paid_amount' => 0, 'change_amount' => 0, 'payment_status' => 'unpaid', 'payment_method' => 'room',
    ]);

    $this->actingAs($user)
        ->post(route('rooms.settle', $room), ['amount' => 1500, 'method' => 'cash'])
        ->assertRedirect();

    expect($firstSale->fresh()->payment_status)->toBe('paid')
        ->and($secondSale->fresh()->payment_status)->toBe('paid')
        ->and(Payment::whereIn('sale_id', [$firstSale->id, $secondSale->id])->sum('amount'))->toEqual(1500);

    $this->actingAs($user)
        ->post(route('rooms.settle', $room), ['amount' => 100, 'method' => 'cash', 'sale_id' => $otherSale->id])
        ->assertStatus(422);
});

test('a full refund records menu and product refunds but restores stock only for products', function () {
    $user = ledgerUser();
    $product = ledgerProduct();
    $menuItem = MenuItem::create(['name' => 'Service Charge', 'price' => 500, 'category' => 'service']);
    StockMovement::create(['product_id' => $product->id, 'quantity' => 3, 'type' => 'purchase']);

    $sale = app(SaleService::class)->createSale($user->id, [
        ['item_type' => 'product', 'product_id' => $product->id, 'quantity' => 1],
        ['item_type' => 'menu', 'menu_item_id' => $menuItem->id, 'quantity' => 1],
    ], 2500);

    app(RefundService::class)->refund($sale, $user->id, 'Customer returned the order');

    expect($sale->fresh()->isRefunded())->toBeTrue()
        ->and($sale->refunds()->count())->toBe(2)
        ->and((float) $sale->refunds()->sum('amount'))->toBe(2500.0)
        ->and($product->fresh()->currentStock())->toBe(3);
});

test('a fresh-cut menu item deducts and restores its linked product portions', function () {
    $user = ledgerUser();
    $freshCut = Product::create([
        'name' => 'Goat Fresh Cut',
        'barcode' => 'GOAT-'.fake()->unique()->numerify('#####'),
        'unit_type' => 'portion',
        'cost_price' => 6000,
        'selling_price' => 0,
        'is_active' => true,
    ]);
    StockMovement::create(['product_id' => $freshCut->id, 'quantity' => 5, 'type' => 'purchase']);

    $menuItem = MenuItem::create([
        'name' => 'Goat Fresh Cut Platter',
        'price' => 18000,
        'category' => 'Fresh Cuts',
        'stock_product_id' => $freshCut->id,
        'stock_quantity' => 2,
    ]);

    $sale = app(SaleService::class)->createSale($user->id, [
        ['item_type' => 'menu', 'menu_item_id' => $menuItem->id, 'quantity' => 2],
    ], 36000);

    $saleItem = $sale->items()->firstOrFail();

    expect($saleItem->stock_product_id)->toBe($freshCut->id)
        ->and($saleItem->stock_quantity)->toBe(2)
        ->and($freshCut->fresh()->currentStock())->toBe(1);

    app(PartialRefundService::class)->refund($sale, $user->id, [$saleItem->id => 1], 'One platter was returned');

    expect($freshCut->fresh()->currentStock())->toBe(3);
});
