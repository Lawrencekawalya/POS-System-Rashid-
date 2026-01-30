<x-layouts::app title="Inventory Turnover">

    <h1 class="text-xl font-semibold mb-4">Inventory Turnover</h1>

    <form method="GET" class="flex gap-2 mb-4">
        <input type="date" name="start_date" value="{{ $startDate }}" class="border px-2 py-1">
        <input type="date" name="end_date" value="{{ $endDate }}" class="border px-2 py-1">
        <button class="bg-black text-white px-3 py-1">Apply</button>
    </form>

    <table class="w-full border text-sm">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">Product</th>
                <th class="border px-2 py-1 text-center">Qty Sold</th>
                <th class="border px-2 py-1 text-center">Avg Stock</th>
                <th class="border px-2 py-1 text-center">Turnover</th>
                <th class="border px-2 py-1">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $row)
                <tr>
                    <td class="border px-2 py-1">{{ $row['product']->name }}</td>
                    <td class="border px-2 py-1 text-center">{{ $row['qty_sold'] }}</td>
                    <td class="border px-2 py-1 text-center">{{ $row['avg_stock'] }}</td>
                    <td class="border px-2 py-1 text-center">{{ $row['turnover'] }}</td>
                    <td class="border px-2 py-1">
                        @if ($row['turnover'] == 0)
                            <span class="text-gray-500">Dead</span>
                        @elseif ($row['turnover'] < 1)
                            <span class="text-red-600">Slow</span>
                        @elseif ($row['turnover'] < 3)
                            <span class="text-yellow-600">Average</span>
                        @else
                            <span class="text-green-600">Fast</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</x-layouts::app>
