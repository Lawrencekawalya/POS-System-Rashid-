<x-layouts::app title="POS Checkout">

    <h1 class="text-xl font-semibold mb-4">POS Checkout</h1>

    @if ($errors->any())
        <div class="mb-3 text-red-600">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('success'))
        <div class="mb-3 text-green-600">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('pos.add') }}" class="mb-4">
        @csrf
        <input type="text" name="barcode" autofocus placeholder="Scan barcode" class="border px-3 py-2 w-full">
    </form>

    <table class="w-full border mb-4">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">Product</th>
                <th class="border px-2 py-1">Qty</th>
                <th class="border px-2 py-1">Price</th>
                <th class="border px-2 py-1">Subtotal</th>
                <th class="border px-2 py-1">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($cart as $item)
                <tr>
                    <td class="border px-2 py-1">{{ $item['name'] }}</td>
                    {{-- <td class="border px-2 py-1 text-center">{{ $item['quantity'] }}</td> --}}
                    <td class="border px-2 py-1 text-center">
                        <form method="POST" action="{{ route('pos.decrease', $item['product_id']) }}" class="inline">
                            @csrf
                            <button class="px-2">−</button>
                        </form>

                        {{ $item['quantity'] }}

                        <form method="POST" action="{{ route('pos.increase', $item['product_id']) }}" class="inline">
                            @csrf
                            <button class="px-2">+</button>
                        </form>
                    </td>
                    <td class="border px-2 py-1">{{ number_format($item['unit_price'], 2) }}</td>
                    <td class="border px-2 py-1">{{ number_format($item['subtotal'], 2) }}</td>
                    <td class="border px-2 py-1 text-center">
                        <form method="POST" action="{{ route('pos.remove', $item['product_id']) }}">
                            @csrf
                            <button class="text-red-600 text-sm">Remove</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-3">Cart is empty</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mb-4 font-semibold">
        Total: {{ number_format($total, 2) }}
    </div>

    <form method="POST" action="{{ route('pos.checkout') }}">
        @csrf
        <input type="number" name="paid_amount" step="0.01" placeholder="Cash received"
            class="border px-3 py-2 w-full mb-2">
        <button class="bg-black text-white px-4 py-2 w-full">
            Complete Sale
        </button>
    </form>
    <hr class="my-6">

    <h2 class="text-lg font-semibold mb-2">Today’s Sales Summary</h2>

    <div class="grid grid-cols-3 gap-4 mb-4 text-sm">
        <div class="p-3 border rounded">
            <div class="text-gray-500">Total Sales</div>
            <div class="font-semibold">{{ number_format($totalSales, 2) }}</div>
        </div>

        <div class="p-3 border rounded">
            <div class="text-gray-500">Refunds</div>
            <div class="font-semibold text-red-600">
                {{ number_format($totalRefunds, 2) }}
            </div>
        </div>

        <div class="p-3 border rounded">
            <div class="text-gray-500">Net</div>
            <div class="font-semibold">
                {{ number_format($netTotal, 2) }}
            </div>
        </div>
    </div>

    @if ($salesToday->count())
        <table class="w-full border text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1">Receipt</th>
                    <th class="border px-2 py-1">Time</th>
                    <th class="border px-2 py-1">Amount</th>
                    <th class="border px-2 py-1">Status</th>
                    <th class="border px-2 py-1">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($salesToday as $sale)
                    <tr>
                        <td class="border px-2 py-1 text-center">
                            #{{ $sale->id }}
                        </td>
                        <td class="border px-2 py-1">
                            {{ $sale->created_at->format('H:i') }}
                        </td>
                        <td class="border px-2 py-1">
                            {{ number_format($sale->total_amount, 2) }}
                        </td>
                        <td class="border px-2 py-1">
                            @if ($sale->isRefunded())
                                <span class="text-red-600">Refunded</span>
                            @else
                                <span class="text-green-600">Sold</span>
                            @endif
                        </td>
                        <td class="border px-2 py-1 text-center">
                            <a href="{{ route('sales.show', $sale) }}" class="text-blue-600 text-xs">
                                View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-sm text-gray-500">
            No sales recorded today.
        </p>
    @endif

</x-layouts::app>
