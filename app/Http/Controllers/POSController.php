<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\Room;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    /**
     * Display the POS interface.
     */
    public function index()
    {
        $cart = session('cart', []);
        $total = collect($cart)->sum(fn($item) => $item['subtotal']);

        $today = now()->toDateString();
        $userId = Auth::id();

        // Detect mode
        $mode = request('mode', 'pos');
        $roomId = request('room_id');

        // Rooms (only load when needed)
        $rooms = $mode === 'room'
            ? Room::orderBy('name')->get()
            : collect();

        /**
         * Products & MenuItems
         */
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $menuItems = MenuItem::orderBy('name')->get();

        /**
         * Sales
         */
        $salesToday = Sale::with('items')
            ->where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $totalSales = $salesToday->sum('total_amount');

        /**
         * Refunds Logic
         */
        $totalRefunds = DB::table('stock_movements')
            ->join('sale_items', function ($join) {
                $join->on('sale_items.sale_id', '=', 'stock_movements.reference_id')
                    ->on('sale_items.product_id', '=', 'stock_movements.product_id');
            })
            ->where('stock_movements.type', 'refund')
            ->whereDate('stock_movements.created_at', now()->toDateString())
            ->whereIn('stock_movements.reference_id', function ($q) use ($userId) {
                $q->select('id')
                    ->from('sales')
                    ->where('user_id', $userId);
            })
            ->sum(DB::raw('stock_movements.quantity * sale_items.unit_price'));

        $netTotal = $totalSales - $totalRefunds;

        $cumulativeRevenue = \App\Models\Payment::sum('amount')
            - \App\Models\SaleRefund::sum('amount')
            - Expense::where('status', 'confirmed')->sum('amount');

        return view('pos.index', compact(
            'cart',
            'total',
            'salesToday',
            'totalSales',
            'totalRefunds',
            'netTotal',
            'products',
            'menuItems',
            'cumulativeRevenue',
            'rooms',
            'mode',
            'roomId'
        ));
    }

    /**
     * Add an item to the cart (Unified for Products and MenuItems).
     */
    public function add(Request $request)
    {
        $request->validate([
            'barcode' => 'nullable|string',
            'custom_name' => 'nullable|string',
            'custom_price' => 'nullable|numeric|min:0',
            'save_to_menu' => 'nullable|boolean'
        ]);

        $cart = session('cart', []);

        // 1. Handle Custom Item First
        if ($request->filled('custom_name') && $request->filled('custom_price')) {
            $name = $request->custom_name;
            $price = $request->custom_price;

            $menuItem = MenuItem::firstOrCreate(
                ['name' => $name],
                ['price' => $price, 'category' => $request->boolean('save_to_menu') ? 'Custom' : 'Ad-hoc']
            );

            // If we want to update the price of an existing menu item when 'save_to_menu' is checked
            if ($request->boolean('save_to_menu')) {
                $menuItem->update(['price' => $price]);
            }

            $key = 'menu_' . $menuItem->id;
            if (isset($cart[$key])) {
                $cart[$key]['quantity']++;
            } else {
                $cart[$key] = [
                    'item_type' => 'menu',
                    'menu_item_id' => $menuItem->id,
                    'name' => $menuItem->name,
                    'quantity' => 1,
                    'unit_price' => $price,
                ];
            }
            $cart[$key]['subtotal'] = $cart[$key]['quantity'] * $cart[$key]['unit_price'];
        } 
        // 2. Handle Barcode/Search Input
        elseif ($request->filled('barcode')) {
            $input = $request->barcode;

            // Try Product
            $product = Product::where('barcode', $input)->first();
            if ($product) {
                $key = 'product_' . $product->id;
                if (isset($cart[$key])) {
                    if ($cart[$key]['quantity'] + 1 > $product->currentStock()) {
                        return back()->withErrors(['barcode' => 'Insufficient stock for ' . $product->name]);
                    }
                    $cart[$key]['quantity']++;
                } else {
                    if ($product->currentStock() < 1) {
                        return back()->withErrors(['barcode' => $product->name . ' is currently out of stock']);
                    }
                    $cart[$key] = [
                        'item_type' => 'product',
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'quantity' => 1,
                        'unit_price' => $product->selling_price,
                    ];
                }
                $cart[$key]['subtotal'] = $cart[$key]['quantity'] * $cart[$key]['unit_price'];
            } else {
                // Try Menu Item
                $menuItem = MenuItem::where('name', $input)->first();
                if ($menuItem) {
                    $key = 'menu_' . $menuItem->id;
                    if (isset($cart[$key])) {
                        $cart[$key]['quantity']++;
                    } else {
                        $cart[$key] = [
                            'item_type' => 'menu',
                            'menu_item_id' => $menuItem->id,
                            'name' => $menuItem->name,
                            'quantity' => 1,
                            'unit_price' => $menuItem->price,
                        ];
                    }
                    $cart[$key]['subtotal'] = $cart[$key]['quantity'] * $cart[$key]['unit_price'];
                } else {
                    return back()->withErrors(['barcode' => "Item '{$input}' not found."]);
                }
            }
        } else {
            return back()->withErrors(['barcode' => "Please enter an item or barcode."]);
        }

        session(['cart' => $cart]);
        return redirect()->route('pos.index', ['mode' => $request->mode, 'room_id' => $request->room_id]);
    }

    /**
     * Process checkout and create a Sale.
     */
    public function checkout(Request $request, SaleService $saleService)
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return back()->withErrors(['cart' => 'Cart is empty']);
        }

        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'room_id' => 'nullable|integer',
            'payment_method' => 'required|string|in:cash,bank,card,room',
        ]);

        $items = collect($cart)->values()->toArray();

        $sale = $saleService->createSale(
            $request->user()->id,
            $items,
            $request->paid_amount,
            $request->room_id,
            $request->payment_method
        );

        session()->forget('cart');

        return redirect()->route('sales.show', $sale);
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(string $key)
    {
        $cart = session('cart', []);
        unset($cart[$key]);
        session(['cart' => $cart]);

        return back();
    }

    /**
     * Increase quantity in cart.
     */
    public function increase(string $key)
    {
        $cart = session('cart', []);
        if (!isset($cart[$key])) return back();

        if ($cart[$key]['item_type'] === 'product') {
            $product = Product::findOrFail($cart[$key]['product_id']);
            if ($cart[$key]['quantity'] + 1 > $product->currentStock()) {
                return back()->withErrors(['cart' => 'Insufficient stock']);
            }
        }

        $cart[$key]['quantity']++;
        $cart[$key]['subtotal'] = $cart[$key]['quantity'] * $cart[$key]['unit_price'];

        session(['cart' => $cart]);
        return back();
    }

    /**
     * Decrease quantity in cart.
     */
    public function decrease(string $key)
    {
        $cart = session('cart', []);
        if (!isset($cart[$key])) return back();

        $cart[$key]['quantity']--;

        if ($cart[$key]['quantity'] <= 0) {
            unset($cart[$key]);
        } else {
            $cart[$key]['subtotal'] = $cart[$key]['quantity'] * $cart[$key]['unit_price'];
        }

        session(['cart' => $cart]);
        return back();
    }

    /**
     * Manually update quantity in cart.
     */
    public function updateQuantity(Request $request, string $key)
    {
        $cart = session('cart', []);
        if (!isset($cart[$key])) return back();

        $qty = (int) $request->quantity;
        if ($qty <= 0) {
            unset($cart[$key]);
        } else {
            if ($cart[$key]['item_type'] === 'product') {
                $product = Product::findOrFail($cart[$key]['product_id']);
                if ($qty > $product->currentStock()) {
                    return back()->withErrors(['quantity' => 'Insufficient stock']);
                }
            }
            $cart[$key]['quantity'] = $qty;
            $cart[$key]['subtotal'] = $qty * (float) $cart[$key]['unit_price'];
        }

        session(['cart' => $cart]);
        return back();
    }
}
