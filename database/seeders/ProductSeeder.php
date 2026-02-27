<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [

            // Wines
            ['name' => '4 Cousins Red', 'cost' => 29000, 'price' => 50000, 'unit' => 'bottle'],
            ['name' => '4 Cousins White', 'cost' => 29000, 'price' => 50000, 'unit' => 'bottle'],
            ['name' => 'Four Cousins Red Big', 'cost' => 65000, 'price' => 85000, 'unit' => 'bottle'],
            ['name' => 'Four Cousins Big', 'cost' => 55000, 'price' => 85000, 'unit' => 'bottle'],
            ['name' => 'Nederburg', 'cost' => 50000, 'price' => 65000, 'unit' => 'bottle'],
            ['name' => 'Dry Wine Glass', 'cost' => 7000, 'price' => 10000, 'unit' => 'glass'],

            // Spirits
            ['name' => 'Baileys', 'cost' => 45000, 'price' => 65000, 'unit' => 'bottle'],
            ['name' => 'Black & White', 'cost' => 35000, 'price' => 50000, 'unit' => 'bottle'],
            ['name' => 'Black Label Full', 'cost' => 155000, 'price' => 230000, 'unit' => 'bottle'],
            ['name' => 'Black Label Shot', 'cost' => 6000, 'price' => 7000, 'unit' => 'shot'],
            ['name' => 'Double Black Full', 'cost' => 170000, 'price' => 250000, 'unit' => 'bottle'],
            ['name' => 'Double Black Shot', 'cost' => 7000, 'price' => 8000, 'unit' => 'shot'],
            ['name' => 'Bond 7 Quarter', 'cost' => 8000, 'price' => 12000, 'unit' => 'bottle'],
            ['name' => 'Captain Morgan', 'cost' => 25000, 'price' => 50000, 'unit' => 'bottle'],
            ['name' => 'Captain Morgan Quarter', 'cost' => 8000, 'price' => 15000, 'unit' => 'bottle'],
            ['name' => 'Gilbeys Quarter', 'cost' => 8500, 'price' => 12000, 'unit' => 'bottle'],
            ['name' => 'Gilbeys Big', 'cost' => 35000, 'price' => 55000, 'unit' => 'bottle'],
            ['name' => 'Smirnoff Vodka Quarter', 'cost' => 12000, 'price' => 15000, 'unit' => 'bottle'],
            ['name' => 'UG Quarter', 'cost' => 8000, 'price' => 12000, 'unit' => 'bottle'],
            ['name' => 'UG Big', 'cost' => 32000, 'price' => 50000, 'unit' => 'bottle'],
            ['name' => 'UG Lemon Big', 'cost' => 45000, 'price' => 55000, 'unit' => 'bottle'],
            ['name' => 'V & A Quarter', 'cost' => 8000, 'price' => 12000, 'unit' => 'bottle'],
            ['name' => 'Ginger Lemon Quarter', 'cost' => 11000, 'price' => 15000, 'unit' => 'bottle'],

            // Beers
            ['name' => 'Beer', 'cost' => 3200, 'price' => 5000, 'unit' => 'bottle'],
            ['name' => 'Heineken 330ml', 'cost' => 6000, 'price' => 10000, 'unit' => 'bottle'],
            ['name' => 'Heineken Can 500ml', 'cost' => 9000, 'price' => 12000, 'unit' => 'can'],

            // Soft Drinks & Energy
            ['name' => 'Big Minute Maid', 'cost' => 4000, 'price' => 6000, 'unit' => 'bottle'],
            ['name' => 'Small Minute Maid', 'cost' => 2100, 'price' => 3000, 'unit' => 'bottle'],
            ['name' => 'Red Bull', 'cost' => 6000, 'price' => 10000, 'unit' => 'can'],
            ['name' => 'Sting', 'cost' => 2000, 'price' => 3000, 'unit' => 'bottle'],
            ['name' => 'Guarana', 'cost' => 7000, 'price' => 10000, 'unit' => 'bottle'],
            ['name' => 'Rock Boom', 'cost' => 2000, 'price' => 3000, 'unit' => 'bottle'],

            // Water & Soda
            ['name' => 'Big Water', 'cost' => 1700, 'price' => 4000, 'unit' => 'bottle'],
            ['name' => 'Small Water', 'cost' => 900, 'price' => 2000, 'unit' => 'bottle'],
            ['name' => 'Soda', 'cost' => 900, 'price' => 2000, 'unit' => 'bottle'],
            ['name' => 'Oner', 'cost' => 2100, 'price' => 3000, 'unit' => 'bottle'],

            // Others
            ['name' => 'Coffee Malt', 'cost' => 1500, 'price' => 3000, 'unit' => 'bottle'],
        ];

        foreach ($products as $item) {

            $product = Product::create([
                'name' => $item['name'],
                'brand' => null,
                'barcode' => 'temp', // temporary placeholder
                'unit_type' => $item['unit'],
                'cost_price' => $item['cost'],
                'selling_price' => $item['price'],
                'is_active' => true,
            ]);

            // Generate unique barcode using slug + ID
            $product->barcode = Str::slug($product->name) . '-' . $product->id;
            $product->save();
        }
    }
}
