<?php

/*
|--------------------------------------------------------------------------
| Guest Menu
|--------------------------------------------------------------------------
|
| Layout of the public menu at /menu. Groups and sections appear in this
| order. Food sections list menu items whose `category` matches the section
| name and that are marked "show on guest menu". Bar sections (source
| "products") list active products whose `menu_section` matches.
|
| Menu items in a category not listed here still appear, under "More".
|
*/

return [

    'title' => env('MENU_TITLE', 'Palace Hotel'),

    'groups' => [
        [
            'name' => 'Breakfast',
            'sections' => [
                'Breakfast Specials' => null,
                'Hot Beverages' => 'All teas are served with biscuits or a cookie. Breakfast room service is charged 4,000/= extra.',
                'Herbal Teas' => 'Lemon grass, mint, rosemary, bay leaf or green tea.',
                'Iced Teas & Coffee' => 'Our iced teas are made with local tea leaves.',
                'Freshly Squeezed Juices' => 'Seasonal fruit juices, make a choice of your own.',
                'Breakfast Bites' => null,
            ],
        ],
        [
            'name' => 'Salads & Starters',
            'sections' => [
                'Salads' => 'Salads may be personalised, please ask.',
                'Starters' => 'All soups are served with a bread roll. Please specify cream or clear.',
            ],
        ],
        [
            'name' => 'Main Courses',
            'note' => 'All main dishes may be served with one accompaniment: chips, sautéed, mashed or boiled potatoes (parsley or rosemary), rice, chapati, roasted Irish, jacket potatoes or mashed matooke. Children under 5 years are half price. Any extra accompaniment is charged 5,000/=.',
            'sections' => [
                'Goat' => null,
                'Beef' => null,
                'Chicken' => null,
                'Pork' => null,
                'Fish' => null,
                'Vegetarian' => null,
            ],
        ],
        [
            'name' => 'Pasta, Pizza & Sandwiches',
            'sections' => [
                'Pasta & Spaghetti' => null,
                'Pizza' => 'All our pizzas are home-made. Create your own with mushrooms, tomatoes, onions, green peppers, chicken, chillies, olives or pineapple, spiced hot or mild.',
                'Sandwiches' => 'Served with chips. Please specify toasted or fresh bread. We can make a sandwich to your liking from any of these ingredients: deduct 5,000/= without chips.',
            ],
        ],
        [
            'name' => 'Snacks & Desserts',
            'sections' => [
                'Snacks' => null,
                'Desserts' => null,
                'Healthy Booster Drinks' => null,
            ],
        ],
        [
            'name' => 'Local Dishes',
            'sections' => [
                'African & Ugandan Dishes' => 'Please allow extra time or let us know in advance, as these dishes take longer to prepare. Served with matooke, posho, rice, cassava, sweet potatoes, Irish potatoes or chapati. Any dish with two accompaniments is served with seasonal vegetables.',
            ],
        ],
        [
            'name' => 'Bar',
            'source' => 'products',
            'note' => 'For other drinks, just ask. We can provide.',
            'sections' => [
                'Soft Drinks' => null,
                'Beers' => null,
                'Spirits, Gins & Whiskeys' => null,
                'Energy Drinks' => null,
                'Wines' => null,
            ],
        ],
    ],

];
