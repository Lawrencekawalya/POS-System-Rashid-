<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Support\Facades\Hash;

test('the admin seeder refuses to run without configured credentials', function () {
    config(['app.admin.email' => null, 'app.admin.password' => null]);

    expect(fn () => $this->seed(AdminUserSeeder::class))
        ->toThrow(RuntimeException::class, 'ADMIN_EMAIL and ADMIN_PASSWORD');

    expect(User::count())->toBe(0);
});

test('the admin seeder creates an admin from configuration', function () {
    config(['app.admin' => ['name' => 'Owner', 'email' => 'owner@example.com', 'password' => 'a-long-secret-pass']]);

    $this->seed(AdminUserSeeder::class);

    $admin = User::where('email', 'owner@example.com')->sole();

    expect($admin->role)->toBe('admin')
        ->and($admin->name)->toBe('Owner')
        ->and(Hash::check('a-long-secret-pass', $admin->password))->toBeTrue();
});

test('the admin seeder does not overwrite an existing account', function () {
    $existing = User::factory()->create(['email' => 'owner@example.com', 'role' => 'admin']);
    config(['app.admin' => ['name' => 'Owner', 'email' => 'owner@example.com', 'password' => 'another-secret']]);

    $this->seed(AdminUserSeeder::class);

    expect(User::where('email', 'owner@example.com')->count())->toBe(1)
        ->and(Hash::check('another-secret', $existing->fresh()->password))->toBeFalse();
});
