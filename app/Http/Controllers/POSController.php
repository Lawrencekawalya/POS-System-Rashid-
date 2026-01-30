<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    // public function index()
    // {
    //     $cart = session('cart', []);
    //     $total = collect($cart)->sum(fn($item) => $item['subtotal']);

    //     return view('pos.index', compact('cart', 'total'));
    // }
    public function index()
    {
        $cart = session('cart', []);
        $total = collect($cart)->sum(fn($item) => $item['subtotal']);

        $today = now()->toDateString();
        $userId = Auth::id();

        // Sales made today by this cashier
        $salesToday = Sale::with('items')
            ->where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->get();

        // Total sales value
        $totalSales = $salesToday->sum('total_amount');

        // Refunds DONE today by this cashier (derived from refund events)
        // $totalRefunds = SaleItem::whereIn('sale_id', function ($q) use ($userId, $today) {
        //     $q->select('reference_id')
        //         ->from('stock_movements')
        //         ->where('type', 'refund')
        //         ->whereDate('created_at', $today);
        // })
        //     ->sum('subtotal');
        $totalRefunds = DB::table('stock_movements')
            ->join('sale_items', function ($join) {
                $join->on('sale_items.sale_id', '=', 'stock_movements.reference_id')
                    ->on('sale_items.product_id', '=', 'stock_movements.product_id');
            })
            ->where('stock_movements.type', 'refund')
            ->whereDate('stock_movements.created_at', $today)
            ->whereIn('stock_movements.reference_id', function ($q) use ($userId) {
                $q->select('id')
                    ->from('sales')
                    ->where('user_id', $userId);
            })
            ->sum(DB::raw('stock_movements.quantity * sale_items.unit_price'));

        // Net result
        $netTotal = $totalSales - $totalRefunds;

        return view('pos.index', compact(
            'cart',
            'total',
            'salesToday',
            'totalSales',
            'totalRefunds',
            'netTotal'
        ));
    }
    // public function index()
    // {
    //     $cart = session('cart', []);
    //     $total = collect($cart)->sum(fn($item) => $item['subtotal']);

    //     $today = now()->toDateString();
    //     // $userId = auth()->id();
    //     $userId = Auth::id();

    //     $salesToday = Sale::with('items')
    //         ->where('user_id', $userId)
    //         ->whereDate('created_at', $today)
    //         ->get();

    //     $refundsToday = Sale::where('user_id', $userId)
    //         ->whereDate('refunded_at', $today)
    //         ->get();

    //     $totalSales = $salesToday->sum('total_amount');
    //     $totalRefunds = $refundsToday->sum('total_amount');
    //     $netTotal = $totalSales - $totalRefunds;

    //     return view('pos.index', compact(
    //         'cart',
    //         'total',
    //         'salesToday',
    //         'totalSales',
    //         'totalRefunds',
    //         'netTotal'
    //     ));
    // }

    public function add(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::where('barcode', $request->barcode)->firstOrFail();

        $cart = session('cart', []);

        if (isset($cart[$product->id])) {
            if ($cart[$product->id]['quantity'] + 1 > $product->currentStock()) {
                return back()->withErrors(['barcode' => 'Insufficient stock']);
            }

            $cart[$product->id]['quantity']++;
        } else {
            if ($product->currentStock() < 1) {
                return back()->withErrors(['barcode' => 'Out of stock']);
            }

            $cart[$product->id] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'unit_price' => $product->selling_price,
            ];
        }

        $cart[$product->id]['subtotal'] =
            $cart[$product->id]['quantity'] * $cart[$product->id]['unit_price'];

        session(['cart' => $cart]);

        return redirect()->route('pos.index');
    }

    public function checkout(Request $request, SaleService $saleService)
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return back()->withErrors(['cart' => 'Cart is empty']);
        }

        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $items = collect($cart)->map(fn($item) => [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
        ])->values()->toArray();

        $sale = $saleService->createSale(
            $request->user()->id,
            $items,
            $request->paid_amount
        );

        session()->forget('cart');

        return redirect()->route('sales.show', $sale);
    }
    // public function checkout(Request $request, SaleService $saleService)
    // {
    //     $cart = session('cart', []);

    //     if (empty($cart)) {
    //         return back()->withErrors(['cart' => 'Cart is empty']);
    //     }

    //     $request->validate([
    //         'paid_amount' => 'required|numeric|min:0',
    //     ]);

    //     $items = collect($cart)->map(fn($item) => [
    //         'product_id' => $item['product_id'],
    //         'quantity' => $item['quantity'],
    //     ])->values()->toArray();

    //     $saleService->createSale($request->user()->id, $items, $request->paid_amount);

    //     session()->forget('cart');

    //     return redirect()->route('pos.index')->with('success', 'Sale completed');
    // }

    public function remove(int $productId)
    {
        $cart = session('cart', []);

        unset($cart[$productId]);

        session(['cart' => $cart]);

        return redirect()->route('pos.index');
    }

    public function increase(int $productId)
    {
        $cart = session('cart', []);
        $product = Product::findOrFail($productId);

        if (!isset($cart[$productId])) {
            return redirect()->route('pos.index');
        }

        if ($cart[$productId]['quantity'] + 1 > $product->currentStock()) {
            return back()->withErrors(['cart' => 'Insufficient stock']);
        }

        $cart[$productId]['quantity']++;
        $cart[$productId]['subtotal'] =
            $cart[$productId]['quantity'] * $cart[$productId]['unit_price'];

        session(['cart' => $cart]);

        return redirect()->route('pos.index');
    }

    public function decrease(int $productId)
    {
        $cart = session('cart', []);

        if (!isset($cart[$productId])) {
            return redirect()->route('pos.index');
        }

        $cart[$productId]['quantity']--;

        if ($cart[$productId]['quantity'] <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['subtotal'] =
                $cart[$productId]['quantity'] * $cart[$productId]['unit_price'];
        }

        session(['cart' => $cart]);

        return redirect()->route('pos.index');
    }
}
