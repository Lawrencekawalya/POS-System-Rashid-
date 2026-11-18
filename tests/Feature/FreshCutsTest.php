<?php

use App\Models\FreshCutBatch;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\FreshCutService;
use App\Services\RefundService;
use App\Services\SaleService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cashier = User::factory()->create(['role' => 'cashier']);
});

function beefCut(): Product
{
    return app(FreshCutService::class)->createCut('Beef', 8);
}

function buyBeef(Product $cut, float $kg, string $when = 'now', float $cost = 0): FreshCutBatch
{
    return app(FreshCutService::class)->recordPurchase($cut, User::first(), $kg, 8, $cost, null, Carbon::parse($when));
}

function sellPlates(Product $cut, int $plates, int $portionsEach = 1): void
{
    $dish = MenuItem::firstOrCreate(['name' => "Beef stew x{$portionsEach}"], [
        'price' => 20000, 'category' => 'Beef', 'stock_product_id' => $cut->id, 'stock_quantity' => $portionsEach,
    ]);

    app(SaleService::class)->createSale(User::first()->id, [
        ['item_type' => 'menu', 'menu_item_id' => $dish->id, 'quantity' => $plates],
    ], 20000 * $plates);
}

test('the admin adds a cut and records kg bought as portions', function () {
    $this->actingAs($this->admin)
        ->post(route('fresh-cuts.store'), ['name' => 'Beef', 'portions_per_kg' => 8])
        ->assertRedirect();

    $cut = Product::freshCuts()->sole();

    $this->actingAs($this->admin)->post(route('fresh-cuts.purchases.store', $cut), [
        'kg' => 5, 'portions_per_kg' => 8, 'total_cost' => 100000, 'supplier' => 'Mbarara market',
        'purchased_at' => now()->subMinute()->format('Y-m-d\TH:i'),
    ])->assertSessionHas('success', 'Added 40 portions of Beef.');

    expect($cut->refresh()->currentStock())->toBe(40)
        ->and((float) $cut->cost_price)->toBe(2500.0)
        ->and(FreshCutBatch::sole()->portions)->toBe(40);
});

test('portions are used from the oldest purchase first', function () {
    $cut = beefCut();
    buyBeef($cut, 5, '-2 days');   // 40 portions
    buyBeef($cut, 2, '-1 day');    // 16 portions
    sellPlates($cut, 15, 3);       // 45 portions

    $lots = app(FreshCutService::class)->summary($cut)['lots'];

    expect(array_column($lots, 'remaining'))->toBe([0, 11])
        ->and(app(FreshCutService::class)->summary($cut)['closing'])->toBe(11)
        ->and(app(FreshCutService::class)->summary($cut)['kg_left'])->toBe(1.38);
});

test('any past day shows the portions left at the end of that day', function () {
    $cut = beefCut();
    buyBeef($cut, 5, '-3 days 09:00');
    Carbon::setTestNow(now()->subDays(2)->setTime(13, 0));
    sellPlates($cut, 4);
    Carbon::setTestNow();
    sellPlates($cut, 6);

    $twoDaysAgo = app(FreshCutService::class)->summary($cut, now()->subDays(2));
    expect($twoDaysAgo)->toMatchArray(['opening' => 40, 'sold' => 4, 'closing' => 36]);

    $this->actingAs($this->cashier)
        ->get(route('fresh-cuts.index', ['date' => now()->subDays(2)->toDateString()]))
        ->assertOk()
        ->assertSee('Showing portions at the end of')
        ->assertSee('36');

    expect(app(FreshCutService::class)->summary($cut)['closing'])->toBe(30);
});

test('refunds put portions back', function () {
    $cut = beefCut();
    buyBeef($cut, 1);
    sellPlates($cut, 2);
    app(RefundService::class)->refund(Sale::sole(), $this->admin->id, 'Wrong order');

    expect(app(FreshCutService::class)->summary($cut))->toMatchArray(['sold' => 2, 'returned' => 2, 'closing' => 8]);
});

test('the admin writes off spoiled portions but not more than are left', function () {
    $cut = beefCut();
    buyBeef($cut, 1);

    $this->actingAs($this->admin)
        ->post(route('fresh-cuts.write-offs.store', $cut), ['portions' => 3, 'reason' => 'spoiled', 'note' => 'Fridge off overnight'])
        ->assertSessionHasNoErrors();

    $this->actingAs($this->admin)
        ->post(route('fresh-cuts.write-offs.store', $cut), ['portions' => 6, 'reason' => 'spoiled'])
        ->assertSessionHasErrors(['portions' => 'Only 5 portion(s) are left to write off.']);

    expect(app(FreshCutService::class)->summary($cut))->toMatchArray(['written_off' => 3, 'closing' => 5])
        ->and(StockMovement::where('type', 'write_off')->value('remarks'))->toContain('Spoiled: Fridge off overnight');
});

test('a purchase can be removed only while none of it is used', function () {
    $cut = beefCut();
    $used = buyBeef($cut, 1, '-1 day');
    $mistake = buyBeef($cut, 50);
    sellPlates($cut, 1);

    $this->actingAs($this->admin)->delete(route('fresh-cuts.purchases.destroy', [$cut, $used]))->assertSessionHasErrors('batch');
    $this->actingAs($this->admin)->delete(route('fresh-cuts.purchases.destroy', [$cut, $mistake]))->assertSessionHasNoErrors();

    expect(FreshCutBatch::pluck('id')->all())->toBe([$used->id])
        ->and($cut->currentStock())->toBe(7);
});

test('cashiers can see fresh cuts but not change them', function () {
    $cut = beefCut();
    buyBeef($cut, 5);

    $this->actingAs($this->cashier)->get(route('fresh-cuts.index'))->assertOk()->assertSee('Beef')->assertDontSee('Add fresh cut');
    $this->actingAs($this->cashier)->get(route('fresh-cuts.show', $cut))->assertOk()->assertDontSee('Record a purchase');
    $this->actingAs($this->cashier)->post(route('fresh-cuts.purchases.store', $cut), ['kg' => 1, 'portions_per_kg' => 8, 'purchased_at' => now()])->assertForbidden();
    $this->actingAs($this->cashier)->post(route('fresh-cuts.write-offs.store', $cut), ['portions' => 1, 'reason' => 'spoiled'])->assertForbidden();
});

test('fresh cuts are kept off the products list and cannot be scanned at the POS', function () {
    $cut = beefCut();
    buyBeef($cut, 5);

    $this->actingAs($this->admin)->get(route('products.index'))->assertOk()->assertDontSee('CUT-');
    $this->actingAs($this->cashier)
        ->post(route('pos.add'), ['barcode' => $cut->barcode, 'mode' => 'pos'])
        ->assertSessionHasErrors('barcode');
    $this->actingAs($this->admin)->get(route('fresh-cuts.show', $cut))->assertOk()->assertSee('Record a purchase');
    $this->get(route('fresh-cuts.show', Product::create([
        'name' => 'Soda', 'barcode' => 'SODA-1', 'unit_type' => 'bottles', 'cost_price' => 1, 'selling_price' => 2,
    ])))->assertNotFound();
});
