<x-layouts::app title="Product Profitability">

    <h1 class="text-xl font-semibold mb-4">Product Profitability</h1>

    <table class="w-full border text-sm">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">Product</th>
                <th class="border px-2 py-1 text-center">Qty Sold</th>
                <th class="border px-2 py-1 text-right">Revenue</th>
                <th class="border px-2 py-1 text-right">COGS</th>
                <th class="border px-2 py-1 text-right">Profit</th>
                <th class="border px-2 py-1 text-right">Margin %</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $row)
                <tr>
                    <td class="border px-2 py-1">
                        {{ $row['product']->name }}
                    </td>
                    <td class="border px-2 py-1 text-center">
                        {{ $row['qty_sold'] }}
                    </td>
                    <td class="border px-2 py-1 text-right">
                        {{ number_format($row['revenue'], 2) }}
                    </td>
                    <td class="border px-2 py-1 text-right">
                        {{ number_format($row['cogs'], 2) }}
                    </td>
                    <td
                        class="border px-2 py-1 text-right
                        {{ $row['profit'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($row['profit'], 2) }}
                    </td>
                    <td class="border px-2 py-1 text-right">
                        {{ number_format($row['margin'], 1) }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</x-layouts::app>
