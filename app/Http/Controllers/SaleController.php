<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use App\Services\RefundService;
use App\Services\PartialRefundService;
use App\Models\SaleRefund;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load('items.product', 'user');

        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        //
    }

    public function refund(Sale $sale, RefundService $refundService, Request $request)
    {
        $request->validate([
            'refund_reason' => 'required|string|max:255',
        ]);

        $refundService->refund(
            $sale,
            $request->user()->id,
            $request->refund_reason
        );

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Sale refunded successfully.');
    }

    public function partialRefund(
        Sale $sale,
        PartialRefundService $service,
        Request $request
    ) {
        $request->validate([
            'refund_reason' => 'required|string|max:255',
            'items' => 'required|array',
            'items.*' => 'integer|min:0',
        ]);

        $service->refund(
            $sale,
            $request->user()->id,
            $request->items,
            $request->refund_reason
        );

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Partial refund processed.');
    }

    public function refundReceipt(Sale $sale, SaleRefund $refund)
    {
        abort_unless($refund->sale_id === $sale->id, 404);

        $refund->load('product', 'sale.user');

        return view('sales.refund-receipt', compact('sale', 'refund'));
    }
}
