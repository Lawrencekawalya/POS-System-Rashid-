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
