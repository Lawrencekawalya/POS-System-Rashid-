<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\MenuImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    // public function index().
    // {
    //     $products = Product::with('stockMovements')
    //         ->orderBy('name')
    //         ->paginate(15);

    //     return view('products.index', compact('products'));
    // }
    public function index(Request $request)
    {
        $query = Product::with('stockMovements')
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $products = $query->paginate(15);

        return view('products.index', compact('products'));
    }

    /**
     * Display all active products at or below the low-stock threshold.
     */
    public function lowStock()
    {
        $products = Product::where('is_active', true)
            ->with('stockMovements')
            ->get()
            ->filter(fn (Product $product) => $product->isLowStock(5))
            ->sortBy(fn (Product $product) => $product->currentStock())
            ->values();

        return view('products.low-stock', compact('products'));
    }

    /**
     * Display stock levels for every active product.
     */
    public function currentStock()
    {
        $products = Product::where('is_active', true)
            ->with('stockMovements')
            ->orderBy('name')
            ->get();

        return view('products.current-stock', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('products.create', ['barSections' => $this->barSections()]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request, MenuImageService $images)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'barcode' => 'required|string|max:255|unique:products,barcode',
            'unit_type' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0.01',
            'selling_price' => 'required|numeric|min:0.01',
            'menu_section' => ['nullable', 'string', Rule::in($this->barSections())],
            ...MenuImageService::rules(),
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['menu_section'] = $validated['menu_section'] ?? null;
        unset($validated['image'], $validated['remove_image']);
        $validated = [...$validated, ...$images->sync($request, null)];

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        return view('products.edit', ['product' => $product, 'barSections' => $this->barSections()]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product, MenuImageService $images)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'barcode' => 'required|string|max:255|unique:products,barcode,'.$product->id,
            'unit_type' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0.01',
            'selling_price' => 'required|numeric|min:0.01',
            'menu_section' => ['nullable', 'string', Rule::in($this->barSections())],
            ...MenuImageService::rules(),
        ]);

        // Force boolean value for checkbox
        $validated['is_active'] = $request->has('is_active');
        $validated['menu_section'] = $validated['menu_section'] ?? null;
        unset($validated['image'], $validated['remove_image']);
        $validated = [...$validated, ...$images->sync($request, $product->image_path)];

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Soft delete the specified product.
     */
    public function destroy(Product $product, MenuImageService $images)
    {
        $product->delete();
        $images->delete($product->image_path);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Bar section names from config/menu.php a product can be listed under.
     */
    private function barSections(): array
    {
        return collect(config('menu.groups', []))
            ->filter(fn (array $group) => ($group['source'] ?? null) === 'products')
            ->flatMap(fn (array $group) => array_keys($group['sections']))
            ->values()
            ->all();
    }
}
