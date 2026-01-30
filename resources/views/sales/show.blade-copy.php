<x-layouts::app title="Receipt">

    {{-- Action buttons (hidden when printing) --}}
    <div class="flex justify-between mb-4 print:hidden">
        <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-gray-200 rounded">
            ← Back to POS
        </a>

        <button onclick="window.print()" class="px-4 py-2 bg-black text-white rounded">
            Print Receipt
        </button>
    </div>

    {{-- Receipt --}}
    <div class="max-w-md mx-auto">

        <h1 class="text-xl font-semibold mb-2 text-center">
            Receipt #{{ $sale->id }}
        </h1>

        <p class="text-sm mb-1 text-center">
            Cashier: {{ $sale->user->name }}
        </p>
        <p class="text-sm mb-4 text-center">
            {{ $sale->created_at->format('Y-m-d H:i') }}
        </p>

        <table class="w-full border mb-4 text-sm">
            <thead>
                <tr class="border-b">
                    <th class="py-1 text-left">Product</th>
                    <th class="py-1 text-center">Qty</th>
                    <th class="py-1 text-right">Price</th>
                    <th class="py-1 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr class="border-b">
                        <td class="py-1">{{ $item->product->name }}</td>
                        <td class="py-1 text-center">{{ $item->quantity }}</td>
                        <td class="py-1 text-right">
                            {{ number_format($item->unit_price, 2) }}
                        </td>
                        <td class="py-1 text-right">
                            {{ number_format($item->subtotal, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-sm space-y-1">
            <div class="flex justify-between font-semibold">
                <span>Total</span>
                <span>{{ number_format($sale->total_amount, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Paid</span>
                <span>{{ number_format($sale->paid_amount, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Change</span>
                <span>{{ number_format($sale->change_amount, 2) }}</span>
            </div>
        </div>

        <p class="mt-6 text-center text-xs">
            Thank you for your purchase
        </p>

    </div>

    {{-- Print styles --}}
    <style>
        @media print {
            body {
                background: white;
            }

            .print\:hidden {
                display: none !important;
            }
        }
    </style>

</x-layouts::app>
