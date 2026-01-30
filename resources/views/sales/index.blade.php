<x-layouts::app title="Sales History">

    <h1 class="text-xl font-semibold mb-4">Sales History</h1>

    @if ($sales->count())
        <table class="w-full border text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1">#</th>
                    <th class="border px-2 py-1">Date</th>
                    <th class="border px-2 py-1">Cashier</th>
                    <th class="border px-2 py-1 text-right">Total</th>
                    <th class="border px-2 py-1 text-right">Paid</th>
                    <th class="border px-2 py-1 text-right">Change</th>
                    <th class="border px-2 py-1">Status</th>
                    <th class="border px-2 py-1">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sales as $sale)
                    <tr>
                        <td class="border px-2 py-1">{{ $sale->id }}</td>
                        <td class="border px-2 py-1">
                            {{ $sale->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="border px-2 py-1">
                            {{ $sale->user->name }}
                        </td>
                        <td class="border px-2 py-1 text-right">
                            {{ number_format($sale->total_amount, 2) }}
                        </td>
                        <td class="border px-2 py-1 text-right">
                            {{ number_format($sale->paid_amount, 2) }}
                        </td>
                        <td class="border px-2 py-1 text-right">
                            {{ number_format($sale->change_amount, 2) }}
                        </td>
                        <td class="border px-2 py-1">
                            @if ($sale->isRefunded())
                                <span class="text-red-600">Refunded</span>
                            @else
                                <span class="text-green-600">Completed</span>
                            @endif
                        </td>
                        <td class="border px-2 py-1">
                            <a href="{{ route('sales.show', $sale) }}" class="text-blue-600">
                                View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $sales->links() }}
        </div>
    @else
        <p>No sales found.</p>
    @endif

</x-layouts::app>
