<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoomFolioController extends Controller
{
    /**
     * Show the room folio (all unpaid/partial sales).
     */
    public function show(Room $room)
    {
        $room->load(['sales' => function($query) {
            $query->where('payment_status', '!=', 'paid')->with('items');
        }]);

        $balance = $room->currentBalance();
        
        // Also get payment history for this room
        $payments = Payment::where('room_id', $room->id)
            ->orWhereIn('sale_id', $room->sales->pluck('id'))
            ->latest()
            ->get();

        return view('rooms.folio', compact('room', 'balance', 'payments'));
    }

    /**
     * Settle the room debt or a specific order.
     */
    public function settle(Request $request, Room $room)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string|in:cash,bank,card,mobile_money',
            'remarks' => 'nullable|string|max:255',
            'sale_id' => 'nullable|exists:sales,id', // Target a specific order
        ]);

        $amountToPay = (float) $request->amount;
        $remainingPayment = $amountToPay;
        $targetSaleId = $request->sale_id;

        DB::transaction(function () use ($room, $amountToPay, $request, &$remainingPayment, $targetSaleId) {
            // 1. Record the Payment record
            Payment::create([
                'room_id' => $room->id,
                'sale_id' => $targetSaleId, // Links directly if provided
                'user_id' => Auth::id(),
                'amount' => $amountToPay,
                'method' => $request->method,
                'remarks' => $request->remarks,
            ]);

            // 2. Apply payment logic
            if ($targetSaleId) {
                // Targeted Settlement
                $sale = Sale::findOrFail($targetSaleId);
                $newPaidAmount = (float) $sale->paid_amount + $amountToPay;
                
                $status = 'partial';
                if ($newPaidAmount >= (float) $sale->total_amount) {
                    $newPaidAmount = $sale->total_amount;
                    $status = 'paid';
                }

                $sale->update([
                    'paid_amount' => $newPaidAmount,
                    'payment_status' => $status
                ]);
            } else {
                // Bulk Settlement (FIFO logic)
                $unpaidSales = $room->sales()
                    ->where('payment_status', '!=', 'paid')
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($unpaidSales as $sale) {
                    if ($remainingPayment <= 0) break;

                    $saleBalance = (float) ($sale->total_amount - $sale->paid_amount);
                    
                    if ($remainingPayment >= $saleBalance) {
                        $sale->update([
                            'paid_amount' => $sale->total_amount,
                            'payment_status' => 'paid'
                        ]);
                        $remainingPayment -= $saleBalance;
                    } else {
                        $newPaidAmount = (float) $sale->paid_amount + $remainingPayment;
                        $sale->update([
                            'paid_amount' => $newPaidAmount,
                            'payment_status' => 'partial'
                        ]);
                        $remainingPayment = 0;
                    }
                }
            }
        });

        return back()->with('success', 'Payment of ' . number_format($amountToPay, 2) . ' recorded successfully.');
    }
}
