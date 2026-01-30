<x-layouts::app title="Refund Receipt">

    {{-- Actions --}}
    <div class="flex justify-between mb-4 print:hidden">
        <a href="{{ route('sales.show', $sale) }}" class="px-3 py-1 bg-gray-200 rounded text-sm">
            ← Back to Receipt
        </a>

        <button onclick="window.print()" class="px-3 py-1 bg-black text-white rounded text-sm">
            Print
        </button>
    </div>

    {{-- Thermal --}}
    <div class="thermal-receipt mx-auto">

        <div class="text-center mb-2">
            <h1 class="font-bold text-base">YOUR SHOP NAME</h1>
            <p class="text-xs font-semibold">REFUND RECEIPT</p>
            <p class="text-xs">Refund #{{ $refund->id }}</p>
            <p class="text-xs">Sale #{{ $sale->id }}</p>
            <p class="text-xs">{{ $refund->created_at->format('Y-m-d H:i') }}</p>
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
                <tr>
                    <td>{{ $refund->product->name }}</td>
                    <td class="text-center">{{ $refund->quantity }}</td>
                    <td class="text-right">{{ number_format($refund->amount, 0) }}</td>
                </tr>
            </tbody>
        </table>

        <hr class="my-1">

        <div class="text-xs space-y-1">
            <div class="flex justify-between font-semibold">
                <span>Refund Total</span>
                <span>{{ number_format($refund->amount, 0) }}</span>
            </div>
        </div>

        <hr class="my-2">

        <p class="text-xs">
            Reason: {{ $refund->reason }}
        </p>

        <p class="mt-4 text-center text-xs">
            Refunded items returned to stock
        </p>
    </div>

    <style>
        .thermal-receipt {
            width: 80mm;
            padding: 4mm;
            background: white;
            color: black;
            font-family: monospace;
        }

        @media print {
            body {
                background: white !important;
            }

            .print\:hidden {
                display: none !important;
            }

            .thermal-receipt {
                margin: 0;
                padding: 0;
            }
        }
    </style>

</x-layouts::app>
