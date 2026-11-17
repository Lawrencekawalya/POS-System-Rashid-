<?php

use App\Models\MenuItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PalaceMenuSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('menu cards show a photo when there is one and a placeholder otherwise', function () {
    MenuItem::create(['name' => 'Goat curry', 'price' => 23000, 'category' => 'Goat', 'show_on_menu' => true, 'image_path' => 'goat.webp']);
    MenuItem::create(['name' => 'Beef stew', 'price' => 22000, 'category' => 'Goat', 'show_on_menu' => true]);
    menuProduct('Nile Special', 'Beers')->update(['image_path' => 'nile.webp']);

    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('src="'.asset('uploads/menu/goat.webp').'"', false)
        ->assertSee('alt="Goat curry"', false)
        ->assertSee('src="'.asset('uploads/menu/nile.webp').'"', false)
        ->assertSee('loading="lazy"', false)
        ->assertSee('card__placeholder', false);
});

test('uploaded menu photos are resized to WebP and replaced or removed cleanly', function () {
    Storage::fake('menu_images');
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('menu-items.store'), [
        'name' => 'Goat curry',
        'price' => 23000,
        'category' => 'Goat',
        'show_on_menu' => '1',
        'image' => UploadedFile::fake()->image('goat.jpg', 2400, 1800),
    ])->assertRedirect(route('menu-items.index'));

    $item = MenuItem::where('name', 'Goat curry')->sole();
    $first = $item->image_path;

    expect($first)->toEndWith('.webp');
    Storage::disk('menu_images')->assertExists($first);
    [$width, $height] = getimagesizefromstring(Storage::disk('menu_images')->get($first));
    expect([$width, $height])->toBe([800, 600]);

    // Replacing the photo deletes the old file.
    $this->actingAs($admin)->put(route('menu-items.update', $item), [
        'name' => 'Goat curry',
        'price' => 23000,
        'category' => 'Goat',
        'show_on_menu' => '1',
        'image' => UploadedFile::fake()->image('goat2.png', 600, 600),
    ])->assertRedirect(route('menu-items.index'));

    $second = $item->fresh()->image_path;
    expect($second)->not->toBe($first);
    Storage::disk('menu_images')->assertMissing($first);
    Storage::disk('menu_images')->assertExists($second);

    // Removing the photo deletes the file and clears the path.
    $this->actingAs($admin)->put(route('menu-items.update', $item), [
        'name' => 'Goat curry',
        'price' => 23000,
        'category' => 'Goat',
        'show_on_menu' => '1',
        'remove_image' => '1',
    ])->assertRedirect(route('menu-items.index'));

    expect($item->fresh()->image_path)->toBeNull();
    Storage::disk('menu_images')->assertMissing($second);
});

test('products accept a photo and reject files that are not images', function () {
    Storage::fake('menu_images');
    $admin = User::factory()->create(['role' => 'admin']);
    $product = menuProduct('Coke', 'Soft Drinks', 2000);
    $form = [
        'name' => 'Coke',
        'barcode' => $product->barcode,
        'unit_type' => 'bottle',
        'cost_price' => 1000,
        'selling_price' => 2000,
        'is_active' => '1',
        'menu_section' => 'Soft Drinks',
    ];

    $this->actingAs($admin)->put(route('products.update', $product), [...$form, 'image' => UploadedFile::fake()->create('menu.pdf', 50, 'application/pdf')])
        ->assertSessionHasErrors('image');

    $this->actingAs($admin)->put(route('products.update', $product), [...$form, 'image' => UploadedFile::fake()->image('coke.jpg', 300, 900)])
        ->assertRedirect(route('products.index'));

    $path = $product->fresh()->image_path;
    Storage::disk('menu_images')->assertExists($path);
    [$width, $height] = getimagesizefromstring(Storage::disk('menu_images')->get($path));
    expect([$width, $height])->toBe([267, 800]);
});

test('deleting a menu item deletes its photo', function () {
    Storage::fake('menu_images');
    Storage::disk('menu_images')->put('old.webp', 'x');
    $item = MenuItem::create(['name' => 'Old dish', 'price' => 1000, 'image_path' => 'old.webp']);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->delete(route('menu-items.destroy', $item))
        ->assertRedirect();

    Storage::disk('menu_images')->assertMissing('old.webp');
});

test('the menu page includes the phone navigation helpers', function () {
    MenuItem::create(['name' => 'Goat curry', 'price' => 23000, 'category' => 'Goat', 'show_on_menu' => true]);

    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('aria-label="Back to top"', false)
        ->assertSee('<a href="#main-courses" title="Main Courses">Mains</a>', false);
});
