<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated admins can visit the dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('cashiers cannot access the dashboard', function () {
    $cashier = User::factory()->create([
        'role' => 'cashier',
    ]);

    $this->actingAs($cashier);

    $response = $this->get(route('dashboard'));
    $response->assertForbidden();
});

test('admins can view the complete low stock page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('products.low-stock'))
        ->assertOk()
        ->assertSee('Low Stock Products');
});

test('admins can view the complete current stock page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('products.current-stock'))
        ->assertOk()
        ->assertSee('Current Stock Levels');
});

test('cashiers cannot view the complete low stock page', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)
        ->get(route('products.low-stock'))
        ->assertForbidden();
});

test('cashiers cannot view the complete current stock page', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)
        ->get(route('products.current-stock'))
        ->assertForbidden();
});
