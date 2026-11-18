<?php

use App\Models\MenuItem;
use App\Models\User;

beforeEach(function () {
    $this->cashier = User::factory()->create(['role' => 'cashier']);
    MenuItem::create(['name' => 'Beef stir fry', 'price' => 25000, 'category' => 'Beef']);
    MenuItem::create(['name' => 'Goat curry', 'price' => 23000, 'category' => 'Goat']);
    MenuItem::create(['name' => 'Katogo', 'price' => 13000, 'category' => 'Beef dishes']);
});

test('typing a search returns just the matching results table', function () {
    $response = $this->actingAs($this->cashier)
        ->get(route('menu-items.index', ['search' => 'beef']), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertSee('Beef stir fry')
        ->assertSee('Katogo')
        ->assertDontSee('Goat curry');

    expect($response->getContent())->not->toContain('<html')->not->toContain('Type to search');
});

test('the full page still searches without JavaScript and explains an empty result', function () {
    $this->actingAs($this->cashier)
        ->get(route('menu-items.index', ['search' => 'goat']))
        ->assertOk()
        ->assertSee('Type to search by name or category...')
        ->assertSee('Goat curry')
        ->assertDontSee('Beef stir fry');

    $this->actingAs($this->cashier)
        ->get(route('menu-items.index', ['search' => 'pizza']), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertSee('No menu items match "pizza"', false);
});
