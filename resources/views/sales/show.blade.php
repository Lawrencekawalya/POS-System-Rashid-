<x-layouts::app title="Receipt">

    {{-- Top action bar
    <div class="flex justify-between mb-4 print:hidden">
        <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-gray-200 rounded">
            ← Back to POS
        </a>

        <button onclick="window.print()" class="px-4 py-2 bg-black text-white rounded">
            Print Receipt
        </button>
    </div>--}}
    <div class="flex justify-between mb-4 print:hidden">
        {{-- Only show Back to POS if the user is NOT an admin --}}
        @if(!auth()->user()->isAdmin()) 
            <a href="{{ route('pos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to POS
            </a>
        @else
            {{-- Optional: Show a different link for Admins, like Back to Salse --}}
            <a href="{{ route('sales.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                Back to Sales 
            </a>
        @endif

        <button onclick="window.print()" class="inline-flex items-center px-5 py-2 bg-gray-900 hover:bg-black text-white font-bold rounded-lg shadow-sm transition-all transform active:scale-95">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print Receipt
        </button>
    </div>
    

    {{-- Refund section --}}
    @if (!$sale->isRefunded())
        <form method="POST" action="{{ route('sales.partial-refund', $sale) }}" class="print:hidden space-y-2">
            @csrf

            <input type="text" name="refund_reason" required placeholder="Refund reason"
                class="w-full border px-2 py-1 text-sm rounded">

            <table class="w-full text-sm border">
                @foreach ($sale->items as $item)
                    @php
                        $refunded = $sale->refundedQuantityFor($item->product_id);
                        $remaining = $item->quantity - $refunded;
                    @endphp

                    @if ($remaining > 0)
                        <tr>
                            <td class="border px-2 py-1">{{ $item->product->name }}</td>
                            <td class="border px-2 py-1 text-center">
                                {{ $remaining }} left
                            </td>
                            <td class="border px-2 py-1">
                                <input type="number" name="items[{{ $item->product_id }}]" min="0"
                                    max="{{ $remaining }}" class="w-full border px-1">
                            </td>
                        </tr>
                    @endif
                @endforeach
            </table>

            <button class="px-3 py-1 bg-red-600 text-white rounded text-sm">
                Process Partial Refund
            </button>
        </form>
    @endif


    {{-- Refund history --}}
    @if ($sale->refunds->count())
        <div class="print:hidden mb-4 text-sm border rounded p-3 bg-gray-50">
            <p class="font-semibold mb-2">Refunds</p>

            <ul class="space-y-1">
                @foreach ($sale->refunds as $refund)
                    <li class="flex justify-between items-center">
                        <span>
                            Refund #{{ $refund->id }}
                            <span class="text-gray-500 text-xs">
                                ({{ $refund->created_at->format('Y-m-d H:i') }})
                            </span>
                        </span>

                        <a href="{{ route('sales.refund-receipt', [$sale, $refund]) }}"
                            class="text-blue-600 text-xs hover:underline">
                            View receipt
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif



    {{-- Thermal receipt --}}
    <div class="thermal-receipt mx-auto">

        <div class="text-center mb-2">
            <img src="{{ asset('images/og-share.png') }}" alt="Palace Hotel Logo" class="mx-auto">
            <h1 class="font-bold text-base">Palace Hotel</h1>
            <p class="text-xs">Mbarara, Uganda</p>
            <p class="text-xs">Receipt #{{ $sale->id }}</p>
            <p class="text-xs">
                {{ $sale->created_at->format('Y-m-d H:i') }}
            </p>
            <p class="text-xs">Cashier: {{ $sale->user->name }}</p>
        </div>

        <hr class="my-1">

        <table class="w-full text-xs">
            <thead>
                <tr>
                    <th class="text-left">Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Amt</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td class="text-left">
                            {{ $item->product->name }}
                        </td>
                        <td class="text-center">
                            {{ $item->quantity }}
                        </td>
                        <td class="text-right">
                            {{ number_format($item->subtotal, 0) }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right text-[10px]">
                            @ {{ number_format($item->unit_price, 0) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <hr class="my-1">

        <div class="text-xs space-y-1">
            <div class="flex justify-between font-semibold">
                <span>Total</span>
                <span>{{ number_format($sale->total_amount, 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Paid</span>
                <span>{{ number_format($sale->paid_amount, 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Change</span>
                <span>{{ number_format($sale->change_amount, 0) }}</span>
            </div>
        </div>

        <hr class="my-2">

        <p class="text-center text-xs">
            Thank you for shopping<br>
            Please come again!
        </p>
    </div>

    {{-- Thermal print styles --}}
    <style>
        .thermal-receipt {
            width: 80mm;
            margin: 0 auto;
            font-family: monospace;
            font-size: 12px;
            color: black;
            background: white;
        }

        @media print {

            @page {
                size: 80mm auto;
                /* THIS IS THE IMPORTANT PART */
                margin: 0;
            }

            html,
            body {
                width: 80mm;
                margin: 0;
                padding: 0;
                background: white;
            }

            body * {
                visibility: hidden;
            }

            .thermal-receipt,
            .thermal-receipt * {
                visibility: visible;
            }

            .thermal-receipt {
                position: absolute;
                left: 0;
                top: 0;
            }
        }
    </style>

</x-layouts::app>
