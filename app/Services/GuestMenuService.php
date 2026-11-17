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
                $sections[] = $this->section($sectionName, $note, $source->get($sectionName, collect()));
            }

            $groups[] = $this->group($group['name'], $group['note'] ?? null, $sections);
        }

        // Menu items in categories the config doesn't list still show up.
        $unlisted = $menuItems->except($listedCategories);
        $groups[] = $this->group('More', null, $unlisted->map(
            fn (Collection $items, string $category) => $this->section($category, null, $items)
        )->values()->all());

        return array_values(array_filter($groups, fn (array $group) => $group['sections'] !== []));
    }

    private function group(string $name, ?string $note, array $sections): array
    {
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'note' => $note,
            'sections' => array_values(array_filter($sections, fn (array $section) => $section['items'] !== [])),
        ];
    }

    private function section(string $name, ?string $note, Collection $items): array
    {
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'note' => $note,
            'items' => $items->map(fn (MenuItem|Product $item) => [
                'name' => $item->name,
                'description' => $item instanceof MenuItem ? $item->description : null,
                'price' => (float) ($item instanceof MenuItem ? $item->price : $item->selling_price),
            ])->values()->all(),
        ];
    }
}
