<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    // public function index()
    // {
    //     $products = Product::orderBy('name')->get();

    //     return view('products.index', compact('products'));
    // }
    public function index()
    {
        $products = Product::with('stockMovements')
            ->orderBy('name')
            ->paginate(15);
            // ->get();

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'barcode' => 'required|string|max:255|unique:products,barcode',
            'unit_type' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0.01',
            'selling_price' => 'required|numeric|min:0.01',
        ]);

        $validated['is_active'] = $request->has('is_active');

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
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'barcode' => 'required|string|max:255|unique:products,barcode,' . $product->id,
            'unit_type' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0.01',
            'selling_price' => 'required|numeric|min:0.01',
        ]);

        // Force boolean value for checkbox
        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Soft delete the specified product.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
