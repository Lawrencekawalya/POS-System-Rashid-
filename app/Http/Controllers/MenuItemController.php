<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Product;
use App\Services\MenuImageService;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MenuItem::with('stockProduct')->orderBy('name');

        if ($request->filled('search')) {
            $search = '%'.trim($request->search).'%';
            $query->where(fn ($q) => $q->where('name', 'like', $search)->orWhere('category', 'like', $search));
        }

        $menuItems = $query->paginate(15)->withQueryString();

        // Search-as-you-type asks for just the results table.
        return view('menu-items.index', compact('menuItems'))
            ->fragmentIf($request->ajax(), 'results');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('menu-items.create', [
            'portionProducts' => $this->portionProducts(),
            'menuSections' => $this->menuSections(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, MenuImageService $images)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:menu_items,name',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'stock_product_id' => 'nullable|exists:products,id',
            'stock_quantity' => 'nullable|integer|min:1|required_with:stock_product_id',
            'description' => 'nullable|string|max:1000',
            ...MenuImageService::rules(),
        ]);

        $this->clearStockQuantityWhenUntracked($validated);
        $validated['show_on_menu'] = $request->boolean('show_on_menu');
        unset($validated['image'], $validated['remove_image']);
        $validated = [...$validated, ...$images->sync($request, null)];

        MenuItem::create($validated);

        return redirect()->route('menu-items.index')
            ->with('success', 'Menu item created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MenuItem $menuItem)
    {
        return view('menu-items.edit', [
            'menuItem' => $menuItem,
            'portionProducts' => $this->portionProducts(),
            'menuSections' => $this->menuSections(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MenuItem $menuItem, MenuImageService $images)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:menu_items,name,'.$menuItem->id,
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'stock_product_id' => 'nullable|exists:products,id',
            'stock_quantity' => 'nullable|integer|min:1|required_with:stock_product_id',
            'description' => 'nullable|string|max:1000',
            ...MenuImageService::rules(),
        ]);

        $this->clearStockQuantityWhenUntracked($validated);
        $validated['show_on_menu'] = $request->boolean('show_on_menu');
        unset($validated['image'], $validated['remove_image']);
        $validated = [...$validated, ...$images->sync($request, $menuItem->image_path)];

        $menuItem->update($validated);

        return redirect()->route('menu-items.index')
            ->with('success', 'Menu item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MenuItem $menuItem, MenuImageService $images)
    {
        $menuItem->delete();
        $images->delete($menuItem->image_path);

        return redirect()->route('menu-items.index')
            ->with('success', 'Menu item deleted successfully.');
    }

    /**
     * Food section names from config/menu.php, suggested for the category field.
     */
    private function menuSections(): array
    {
        return collect(config('menu.groups', []))
            ->reject(fn (array $group) => ($group['source'] ?? 'menu_items') === 'products')
            ->flatMap(fn (array $group) => array_keys($group['sections']))
            ->values()
            ->all();
    }

    private function portionProducts()
    {
        return Product::freshCuts()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function clearStockQuantityWhenUntracked(array &$validated): void
    {
        if (empty($validated['stock_product_id'])) {
            $validated['stock_product_id'] = null;
            $validated['stock_quantity'] = null;
        }
    }
}
