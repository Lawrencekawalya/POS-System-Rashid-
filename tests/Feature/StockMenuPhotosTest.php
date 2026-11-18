<?php

use App\Models\MenuItem;
use App\Models\User;
use App\Services\MenuImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;

function stockPhotoList(array $entries): string
{
    $relative = 'storage/framework/testing/stock-photos-test.json';
    @mkdir(dirname(base_path($relative)), 0777, true);
    file_put_contents(base_path($relative), json_encode($entries));

    return $relative;
}

function fakeJpeg(int $width = 1200, int $height = 900): string
{
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 120, 40));
    ob_start();
    imagejpeg($image);

    return (string) ob_get_clean();
}

function curryEntry(): array
{
    return [
        'type' => 'menu_item',
        'name' => 'Goat curry',
        'url' => 'https://images.example.org/goat-curry.jpg',
        'credit' => 'Photo: Jane Doe, CC BY-SA 2.0',
        'credit_url' => 'https://www.flickr.com/photos/jane/123',
    ];
}

beforeEach(function () {
    Storage::fake('menu_images');
    Sleep::fake();
});

afterEach(function () {
    @unlink(base_path('storage/framework/testing/stock-photos-test.json'));
});

test('stand-in photos are downloaded, resized and credited on the menu', function () {
    Http::fake(['images.example.org/*' => Http::response(fakeJpeg(), 200, ['Content-Type' => 'image/jpeg'])]);
    $item = MenuItem::create(['name' => 'Goat curry', 'price' => 23000, 'category' => 'Goat', 'show_on_menu' => true]);

    $this->artisan('menu:stock-photos', ['--file' => stockPhotoList([curryEntry()])])
        ->expectsOutputToContain('applied: 1')
        ->assertSuccessful();

    $item->refresh();
    expect($item->image_is_stock)->toBeTrue()
        ->and($item->image_credit)->toBe('Photo: Jane Doe, CC BY-SA 2.0');
    Storage::disk('menu_images')->assertExists($item->image_path);
    expect(getimagesizefromstring(Storage::disk('menu_images')->get($item->image_path))[0])->toBe(800);

    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('Some photos are for illustration only.')
        ->assertSee('Photo: Jane Doe, CC BY-SA 2.0')
        ->assertSee('https://www.flickr.com/photos/jane/123', false);
});

test('the stand-in import never replaces staff photos and skips entries already applied', function () {
    Http::fake(['images.example.org/*' => Http::response(fakeJpeg(), 200, ['Content-Type' => 'image/jpeg'])]);
    Storage::disk('menu_images')->put('staff.webp', 'x');
    MenuItem::create(['name' => 'Goat curry', 'price' => 23000, 'category' => 'Goat', 'show_on_menu' => true, 'image_path' => 'staff.webp']);
    MenuItem::create(['name' => 'Beef stew', 'price' => 22000, 'category' => 'Goat', 'show_on_menu' => true]);

    $list = stockPhotoList([curryEntry(), [...curryEntry(), 'name' => 'Beef stew', 'credit' => 'Photo: John Roe, CC BY 4.0', 'credit_url' => 'https://example.org/stew']]);

    $this->artisan('menu:stock-photos', ['--file' => $list])->assertSuccessful();
    $this->artisan('menu:stock-photos', ['--file' => $list])->assertSuccessful();

    // Only the stew was downloaded, and only once.
    Http::assertSentCount(1);
    expect(MenuItem::where('name', 'Goat curry')->value('image_path'))->toBe('staff.webp')
        ->and(MenuItem::where('name', 'Beef stew')->value('image_is_stock'))->toBeTrue();

    $this->get(route('menu'))->assertDontSee('Photo: Jane Doe', false)->assertSee('Some photos are for illustration only.');
});

test('a failed or non-image download leaves the item unchanged', function () {
    Http::fake([
        'images.example.org/missing.jpg' => Http::response('Not found', 404),
        'images.example.org/page.jpg' => Http::response('<html></html>', 200, ['Content-Type' => 'text/html']),
    ]);
    MenuItem::create(['name' => 'Goat curry', 'price' => 23000, 'category' => 'Goat', 'show_on_menu' => true]);
    MenuItem::create(['name' => 'Beef stew', 'price' => 22000, 'category' => 'Goat', 'show_on_menu' => true]);

    $this->artisan('menu:stock-photos', ['--file' => stockPhotoList([
        [...curryEntry(), 'url' => 'https://images.example.org/missing.jpg'],
        [...curryEntry(), 'name' => 'Beef stew', 'url' => 'https://images.example.org/page.jpg'],
    ])])->expectsOutputToContain('failed: 2')->assertSuccessful();

    expect(MenuItem::whereNotNull('image_path')->count())->toBe(0);
    $this->get(route('menu'))->assertDontSee('Some photos are for illustration only.');
});

test('a staff upload replaces a stand-in photo and its credit', function () {
    Storage::disk('menu_images')->put('stock.webp', 'x');
    $item = MenuItem::create([
        'name' => 'Goat curry', 'price' => 23000, 'category' => 'Goat', 'show_on_menu' => true,
        'image_path' => 'stock.webp', 'image_credit' => 'Photo: Jane Doe, CC BY-SA 2.0',
        'image_credit_url' => 'https://www.flickr.com/photos/jane/123', 'image_is_stock' => true,
    ]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('menu-items.edit', $item))->assertSee('This is a stand-in photo');

    $this->actingAs($admin)->put(route('menu-items.update', $item), [
        'name' => 'Goat curry', 'price' => 23000, 'category' => 'Goat', 'show_on_menu' => '1',
        'image' => UploadedFile::fake()->image('real-curry.jpg', 1000, 750),
    ])->assertRedirect(route('menu-items.index'));

    $item->refresh();
    expect($item->image_is_stock)->toBeFalse()
        ->and($item->image_credit)->toBeNull()
        ->and($item->image_path)->not->toBe('stock.webp');
    Storage::disk('menu_images')->assertMissing('stock.webp');
    $this->get(route('menu'))->assertDontSee('Photo: Jane Doe');
});

test('rate-limited downloads are retried before giving up', function () {
    Http::fakeSequence('images.example.org/*')
        ->push('Too many requests', 429)
        ->push(fakeJpeg(), 200, ['Content-Type' => 'image/jpeg']);
    MenuItem::create(['name' => 'Goat curry', 'price' => 23000, 'category' => 'Goat', 'show_on_menu' => true]);

    $this->artisan('menu:stock-photos', ['--file' => stockPhotoList([curryEntry()])])
        ->expectsOutputToContain('applied: 1')
        ->assertSuccessful();

    Http::assertSentCount(2);
    expect(MenuItem::where('name', 'Goat curry')->value('image_is_stock'))->toBeTrue();
});

test('an indexed-colour PNG can be uploaded as a menu photo', function () {
    $image = imagecreate(300, 200);
    imagecolorallocate($image, 120, 40, 20);
    $png = tempnam(sys_get_temp_dir(), 'palette').'.png';
    imagepng($image, $png);

    $path = app(MenuImageService::class)->store(new UploadedFile($png, 'logo.png', 'image/png', null, true));

    Storage::disk('menu_images')->assertExists($path);
    expect(getimagesizefromstring(Storage::disk('menu_images')->get($path))['mime'])->toBe('image/webp');
    @unlink($png);
});
