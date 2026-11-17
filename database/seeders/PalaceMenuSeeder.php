<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Imports the Palace Hotel paper menu.
 *
 * Food is imported as menu items shown on the guest menu. Existing items with
 * the same name keep their price (the POS price stays authoritative) but get
 * the menu's section, description and order. Bar drinks are not duplicated:
 * existing products are placed under bar sections instead.
 *
 * Safe to run more than once: php artisan db:seed --class=PalaceMenuSeeder
 */
class PalaceMenuSeeder extends Seeder
{
    /** @var array<string, array<int, array{0: string, 1: int, 2?: string}>> section => [name, price, description] */
    private const FOOD = [
        'Breakfast Specials' => [
            ['Palace full breakfast', 25000, 'Juice, fruits, katogo, eggs of your choice, bread and any tea of your choice.'],
            ['Palace petit breakfast', 15000, 'Katogo, tea, bread and juice.'],
        ],
        'Hot Beverages' => [
            ['African coffee', 8000],
            ['Black coffee', 7000],
            ['African tea', 7000],
            ['Black tea', 6000],
            ['English tea', 10000, 'A pot of black tea served with a choice of hot or cold milk on the side.'],
            ['Hot water', 3000],
            ['Lemon tea', 7000],
            ['Dawa tea', 10000, 'Well cooked and spiced to your liking, served with a choice of sugar or honey.'],
            ['A glass of hot or cold milk', 5000],
            ['Hot chocolate', 8000, 'Served with steaming milk.'],
        ],
        'Herbal Teas' => [
            ['Fresh hibiscus flower tea', 9000, 'Believed to help lower blood pressure.'],
        ],
        'Iced Teas & Coffee' => [
            ['Iced tea', 8000],
            ['Lemon iced tea', 8000],
            ['Ginger iced tea', 8000],
            ['Iced coffee', 8000, 'With a dash of ginger root, rosemary, lemon or lemon grass flavour, topped with fresh mint.'],
        ],
        'Freshly Squeezed Juices' => [
            ['Fresh juice', 10000, 'Mango, pineapple, watermelon, passion fruit, orange, lemon, carrot, beetroot or mint pineapple.'],
            ['Fruit cocktail', 12000, 'Mix your own choice of fruits.'],
        ],
        'Breakfast Bites' => [
            ['Bread omelette', 7000],
            ['Sunny side up egg', 6000],
            ['Egg on toast', 8000],
            ['Cheese on toast', 8000],
            ['Scrambled eggs', 6000, 'Specify well done or medium.'],
            ['Fried eggs', 6000, 'Specify well done or medium.'],
            ['Boiled eggs', 6000, 'Specify well done or medium.'],
            ['Omelette', 6000, 'Spanish, plain, cheese or mushroom. Please choose.'],
            ['French toast', 8000],
            ['A pair of beef sausages', 5000],
            ['Toasted or fresh bread', 2000],
            ['A plate of katogo (vegetables)', 12000],
            ['A plate of katogo (beef)', 13000],
        ],
        'Salads' => [
            ['Greek salad', 22000, 'Cucumber, tomatoes, feta cheese, onions and olives.'],
            ['Garden salad', 12000, 'Cucumber, tomatoes, onions, carrots and avocado.'],
            ['Chicken pasta salad', 25000],
            ['Cucumber salad', 15000, 'Diced cucumber, lettuce, lemon juice, mayonnaise, yoghurt, salt and pepper.'],
        ],
        'Starters' => [
            ['Tomato soup', 12000],
            ['Vegetable soup', 12000],
            ['Pumpkin soup', 15000],
            ['Mushroom soup', 15000],
            ['Potato soup', 12000],
            ['Chicken soup', 15000],
            ['French onion soup', 12000],
        ],
        'Goat' => [
            ['Pan fried goat mchomo', 22000, 'Served with rice, matooke or chips.'],
            ['Grilled goat mchomo', 25000, 'Served with chips or chapati and salad.'],
            ['Goat curry', 23000, 'Hot, medium or mild. Cooked with Indian spices, served with rice, chapati, Irish potatoes or matooke.'],
        ],
        'Beef' => [
            ['Beef stroganoff', 25000, 'Strips of beef and mushrooms in a creamy sauce, served with rice, chapati, chips or mashed Irish.'],
            ['Beef stir fry', 25000, 'Beef fried in Indian spices, served with chips or chapati.'],
            ['Pepper steak', 28000, 'Well done or medium rare, grilled or pan fried. Served with rice, chapati or chips and pepper sauce.'],
            ['Beef burger', 25000, 'Served with chips and salad.'],
            ['Cheese burger', 25000, 'Served with chips and salad.'],
            ['Beef bitoka', 25000, 'Topped with pepper sauce, served with sautéed potatoes and vegetables.'],
        ],
        'Chicken' => [
            ['Chicken curry', 23000, 'Hot, medium or mild, served with rice, matooke, chapati or mashed Irish.'],
            ['Chicken stroganoff', 25000, 'Chicken strips and mushrooms in a creamy sauce, served with rice or chapati.'],
            ['Sweet & sour chicken', 25000, 'Boneless chicken cooked with pineapple, soy sauce and Chinese five spice, served with rice.'],
            ['Chicken stir fry', 25000, 'Served with chips or chapati.'],
            ['Grilled chicken with chips and salad of the day', 25000],
            ['Mushroom chicken', 25000, 'Served with parsley potatoes or steamed rice.'],
            ['Chicken pasta bake', 25000, 'Topped with cheese.'],
            ['Baked avocado chicken', 25000, 'Served with chips.'],
            ['Chicken biryani', 25000, 'Served with brown sauce.'],
            ['Chicken wrap', 28000, 'Chicken stuffed with spinach and cheese, wrapped in bacon, served with brown sauce.'],
            ['Whole chicken', 50000, 'Served with an accompaniment for one person.'],
        ],
        'Pork' => [
            ['Pork with chips, matooke or chapati', 23000],
            ['Grilled pork', 25000, 'Served with jacket potatoes, chips, boiled potatoes or salad.'],
            ['Pork noisette', 28000, 'Pork steak grilled with onions and tomatoes, topped with cheese, served with chips.'],
            ['Palace pork special', 30000, 'Grilled pork steak topped with a pineapple ring and crispy bacon, served with rice or chips and brown sauce.'],
            ['Pork medallions al creamy', 25000, 'Cubes of pork in a creamy mushroom sauce, served with rice.'],
            ['Julienne pork', 27000, 'Tossed with assorted vegetables and pasta, flavoured with Tabasco and cheese.'],
        ],
        'Fish' => [
            ['Whole fish', 30000, 'Seasoned with mild spices, deep fried or grilled, topped with sautéed vegetables, served with roasted Irish or chips.'],
            ['Grilled fish fillet (tilapia)', 35000, 'Lightly seasoned with garlic and lemon, served with a salad garnish and chips.'],
            ['Fish fingers or battered fish fillet', 30000, 'Coated in egg, flour and bread crumbs, deep fried, served with chips or roasted Irish.'],
            ['Fish masala or fish curry', 30000, 'Fillet cooked in masala sauce and Indian spices, served with rice or mashed Irish.'],
            ["Chef's special fish luwombo", 35000, 'Served with boiled Irish.'],
            ['Fish stir fry', 30000, 'Fish fingers fried in Indian spices, served with chips or roasted Irish.'],
        ],
        'Vegetarian' => [
            ['Grilled palace potato special', 22000, 'Sautéed vegetables topped with mashed potatoes and finished with melted grated cheese.'],
            ['Vegetable pasta bake', 25000, 'Topped with cheese.'],
            ['Mixed vegetable curry', 22000, 'Hot or mild, served with potatoes, chapati, rice or pasta.'],
            ['Vegetable ratatouille', 22000, 'Stuffed vegetables rolled into a pancake and topped with melted cheese, served with mashed potatoes or plain rice and the sauce of the day.'],
            ['Aloo matar masala', 20000, 'Potatoes and green peas, served with rice or chapati.'],
            ['Aloo gobi masala', 20000, 'Potatoes and cauliflower, served with rice or chapati.'],
            ['Vegetable biryani', 22000],
            ['Vegetable cutlets', 20000, 'Served with southern sauce.'],
        ],
        'Pasta & Spaghetti' => [
            ['Chicken and bacon pasta bake', 28000],
            ['Macaroni cheese', 22000],
            ['Spaghetti bolognaise', 25000, 'Spaghetti with minced meat cooked in onions and tomatoes with herbs.'],
            ['Spaghetti carbonara', 23000, 'With bacon, fresh cream, white wine and fresh herbs.'],
            ['Spaghetti napolitano', 20000, 'With mixed vegetables cooked with tomatoes and fresh herbs.'],
        ],
        'Pizza' => [
            ['Palace special pizza', 35000, 'Chicken, onions and mushrooms topped with bacon and cheese.'],
            ['Pizza with olives', 30000, 'Tomatoes, onions, mushrooms, green peppers and black olives.'],
            ['Vegetable pizza', 30000, 'Mixed vegetables topped with cheese.'],
            ['Hawaii pizza', 30000, 'Vegetables and pineapple.'],
            ['Margherita pizza', 30000, 'Tomatoes and cheese.'],
        ],
        'Sandwiches' => [
            ['Bacon, lettuce & tomato (BLT)', 22000],
            ['Egg sandwich', 15000],
            ['Cheese sandwich', 15000],
            ['Chicken mayonnaise sandwich', 22000],
        ],
        'Snacks' => [
            ['Plain chips', 8000],
            ['Chips chicken', 20000],
            ['Chips goat', 22000],
            ['Chips beef', 22000],
            ['Chips pork', 23000],
            ['1kg pork (plain)', 45000],
            ['1kg goat', 55000],
            ['Pilau rice', 15000],
            ['Chapati', 2000],
            ['Sausages (a pair)', 5000],
            ['Yellow banana', 1000],
            ['Guacamole', 13000, 'Served with tortilla chips or sweet potatoes.'],
            ['Popcorn', 2000],
            ['English pancakes', 5000],
            ['Rolex', 8000],
            ['Chicken nuggets with chips', 23000],
            ['Fish fillet or fingers with chips', 30000],
            ['Ryan chips masala', 20000],
        ],
        'Desserts' => [
            ['Tropical fruit salad', 8000],
            ['Pancake parisienne', 10000],
            ['Assorted fruit platter', 10000],
            ['Chocolate banana', 8000, 'Flavoured with vanilla essence.'],
        ],
        'Healthy Booster Drinks' => [
            ['Mango magic', 15000, 'Mango, banana, fresh milk, yoghurt and ice cubes.'],
            ['Watermelon crush', 15000, 'Watermelon and ice cubes.'],
            ['Tropical storm', 15000, 'Banana, mango, coconut milk, yoghurt and ice cubes.'],
        ],
        'African & Ugandan Dishes' => [
            ['Vegetable katogo', 12000],
            ['Beef katogo', 13000],
            ['Goat katogo', 15000],
            ['Groundnut sauce', 16000, 'With mushrooms or greens.'],
            ['Beans goulash', 16000, 'Beans cooked with vegetables.'],
            ['Peas goulash', 16000, 'Peas cooked with vegetables.'],
            ['Beef or goat stew', 22000, 'Meat cooked with vegetables.'],
            ['Whole fish stew', 30000],
            ['Chicken stew', 22000],
        ],
    ];

    /** @var array<string, array<int, string>> bar section => existing product names */
    private const BAR = [
        'Soft Drinks' => ['Soda', 'Small Water', 'Big Water', 'Small Minute Maid', 'Big Minute Maid'],
        'Beers' => ['Beer', 'Heineken 330ml', 'Heineken Can 500ml'],
        'Spirits, Gins & Whiskeys' => [
            'Gilbeys Big', 'Gilbeys Quarter', 'Captain Morgan', 'Captain Morgan Quarter', 'Smirnoff Vodka Quarter',
            'Bond 7 Quarter', 'Baileys', 'UG Big', 'UG Quarter', 'V & A Quarter', 'Black Label Full', 'Black Label Shot',
        ],
        'Energy Drinks' => ['Red Bull', 'Rock Boom', 'Sting', 'Guarana'],
        'Wines' => ['4 Cousins Red', '4 Cousins White', 'Nederburg'],
    ];

    public function run(): void
    {
        $order = 0;
        $keptPrices = [];

        foreach (self::FOOD as $section => $items) {
            foreach ($items as $item) {
                [$name, $price] = $item;
                $order++;

                $menuItem = MenuItem::firstOrNew(['name' => $name]);

                if (! $menuItem->exists) {
                    $menuItem->price = $price;
                } elseif ((float) $menuItem->price !== (float) $price) {
                    $keptPrices[] = sprintf('%s (kept %s, menu says %s)', $name, number_format($menuItem->price), number_format($price));
                }

                $menuItem->fill([
                    'category' => $section,
                    'description' => $item[2] ?? null,
                    'show_on_menu' => true,
                    'sort_order' => $order,
                ])->save();
            }
        }

        $missingProducts = [];

        foreach (self::BAR as $section => $names) {
            foreach ($names as $name) {
                $updated = Product::where('name', $name)->update(['menu_section' => $section]);

                if (! $updated) {
                    $missingProducts[] = $name;
                }
            }
        }

        $this->command?->info(sprintf('Imported %d menu items.', $order));

        if ($keptPrices !== []) {
            $this->command?->warn('Existing prices kept: '.implode('; ', $keptPrices));
        }

        if ($missingProducts !== []) {
            $this->command?->warn('Bar products not found (set their menu section in Products): '.implode(', ', $missingProducts));
        }
    }
}
