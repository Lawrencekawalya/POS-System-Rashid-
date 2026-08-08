<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class KitchenMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menuItems = [
            ['name' => 'Rolex', 'price' => 5000, 'category' => 'Breakfast'],
            ['name' => 'Chapati', 'price' => 2000, 'category' => 'Breakfast'],
            ['name' => 'Beef Pilau', 'price' => 18000, 'category' => 'Main Course'],
            ['name' => 'Chicken Pilau', 'price' => 22000, 'category' => 'Main Course'],
            ['name' => 'Fried Chicken', 'price' => 20000, 'category' => 'Main Course'],
            ['name' => 'Chips', 'price' => 8000, 'category' => 'Sides'],
            ['name' => 'Chips and Chicken', 'price' => 25000, 'category' => 'Main Course'],
            ['name' => 'Kachumbari', 'price' => 5000, 'category' => 'Sides'],
            ['name' => 'Fruit Salad', 'price' => 8000, 'category' => 'Dessert'],
            ['name' => 'African Tea', 'price' => 3000, 'category' => 'Hot Drinks'],
            ['name' => 'Coffee', 'price' => 5000, 'category' => 'Hot Drinks'],
            ['name' => 'Fresh Juice', 'price' => 8000, 'category' => 'Drinks'],
        ];

        foreach ($menuItems as $menuItem) {
            MenuItem::updateOrCreate(
                ['name' => $menuItem['name']],
                ['price' => $menuItem['price'], 'category' => $menuItem['category']],
            );
        }
    }
}
