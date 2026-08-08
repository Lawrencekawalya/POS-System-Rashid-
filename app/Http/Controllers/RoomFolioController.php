<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Room;
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
        $room->load(['sales' => function ($query) {
            $query->whereNotIn('payment_status', ['paid', 'refunded'])->with('items');
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
            if ($targetSaleId) {
                $sales = $room->sales()
                    ->whereKey($targetSaleId)
                    ->whereNotIn('payment_status', ['paid', 'refunded'])
                    ->lockForUpdate()
                    ->get();

                if ($sales->isEmpty()) {
                    abort(422, 'The selected sale does not belong to this room or has already been paid.');
                }
            } else {
                $sales = $room->sales()
                    ->whereNotIn('payment_status', ['paid', 'refunded'])
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();
            }

            $outstandingBalance = $sales->sum(fn (Sale $sale) => $sale->balance);

            if ($amountToPay > $outstandingBalance) {
                abort(422, 'Payment exceeds the outstanding room balance.');
            }

            foreach ($sales as $sale) {
                if ($remainingPayment <= 0) {
                    break;
                }

                $saleBalance = $sale->balance;
                $allocation = min($remainingPayment, $saleBalance);
                $newPaidAmount = (float) $sale->paid_amount + $allocation;

                Payment::create([
                    'room_id' => $room->id,
                    'sale_id' => $sale->id,
                    'user_id' => Auth::id(),
                    'amount' => $allocation,
                    'method' => $request->method,
                    'remarks' => $request->remarks,
                ]);

                $sale->update([
                    'paid_amount' => $newPaidAmount,
                    'payment_status' => $newPaidAmount + (float) ($sale->refunded_amount ?? 0) >= (float) $sale->total_amount ? 'paid' : 'partial',
                ]);

                $remainingPayment -= $allocation;
            }
        });

        return back()->with('success', 'Payment of '.number_format($amountToPay, 2).' recorded successfully.');
    }
}
