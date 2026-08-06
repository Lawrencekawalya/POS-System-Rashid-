<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\PurchaseService;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    // public function index(Request $request)
    // {
    //     $from = $request->start_date
    //         ? now()->parse($request->start_date)->startOfDay()
    //         : now()->startOfDay();

    //     $to = $request->end_date
    //         ? now()->parse($request->end_date)->endOfDay()
    //         : now()->endOfDay();

    //     $purchases = Purchase::with('user')
    //         ->whereBetween('purchased_at', [$from, $to])
    //         ->orderByDesc('purchased_at')
    //         ->get();

    //     return view('purchases.index', compact('purchases', 'from', 'to'));
    // }
    public function index(Request $request)
    {
        $query = Purchase::with('user')->orderByDesc('purchased_at');

        // Only filter if dates are actually provided in the request
        if ($request->filled('start_date')) {
            $query->where('purchased_at', '>=', now()->parse($request->start_date)->startOfDay());
        }

        if ($request->filled('end_date')) {
            $query->where('purchased_at', '<=', now()->parse($request->end_date)->endOfDay());
        }

        $purchases = $query->paginate(15); // Use paginate for better performance

        return view('purchases.index', compact('purchases'));
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('items.product', 'user');

        return view('purchases.show', compact('purchase'));
    }
    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('purchases.create', compact('products'));
    }

    public function store(Request $request, PurchaseService $purchaseService)
    {
        $request->validate([
            'supplier_name' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $purchase = $purchaseService->recordPurchase(
            $request->user()->id,
            $request->items,
            $request->supplier_name,
            $request->reference_no
        );

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase recorded successfully.');
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load('items');
        $products = Product::orderBy('name')->get();

        // Map the items to a simple array structure for the frontend
        $existingItems = old('items') ?? $purchase->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'unit_cost' => $item->unit_cost,
        ]);

        return view('purchases.edit', compact('purchase', 'products', 'existingItems'));
    }

    public function update(Request $request, Purchase $purchase, PurchaseService $purchaseService)
    {
        $request->validate([
            'supplier_name' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $purchaseService->updatePurchase(
            $purchase,
            $request->items,
            $request->supplier_name,
            $request->reference_no
        );

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase updated successfully.');
    }

}
