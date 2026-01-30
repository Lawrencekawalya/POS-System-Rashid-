<x-layouts::app title="Receipt">

    {{-- Action buttons (not printed) --}}
    <div class="flex justify-between mb-4 print:hidden">
        <a href="{{ route('pos.index') }}" class="px-3 py-1 bg-gray-200 rounded text-sm">
            ← Back to POS
        </a>

        <button onclick="window.print()" class="px-3 py-1 bg-black text-white rounded text-sm">
            Print
        </button>
        @if (!$sale->isRefunded())
        <form method="POST" action="{{ route('sales.refund', $sale) }}"
            onsubmit="return confirm('Refund this sale? Stock will be restored.')" class="print:hidden mb-4 space-y-2">
            @csrf

            <input type="text" name="refund_reason" required placeholder="Reason for refund"
                class="w-full border px-2 py-1 text-sm rounded">

            <button class="px-3 py-1 bg-red-600 text-white rounded text-sm">
                Refund Sale
            </button>
        </form>
        @else
        <p class="print:hidden text-red-600 text-sm mb-2">
            Refunded on {{ $sale->refunded_at->format('Y-m-d H:i') }}
        </p>
        <p class="print:hidden text-sm">
            Reason: {{ $sale->refund_reason }}
        </p>
        @endif

    </div>

    {{-- Thermal receipt --}}
    <div class="thermal-receipt mx-auto">

        <div class="text-center mb-2">
            <h1 class="font-bold text-base">YOUR SHOP NAME</h1>
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
            Thank you for shopping
        </p>
    </div>

    {{-- Thermal print styles --}}
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
                width: 80mm;
                margin: 0;
                padding: 0;
            }
        }
    </style>

</x-layouts::app>


<x-layouts::app title="Receipt">

    {{-- Top action bar --}}
    <div class="flex justify-between items-center mb-4 print:hidden">
        <a href="{{ route('pos.index') }}" class="px-3 py-1 bg-gray-200 rounded text-sm">
            ← Back to POS
        </a>

        <button onclick="window.print()" class="px-3 py-1 bg-black text-white rounded text-sm">
            Print
        </button>
    </div>

    {{-- Refund section --}}
    @if (!$sale->isRefunded())
    <div class="print:hidden mb-4 p-3 border border-red-200 rounded bg-red-50">
        <form method="POST" action="{{ route('sales.refund', $sale) }}"
            onsubmit="return confirm('Refund this sale? Stock will be restored.')" class="flex items-center gap-2">
            @csrf

            <input type="text" name="refund_reason" required placeholder="Reason for refund"
                class="flex-1 border px-2 py-1 text-sm rounded focus:border-red-400 focus:ring-0">

            <button class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm whitespace-nowrap">
                Refund
            </button>
        </form>
    </div>
    @else
    <div class="print:hidden mb-4 text-sm">
        <p class="text-red-600">
            Refunded on {{ $sale->refunded_at->format('Y-m-d H:i') }}
        </p>
        <p class="text-gray-700">
            Reason: {{ $sale->refund_reason }}
        </p>
    </div>
    @endif

    {{-- Thermal receipt --}}
    <div class="thermal-receipt mx-auto">

        <div class="text-center mb-2">
            <h1 class="font-bold text-base">YOUR SHOP NAME</h1>
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
            Thank you for shopping
        </p>
    </div>

    {{-- Thermal print styles --}}
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
                width: 80mm;
                margin: 0;
                padding: 0;
            }
        }
    </style>

</x-layouts::app>
/////////////////////////////////////////////////////////////////////////
<x-layouts::app title="Receipt">

    {{-- Top action bar --}}
    <div class="flex justify-between mb-4 print:hidden">
        <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-gray-200 rounded">
            ← Back to POS
        </a>

        <button onclick="window.print()" class="px-4 py-2 bg-black text-white rounded">
            Print Receipt
        </button>
    </div>

    {{-- Refund section --}}
    @if (!$sale->isRefunded())
    <div class="print:hidden mb-4 p-3 border border-red-200 rounded bg-red-50">
        <form method="POST" action="{{ route('sales.refund', $sale) }}"
            onsubmit="return confirm('Refund this sale? Stock will be restored.')" class="flex items-center gap-2">
            @csrf

            <input type="text" name="refund_reason" required placeholder="Reason for refund"
                class="flex-1 border px-2 py-1 text-sm rounded focus:border-red-400 focus:ring-0">

            <button class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm whitespace-nowrap">
                Refund
            </button>
        </form>
    </div>
    @else
    <div class="print:hidden mb-4 text-sm">
        <p class="text-red-600">
            Refunded on {{ $sale->refunded_at->format('Y-m-d H:i') }}
        </p>
        <p class="text-gray-700">
            Reason: {{ $sale->refund_reason }}
        </p>
    </div>
    @endif

    {{-- Thermal receipt --}}
    <div class="thermal-receipt mx-auto">

        <div class="text-center mb-2">
            <h1 class="font-bold text-base">YOUR SHOP NAME</h1>
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
            Thank you for shopping
        </p>
    </div>

    {{-- Thermal print styles --}}
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
                width: 80mm;
                margin: 0;
                padding: 0;
            }
        }
    </style>

</x-layouts::app>

/////////////////////////////////////////////////////////////////////////