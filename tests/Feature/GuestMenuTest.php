<?php

use App\Models\MenuItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PalaceMenuSeeder;

function menuProduct(string $name, ?string $section, int $price = 5000, bool $active = true): Product
{
    return Product::create([
        'name' => $name,
        'barcode' => 'MENU-'.fake()->unique()->numerify('#####'),
        'unit_type' => 'bottle',
        'cost_price' => 1000,
        'selling_price' => $price,
        'is_active' => $active,
        'menu_section' => $section,
    ]);
}

test('guests can open the menu without logging in', function () {
    MenuItem::create(['name' => 'Goat curry', 'price' => 23000, 'category' => 'Goat', 'show_on_menu' => true, 'description' => 'Hot, medium or mild.']);

    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('Main Courses')
        ->assertSee('Goat curry')
        ->assertSee('Hot, medium or mild.')
        ->assertSee('23,000/=')
        ->assertSee('Children under 5 years are half price.');
});

test('the menu only lists items marked for it, in their configured order', function () {
    MenuItem::create(['name' => 'Iced coffee', 'price' => 8000, 'category' => 'Iced Teas & Coffee', 'show_on_menu' => true, 'sort_order' => 2]);
    MenuItem::create(['name' => 'Iced tea', 'price' => 8000, 'category' => 'Iced Teas & Coffee', 'show_on_menu' => true, 'sort_order' => 1]);
    MenuItem::create(['name' => 'Hidden special', 'price' => 9000, 'category' => 'Goat', 'show_on_menu' => false]);
    MenuItem::create(['name' => 'Custom till item', 'price' => 1000, 'category' => 'Custom']);

    $this->get(route('menu'))
        ->assertOk()
        ->assertSeeInOrder(['Iced tea', 'Iced coffee'])
        ->assertDontSee('Hidden special')
        ->assertDontSee('Custom till item')
        // Sections without items are left out.
        ->assertDontSee('Salads');
});

test('bar drinks come from active products with a menu section', function () {
    menuProduct('Nile Special', 'Beers', 5000);
    menuProduct('Office Stock Drink', null);
    menuProduct('Discontinued Lager', 'Beers', 4000, active: false);

    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('Bar')
        ->assertSee('Nile Special')
        ->assertSee('5,000/=')
        ->assertDontSee('Office Stock Drink')
        ->assertDontSee('Discontinued Lager');
});

test('menu items in unlisted categories still appear under More', function () {
    MenuItem::create(['name' => 'Chef surprise', 'price' => 30000, 'category' => 'Weekend Specials', 'show_on_menu' => true]);

    $this->get(route('menu'))
        ->assertOk()
        ->assertSeeInOrder(['More', 'Weekend Specials', 'Chef surprise']);
});

test('staff can set the guest menu fields on menu items and products', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('menu-items.store'), [
        'name' => 'Rolex',
        'price' => 8000,
        'category' => 'Snacks',
        'description' => 'Chapati rolled with eggs.',
        'show_on_menu' => '1',
    ])->assertRedirect(route('menu-items.index'));

    expect(MenuItem::where('name', 'Rolex')->sole())
        ->show_on_menu->toBeTrue()
        ->description->toBe('Chapati rolled with eggs.');

    $product = menuProduct('Coke', null, 2000);

    $this->actingAs($admin)->put(route('products.update', $product), [
        'name' => 'Coke',
        'barcode' => $product->barcode,
        'unit_type' => 'bottle',
        'cost_price' => 1000,
        'selling_price' => 2000,
        'is_active' => '1',
        'menu_section' => 'Soft Drinks',
    ])->assertRedirect(route('products.index'));

    expect($product->fresh()->menu_section)->toBe('Soft Drinks');

    $this->actingAs($admin)->put(route('products.update', $product), [
        'name' => 'Coke',
        'barcode' => $product->barcode,
        'unit_type' => 'bottle',
        'cost_price' => 1000,
        'selling_price' => 2000,
        'menu_section' => 'Not A Section',
    ])->assertSessionHasErrors('menu_section');
});

test('the menu import is repeatable and keeps existing prices', function () {
    MenuItem::create(['name' => 'Rolex', 'price' => 5000, 'category' => 'Breakfast']);
    $soda = menuProduct('Soda', null, 2000);

    $this->seed(PalaceMenuSeeder::class);
    $countAfterFirstRun = MenuItem::count();
    $this->seed(PalaceMenuSeeder::class);

    $rolex = MenuItem::where('name', 'Rolex')->sole();

    expect(MenuItem::count())->toBe($countAfterFirstRun)
        ->and($countAfterFirstRun)->toBeGreaterThan(130)
        ->and((float) $rolex->price)->toBe(5000.0)
        ->and($rolex->category)->toBe('Snacks')
        ->and($rolex->show_on_menu)->toBeTrue()
        ->and($soda->fresh()->menu_section)->toBe('Soft Drinks');

    $this->get(route('menu'))
        ->assertOk()
        ->assertSeeInOrder(['Breakfast', 'Palace full breakfast', 'Hot Beverages', 'African coffee', 'Bar', 'Soda']);
});

test('staff get a printable QR code for the menu', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)
        ->get('http://palacehotel.example/menu-qr-code')
        ->assertOk()
        ->assertSee('<svg', false)
        ->assertSee('http://palacehotel.example/menu')
        ->assertDontSee('only opens on this computer');

    $this->actingAs($cashier)
        ->get(route('menu.qr.svg'))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml')
        ->assertDownload('menu-qr-code.svg');
});

test('the QR page warns when it points at a local address', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get('http://127.0.0.1:8000/menu-qr-code')
        ->assertOk()
        ->assertSee('only opens on this computer');
});

test('guests cannot open the QR code page', function () {
    $this->get(route('menu.qr'))->assertRedirect(route('login'));
});
