<x-layouts::app title="Purchase #{{ $purchase->id }}">

    <div class="flex justify-between mb-4">
        <h1 class="text-xl font-semibold">
            Purchase #{{ $purchase->id }}
        </h1>

        <a href="{{ route('purchases.index') }}" class="text-sm text-gray-600">← Back</a>
    </div>

    <div class="mb-4 text-sm">
        <p><strong>Date:</strong> {{ $purchase->purchased_at }}</p>
        <p><strong>Supplier:</strong> {{ $purchase->supplier_name ?? '-' }}</p>
        <p><strong>Reference:</strong> {{ $purchase->reference_no ?? '-' }}</p>
        <p><strong>Recorded by:</strong> {{ $purchase->user->name }}</p>
    </div>

    <table class="w-full border text-sm mb-4">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">Product</th>
                <th class="border px-2 py-1">Qty</th>
                <th class="border px-2 py-1">Unit Cost</th>
                <th class="border px-2 py-1">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $item)
                <tr>
                    <td class="border px-2 py-1">
                        {{ $item->product->name }}
                    </td>
                    <td class="border px-2 py-1 text-center">
                        {{ $item->quantity }}
                    </td>
                    <td class="border px-2 py-1">
                        {{ number_format($item->unit_cost, 2) }}
                    </td>
                    <td class="border px-2 py-1">
                        {{ number_format($item->subtotal, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="font-semibold">
        Total Cost: {{ number_format($purchase->total_cost, 2) }}
    </div>

</x-layouts::app>
