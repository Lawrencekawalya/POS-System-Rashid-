<x-layouts::app title="Purchase History">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold mb-4">Purchase History</h1>

        <form method="GET" class="flex gap-2 mb-4">
            <input type="date" name="start_date" class="border px-2 py-1">
            <input type="date" name="end_date" class="border px-2 py-1">
            <button class="bg-black text-white px-3 py-1">Filter</button>
        </form>

        <a href="{{ route('purchases.create') }}" class="px-4 py-2 bg-black text-white rounded text-sm">
            + New Stock In
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 text-green-600">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full border text-sm">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">Date</th>
                <th class="border px-2 py-1">Supplier</th>
                <th class="border px-2 py-1">Reference</th>
                <th class="border px-2 py-1">Total Cost</th>
                <th class="border px-2 py-1">Recorded By</th>
                <th class="border px-2 py-1">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchases as $purchase)
                <tr>
                    <td class="border px-2 py-1">
                        {{ $purchase->purchased_at->format('Y-m-d H:i') }}
                    </td>
                    <td class="border px-2 py-1">
                        {{ $purchase->supplier_name ?? '-' }}
                    </td>
                    <td class="border px-2 py-1">
                        {{ $purchase->reference_no ?? '-' }}
                    </td>
                    <td class="border px-2 py-1">
                        {{ number_format($purchase->total_cost, 2) }}
                    </td>
                    <td class="border px-2 py-1">
                        {{ $purchase->user->name }}
                    </td>
                    <td class="border px-2 py-1">
                        <a href="{{ route('purchases.show', $purchase) }}" class="text-blue-600">View</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</x-layouts::app>
