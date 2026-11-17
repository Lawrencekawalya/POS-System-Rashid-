<?php

use App\Models\Product;
use App\Models\Room;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\RefundService;
use App\Services\SaleService;

function showPageProduct(): Product
{
    $product = Product::create([
        'name' => '4 Cousins Red',
        'barcode' => 'SHOW-'.fake()->unique()->numerify('#####'),
        'unit_type' => 'bottle',
        'cost_price' => 29000,
        'selling_price' => 50000,
        'is_active' => true,
    ]);
    StockMovement::create(['product_id' => $product->id, 'quantity' => 10, 'type' => 'purchase']);

    return $product;
}

test('a partly paid sale shows its status, payments and balance due', function () {
    $cashier = User::factory()->create(['role' => 'cashier', 'name' => 'Test User']);
    $sale = app(SaleService::class)->createSale($cashier->id, [
        ['item_type' => 'product', 'product_id' => showPageProduct()->id, 'quantity' => 2],
    ], 50000);

    $this->actingAs($cashier)
        ->get(route('sales.show', $sale))
        ->assertOk()
        ->assertSee("Sale #{$sale->id}")
        ->assertSee('Partly paid')
        ->assertSeeInOrder(['Total', '100,000', 'Paid', '50,000', 'Balance due', '50,000'])
        ->assertSee('Payments')
        ->assertSee('Refund items')
        ->assertSee('2 of 2 refundable')
        // The printable receipt is still on the page.
        ->assertSee('thermal-receipt', false)
        ->assertSee("Receipt #{$sale->id}");
});

test('a room charge links to the folio to settle the balance', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $room = Room::create(['name' => 'A1']);
    $sale = app(SaleService::class)->createSale($cashier->id, [
        ['item_type' => 'product', 'product_id' => showPageProduct()->id, 'quantity' => 1],
    ], 0, $room->id, 'room');

    $this->actingAs($cashier)
        ->get(route('sales.show', $sale))
        ->assertOk()
        ->assertSee('Unpaid')
        ->assertSee('Charged to room')
        ->assertSee('Settle on folio')
        ->assertSee(route('rooms.folio', $room), false)
        ->assertSee('No payments recorded yet.');
});

test('a fully refunded sale shows its refunds and no refund form', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $sale = app(SaleService::class)->createSale($cashier->id, [
        ['item_type' => 'product', 'product_id' => showPageProduct()->id, 'quantity' => 1],
    ], 50000);

    app(RefundService::class)->refund($sale, $cashier->id, 'Bottle was open');

    $this->actingAs($cashier)
        ->get(route('sales.show', $sale))
        ->assertOk()
        ->assertSee('Refunded')
        ->assertSee('Bottle was open')
        ->assertSee('Cash refund')
        ->assertDontSee('Refund items')
        ->assertDontSee('Balance due');
});

test('the refund form on the page processes a partial refund', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $sale = app(SaleService::class)->createSale($cashier->id, [
        ['item_type' => 'product', 'product_id' => showPageProduct()->id, 'quantity' => 2],
    ], 100000);
    $item = $sale->items()->firstOrFail();

    $this->actingAs($cashier)
        ->post(route('sales.partial-refund', $sale), [
            'refund_reason' => 'One bottle returned',
            'items' => [$item->id => 1],
        ])
        ->assertRedirect(route('sales.show', $sale));

    $this->get(route('sales.show', $sale))
        ->assertOk()
        ->assertSee('Partial refund processed.')
        ->assertSee('1 refunded')
        ->assertSee('1 of 2 refundable');
});
