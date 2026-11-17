<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GuestMenuService
{
    /**
     * Build the public menu: groups of sections of items, in the order set in
     * config/menu.php. Empty sections and groups are left out.
     *
     * @return array<int, array{name: string, slug: string, note: ?string, sections: array}>
     */
    public function groups(): array
    {
        $menuItems = MenuItem::onGuestMenu()->get()->toBase()->groupBy(fn (MenuItem $item) => $item->category ?: 'Other');
        $barProducts = Product::where('is_active', true)
            ->whereNotNull('menu_section')
            ->orderBy('name')
            ->get()
            ->toBase()
            ->groupBy('menu_section');

        $groups = [];
        $listedCategories = [];

        foreach (config('menu.groups', []) as $group) {
            $source = ($group['source'] ?? 'menu_items') === 'products' ? $barProducts : $menuItems;
            $sections = [];

            foreach ($group['sections'] as $sectionName => $note) {
                $listedCategories[] = $sectionName;
                $sections[] = $this->section($sectionName, $note, $source->get($sectionName, collect()), $this->icon($sectionName, $source === $barProducts));
            }

            $groups[] = $this->group($group['name'], $group['note'] ?? null, $sections, $group['nav'] ?? null);
        }

        // Menu items in categories the config doesn't list still show up.
        $unlisted = $menuItems->except($listedCategories);
        $groups[] = $this->group('More', null, $unlisted->map(
            fn (Collection $items, string $category) => $this->section($category, null, $items, 'food')
        )->values()->all());

        return array_values(array_filter($groups, fn (array $group) => $group['sections'] !== []));
    }

    /**
     * Placeholder icon for cards without a photo: a glass for cold drinks,
     * a cup for hot drinks, a plate for food.
     */
    private function icon(string $section, bool $isBar): string
    {
        return match (true) {
            $isBar, (bool) preg_match('/iced|juice|drink|booster|smoothie/i', $section) => 'cold',
            (bool) preg_match('/beverage|tea|coffee/i', $section) => 'hot',
            default => 'food',
        };
    }

    private function group(string $name, ?string $note, array $sections, ?string $nav = null): array
    {
        return [
            'name' => $name,
            'nav' => $nav ?? $name,
            'slug' => Str::slug($name),
            'note' => $note,
            'sections' => array_values(array_filter($sections, fn (array $section) => $section['items'] !== [])),
        ];
    }

    private function section(string $name, ?string $note, Collection $items, string $icon): array
    {
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'note' => $note,
            'icon' => $icon,
            'items' => $items->map(fn (MenuItem|Product $item) => [
                'name' => $item->name,
                'description' => $item instanceof MenuItem ? $item->description : null,
                'price' => (float) ($item instanceof MenuItem ? $item->price : $item->selling_price),
                'image' => $item->image_url,
                'credit' => $item->image_is_stock ? $item->image_credit : null,
                'credit_url' => $item->image_is_stock ? $item->image_credit_url : null,
            ])->values()->all(),
        ];
    }
}
