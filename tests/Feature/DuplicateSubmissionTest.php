<?php

use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->cashier = User::factory()->create(['role' => 'cashier']);
    $this->dish = MenuItem::create(['name' => 'Goat curry', 'price' => 23000, 'category' => 'Goat']);
});

function checkout(string $key, int $paid = 25000)
{
    return test()->actingAs(test()->cashier)
        ->withSession(['cart' => ['menu_'.test()->dish->id => [
            'item_type' => 'menu', 'menu_item_id' => test()->dish->id, 'name' => 'Goat curry',
            'quantity' => 1, 'unit_price' => 23000, 'subtotal' => 23000,
        ]]])
        ->post(route('pos.checkout'), ['paid_amount' => $paid, 'payment_method' => 'cash', '_idempotency_key' => $key]);
}

test('a sale submitted twice with the same key is recorded once', function () {
    $first = checkout('3f0f5e0c-1d1b-4e4e-9a51-6d2b1c3f9a10');
    $second = checkout('3f0f5e0c-1d1b-4e4e-9a51-6d2b1c3f9a10');

    expect(Sale::count())->toBe(1)
        ->and(Payment::count())->toBe(1)
        ->and($second->headers->get('Location'))->toBe($first->headers->get('Location'))
        ->and($second->headers->get('Idempotent-Replayed'))->toBe('true');
});

test('a new key or different data is processed as a new submission', function () {
    checkout('11111111-1111-4111-8111-111111111111');
    checkout('22222222-2222-4222-8222-222222222222');
    checkout('22222222-2222-4222-8222-222222222222', paid: 30000);

    expect(Sale::count())->toBe(3);
});

test('requests without a key work as before and never see the key field', function () {
    checkout('');
    checkout('');

    expect(Sale::count())->toBe(2);

    Route::post('/_test/echo', fn () => request()->all())->middleware('web');
    $this->post('/_test/echo', ['name' => 'x', '_idempotency_key' => 'aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee'])
        ->assertExactJson(['name' => 'x']);
});

test('failed submissions are not remembered, so they can be retried', function () {
    Route::post('/_test/flaky', function () {
        static $calls = 0;

        return ++$calls === 1 ? abort(500) : response('ok');
    })->middleware('web');

    $data = ['_idempotency_key' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc'];
    $this->post('/_test/flaky', $data)->assertStatus(500);
    $this->post('/_test/flaky', $data)->assertOk()->assertSee('ok');
});

test('a duplicate that arrives while the first is still running is told to wait', function () {
    Route::post('/_test/slow', fn () => response('done'))->middleware('web');

    // The first request still holds the lock after the duplicate has waited its full time.
    Cache::partialMock()->shouldReceive('lock')->andReturn(new class
    {
        public function block($seconds)
        {
            throw new LockTimeoutException;
        }

        public function release() {}
    });

    $this->post('/_test/slow', ['_idempotency_key' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd'])
        ->assertStatus(409);
});
