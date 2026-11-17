<?php

use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Room;
use App\Models\Sale;
use App\Models\SaleRefund;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\PartialRefundService;
use App\Services\RefundService;
use App\Services\SaleService;
use App\Services\ZReportService;

function cashLedgerProduct(int $stock = 10): Product
{
    $product = Product::create([
        'name' => 'Test Beer',
        'barcode' => 'BEER-'.fake()->unique()->numerify('#####'),
        'unit_type' => 'bottle',
        'cost_price' => 1000,
        'selling_price' => 2000,
        'is_active' => true,
    ]);
    StockMovement::create(['product_id' => $product->id, 'quantity' => $stock, 'type' => 'purchase']);

    return $product;
}

function zReportFor(string $date): array
{
    return app(ZReportService::class)->summaryForDate($date);
}

test('refunding an unpaid room charge pays no money out', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $room = Room::create(['name' => 'B1']);
    $product = cashLedgerProduct();

    $sale = app(SaleService::class)->createSale($cashier->id, [
        ['item_type' => 'product', 'product_id' => $product->id, 'quantity' => 2],
    ], 0, $room->id, 'room');

    app(RefundService::class)->refund($sale, $cashier->id, 'Guest never received the order');

    $report = zReportFor(now()->toDateString());

    expect(Payment::where('sale_id', $sale->id)->count())->toBe(0)
        ->and($report['refundTotal'])->toBe(4000.0)
        ->and($report['cashRefunded'])->toBe(0.0)
        ->and($report['cashExpected'])->toBe(0.0)
        ->and($room->fresh()->currentBalance())->toBe(0.0);
});

test('a cash refund pays back the sale value, not the cash tendered', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = cashLedgerProduct();

    // Total 4000, customer tendered 5000 and got 1000 change.
    $sale = app(SaleService::class)->createSale($cashier->id, [
        ['item_type' => 'product', 'product_id' => $product->id, 'quantity' => 2],
    ], 5000);

    app(RefundService::class)->refund($sale, $cashier->id, 'Wrong brand');

    $report = zReportFor(now()->toDateString());

    expect((float) Payment::where('sale_id', $sale->id)->where('amount', '<', 0)->sum('amount'))->toBe(-4000.0)
        ->and($report['cashReceived'])->toBe(4000.0)
        ->and($report['cashRefunded'])->toBe(4000.0)
        ->and($report['cashExpected'])->toBe(0.0);
});

test('a partial refund on a partly paid sale only pays back the overpayment', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = cashLedgerProduct();

    // Total 4000, only 3000 paid so far.
    $sale = app(SaleService::class)->createSale($cashier->id, [
        ['item_type' => 'product', 'product_id' => $product->id, 'quantity' => 2],
    ], 3000);
    $saleItem = $sale->items()->firstOrFail();

    // Returning one bottle (2000) leaves 2000 owed against 3000 paid: 1000 goes back.
    app(PartialRefundService::class)->refund($sale, $cashier->id, [$saleItem->id => 1], 'One bottle was flat');

    expect((float) Payment::where('sale_id', $sale->id)->where('amount', '<', 0)->sum('amount'))->toBe(-1000.0)
        ->and($sale->fresh()->payment_status)->toBe('paid')
        ->and(zReportFor(now()->toDateString())['cashExpected'])->toBe(2000.0);
});

test('a settled room sale is refunded via the settlement method and leaves no room credit', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $room = Room::create(['name' => 'B2']);
    $product = cashLedgerProduct();

    $settled = app(SaleService::class)->createSale($cashier->id, [
        ['item_type' => 'product', 'product_id' => $product->id, 'quantity' => 2],
    ], 0, $room->id, 'room');
    $open = app(SaleService::class)->createSale($cashier->id, [
        ['item_type' => 'product', 'product_id' => $product->id, 'quantity' => 1],
    ], 0, $room->id, 'room');

    $this->actingAs($cashier)
        ->post(route('rooms.settle', $room), ['amount' => 4000, 'method' => 'mobile_money', 'sale_id' => $settled->id])
        ->assertRedirect();

    $saleItem = $settled->items()->firstOrFail();
    app(PartialRefundService::class)->refund($settled->fresh(), $cashier->id, [$saleItem->id => 1], 'Returned one bottle');

    $payout = Payment::where('sale_id', $settled->id)->where('amount', '<', 0)->sole();

    expect((float) $payout->amount)->toBe(-2000.0)
        ->and($payout->method)->toBe('mobile_money')
        ->and($payout->room_id)->toBe($room->id)
        ->and(zReportFor(now()->toDateString())['cashRefunded'])->toBe(0.0)
        // Only the open 2000 charge is still owed; the refund is not a credit.
        ->and($room->fresh()->currentBalance())->toBe(2000.0);

    expect($open->fresh()->payment_status)->toBe('unpaid');
});

test('the z-report counts cash on the day it is received and excludes non-cash payments', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $admin = User::factory()->create(['role' => 'admin']);
    $room = Room::create(['name' => 'B3']);
    $product = cashLedgerProduct();

    $this->travelTo(now()->setDate(2026, 11, 10)->setTime(20, 0));
    app(SaleService::class)->createSale($cashier->id, [
        ['item_type' => 'product', 'product_id' => $product->id, 'quantity' => 3],
    ], 0, $room->id, 'room');
    app(SaleService::class)->createSale($cashier->id, [
        ['item_type' => 'product', 'product_id' => $product->id, 'quantity' => 1],
    ], 2000, null, 'card');

    $this->travelTo(now()->setDate(2026, 11, 11)->setTime(9, 0));
    $this->actingAs($cashier)
        ->post(route('rooms.settle', $room), ['amount' => 6000, 'method' => 'cash'])
        ->assertRedirect();

    $chargeDay = zReportFor('2026-11-10');
    $settlementDay = zReportFor('2026-11-11');

    expect($chargeDay['grossSales'])->toBe(8000.0)
        ->and($chargeDay['cashExpected'])->toBe(0.0)
        ->and($settlementDay['grossSales'])->toBe(0.0)
        ->and($settlementDay['cashReceived'])->toBe(6000.0)
        ->and($settlementDay['cashExpected'])->toBe(6000.0);

    $this->actingAs($admin)
        ->get(route('reports.z', ['start_date' => '2026-11-11', 'end_date' => '2026-11-11']))
        ->assertOk()
        ->assertViewHas('cashExpected', 6000.0)
        ->assertSee('Cash Received');
});

test('menu item refunds are counted in the z-report and the cashier refund total', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $menuItem = MenuItem::create(['name' => 'Chicken Stew', 'price' => 15000, 'category' => 'Kitchen']);

    $sale = app(SaleService::class)->createSale($cashier->id, [
        ['item_type' => 'menu', 'menu_item_id' => $menuItem->id, 'quantity' => 1],
    ], 15000);

    app(RefundService::class)->refund($sale, $cashier->id, 'Food was cold');

    $report = zReportFor(now()->toDateString());

    expect($report['refundTotal'])->toBe(15000.0)
        ->and($report['netSales'])->toBe(0.0)
        ->and($report['cashExpected'])->toBe(0.0);

    $this->actingAs($cashier)
        ->get(route('pos.index'))
        ->assertOk()
        ->assertViewHas('totalRefunds', fn ($total) => (float) $total === 15000.0)
        ->assertViewHas('cumulativeRevenue', fn ($total) => (float) $total === 0.0);
});

test('the backfill migration records payouts for refunds made before payouts were tracked', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $room = Room::create(['name' => 'B4']);

    $paidSale = Sale::create([
        'user_id' => $cashier->id, 'total_amount' => 4000, 'paid_amount' => 5000, 'change_amount' => 1000,
        'refunded_amount' => 4000, 'payment_status' => 'refunded', 'payment_method' => 'cash',
    ]);
    Payment::create(['sale_id' => $paidSale->id, 'user_id' => $cashier->id, 'amount' => 4000, 'method' => 'cash']);

    $roomSale = Sale::create([
        'user_id' => $cashier->id, 'room_id' => $room->id, 'total_amount' => 3000, 'paid_amount' => 0,
        'change_amount' => 0, 'refunded_amount' => 3000, 'payment_status' => 'refunded', 'payment_method' => 'room',
    ]);

    foreach ([[$paidSale, 4000], [$roomSale, 3000]] as [$sale, $amount]) {
        SaleRefund::create([
            'sale_id' => $sale->id, 'quantity' => 1, 'amount' => $amount,
            'refunded_by' => $cashier->id, 'reason' => 'Legacy refund',
        ]);
    }

    $migration = require database_path('migrations/2026_11_17_000000_backfill_refund_payouts_into_payments.php');
    $migration->up();
    $migration->up(); // Re-running must not duplicate payouts.

    expect((float) Payment::where('sale_id', $paidSale->id)->where('amount', '<', 0)->sum('amount'))->toBe(-4000.0)
        ->and(Payment::where('sale_id', $paidSale->id)->where('amount', '<', 0)->count())->toBe(1)
        ->and(Payment::where('sale_id', $roomSale->id)->count())->toBe(0);

    $migration->down();

    expect(Payment::where('amount', '<', 0)->count())->toBe(0);
});
