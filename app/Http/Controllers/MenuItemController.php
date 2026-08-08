<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Product;
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
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('category', 'like', '%'.$request->search.'%');
        }

        $menuItems = $query->paginate(15);

        return view('menu-items.index', compact('menuItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('menu-items.create', [
            'portionProducts' => $this->portionProducts(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:menu_items,name',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'stock_product_id' => 'nullable|exists:products,id',
            'stock_quantity' => 'nullable|integer|min:1|required_with:stock_product_id',
        ]);

        $this->clearStockQuantityWhenUntracked($validated);

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
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:menu_items,name,'.$menuItem->id,
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'stock_product_id' => 'nullable|exists:products,id',
            'stock_quantity' => 'nullable|integer|min:1|required_with:stock_product_id',
        ]);

        $this->clearStockQuantityWhenUntracked($validated);

        $menuItem->update($validated);

        return redirect()->route('menu-items.index')
            ->with('success', 'Menu item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return redirect()->route('menu-items.index')
            ->with('success', 'Menu item deleted successfully.');
    }

    private function portionProducts()
    {
        return Product::where('is_active', true)
            ->where('unit_type', 'portion')
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
