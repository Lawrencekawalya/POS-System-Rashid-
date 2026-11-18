<?php

use App\Models\MenuItem;
use App\Models\Sale;
use App\Models\User;

beforeEach(function () {
    $this->cashier = User::factory()->create(['role' => 'cashier']);
});

test('the POS tab suggests menu items and shows the custom item button', function () {
    MenuItem::create(['name' => 'Pork with chips, matooke or chapati', 'price' => 25000, 'category' => 'Pork']);

    $this->actingAs($this->cashier)->get(route('pos.index'))
        ->assertOk()
        ->assertSee('<option value="Pork with chips, matooke or chapati">', false)
        ->assertSee('Pork with chips, matooke or chapati · 25,000 (Menu)')
        ->assertSee('Custom Order / Not on Menu');
});

test('a menu item can be sold from the POS tab', function () {
    $item = MenuItem::create(['name' => 'Goat curry', 'price' => 23000, 'category' => 'Goat']);

    $this->actingAs($this->cashier)
        ->post(route('pos.add'), ['barcode' => 'Goat curry', 'mode' => 'pos'])
        ->assertRedirect(route('pos.index', ['mode' => 'pos']));

    $this->actingAs($this->cashier)
        ->post(route('pos.checkout'), ['paid_amount' => 25000, 'payment_method' => 'cash'])
        ->assertRedirect();

    $sale = Sale::with('items')->sole();
    expect($sale->payment_status)->toBe('paid')
        ->and((float) $sale->total_amount)->toBe(23000.0)
        ->and((float) $sale->change_amount)->toBe(2000.0)
        ->and($sale->items->sole()->menu_item_id)->toBe($item->id);
});

test('a custom item can be added from the POS tab without appearing on the guest menu', function () {
    $this->actingAs($this->cashier)
        ->post(route('pos.add'), ['custom_name' => 'Special grilled fish', 'custom_price' => 40000, 'mode' => 'pos'])
        ->assertRedirect();

    expect(session('cart'))->toHaveCount(1)
        ->and(collect(session('cart'))->first()['unit_price'])->toEqual(40000);

    $this->get(route('menu'))->assertDontSee('Special grilled fish');
});
