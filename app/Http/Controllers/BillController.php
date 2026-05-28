<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\MenuItem;
use App\Models\Product;
use Illuminate\Http\Request;

class BillController extends Controller
{
    /**
     * Add item to room bill (BAR ITEMS OR CUSTOM)
     */
    public function addItem(Request $request)
    {
        $data = $request->validate([
            'barcode' => 'nullable|string',
            'room_id' => 'required',
            'custom_name' => 'nullable|string',
            'custom_price' => 'nullable|numeric|min:0',
            'save_to_menu' => 'nullable|boolean',
        ]);

        if (! $data['room_id']) {
            return back()->withErrors(['Select a room first']);
        }

        // Get or create bill
        $bill = Bill::where('room_id', $data['room_id'])
            ->where('status', 'open')
            ->first();

        if (! $bill) {
            $bill = Bill::create([
                'room_id' => $data['room_id'],
                'status' => 'open',
                'total' => 0,
            ]);
        }

        // Handle Custom Item
        if ($request->filled('custom_name') && $request->filled('custom_price')) {
            $name = $data['custom_name'];
            $price = $data['custom_price'];

            // Optionally save to MenuItem table
            $itemId = null;
            if ($request->boolean('save_to_menu')) {
                $menuItem = MenuItem::updateOrCreate(
                    ['name' => $name],
                    ['price' => $price, 'category' => 'Custom']
                );
                $itemId = $menuItem->id;
                $type = 'menu';
            } else {
                // If not saved to menu, we'll still track it as a 'menu' type or 'custom'
                // For simplicity in your existing BillItem structure, let's see if we should create a MenuItem anyway
                // or just use item_id = 0 for true ad-hoc.
                // Let's create it as a MenuItem to maintain database integrity if that's preferred,
                // OR use a 'custom' type if you want to extend the model.

                // Recommendation: Create the MenuItem anyway so the BillItem relationship doesn't break.
                $menuItem = MenuItem::firstOrCreate(
                    ['name' => $name],
                    ['price' => $price, 'category' => 'Ad-hoc']
                );
                $itemId = $menuItem->id;
                $type = 'menu';
            }

            $item = new BillItem;
            $item->bill_id = $bill->id;
            $item->item_type = $type;
            $item->item_id = $itemId;
            $item->quantity = 1;
            $item->price = $price;
            $item->subtotal = $price;
            $item->source = 'kitchen';
            $item->status = 'served';
            $item->save();

        } elseif ($request->filled('barcode')) {
            // Standard Product or Menu Item Logic
            $input = $data['barcode'];

            // 1. Try finding by Barcode (Product)
            $product = Product::whereBarcode($input)->first();

            if ($product) {
                // Check existing item
                $existingItem = $bill->items()
                    ->where('item_type', 'product')
                    ->where('item_id', $product->id)
                    ->first();

                if ($existingItem) {
                    $existingItem->quantity += 1;
                    $existingItem->subtotal = $existingItem->quantity * $existingItem->price;
                    $existingItem->save();
                } else {
                    $item = new BillItem;
                    $item->bill_id = $bill->id;
                    $item->item_type = 'product';
                    $item->item_id = $product->id;
                    $item->quantity = 1;
                    $item->price = $product->selling_price;
                    $item->subtotal = $product->selling_price;
                    $item->source = 'bar';
                    $item->status = 'served';
                    $item->save();
                }
            } else {
                // 2. Try finding by Name (Menu Item)
                $menuItem = MenuItem::where('name', $input)->first();

                if ($menuItem) {
                    $existingItem = $bill->items()
                        ->where('item_type', 'menu')
                        ->where('item_id', $menuItem->id)
                        ->first();

                    if ($existingItem) {
                        $existingItem->quantity += 1;
                        $existingItem->subtotal = $existingItem->quantity * $existingItem->price;
                        $existingItem->save();
                    } else {
                        $item = new BillItem;
                        $item->bill_id = $bill->id;
                        $item->item_type = 'menu';
                        $item->item_id = $menuItem->id;
                        $item->quantity = 1;
                        $item->price = $menuItem->price;
                        $item->subtotal = $menuItem->price;
                        $item->source = 'kitchen';
                        $item->status = 'served';
                        $item->save();
                    }
                } else {
                    return back()->withErrors(['Item not found. Please scan a valid barcode or select an item from the list.']);
                }
            }
        } else {
            return back()->withErrors(['Scan a product or enter custom item details.']);
        }

        // Always update total
        $bill->recalculateTotal();

        return back()->with('success', 'Item added to room bill');
    }

    /**
     * Update quantity of an item in the bill
     */
    public function updateQuantity(Request $request, BillItem $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item->quantity = $request->quantity;
        $item->subtotal = $item->quantity * $item->price;
        $item->save();

        // Recalculate bill total
        $item->bill->recalculateTotal();

        return back()->with('success', 'Quantity updated');
    }

    /**
     * Finalize or update bill
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => 'required',
        ]);

        $bill = Bill::where('room_id', $data['room_id'])
            ->where('status', 'open')
            ->first();

        if (! $bill) {
            return back()->withErrors(['No items yet. Start adding products.']);
        }

        // Calculate total
        $total = BillItem::where('bill_id', $bill->id)->sum('subtotal');

        $bill->total = $total;
        $bill->save();

        return back()->with('success', 'Bill updated successfully');
    }

    /**
     * View room bill
     */
    public function show($room_id)
    {
        $bill = Bill::where('room_id', $room_id)
            ->where('status', 'open')
            ->with('items')
            ->first();

        return view('bills.show', compact('bill'));
    }

    /**
     * Checkout bill (cash or credit)
     */
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'bill_id' => 'required',
            'payment_type' => 'required',
        ]);

        $bill = Bill::findOrFail($data['bill_id']);

        $bill->payment_type = $data['payment_type'];
        $bill->status = 'closed';
        $bill->save();

        // OPTIONAL: deduct stock here

        return redirect()->back()->with('success', 'Bill closed successfully');
    }
}
