<x-layouts::app title="Inventory Valuation">

    <h1 class="text-xl font-semibold mb-4">Inventory Valuation</h1>

    <table class="w-full border text-sm mb-4">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">Product</th>
                <th class="border px-2 py-1 text-center">Stock</th>
                <th class="border px-2 py-1 text-right">Last Cost</th>
                <th class="border px-2 py-1 text-right">Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $row)
                <tr>
                    <td class="border px-2 py-1">
                        {{ $row['product']->name }}
                    </td>
                    <td class="border px-2 py-1 text-center">
                        {{ $row['stock'] }}
                    </td>
                    <td class="border px-2 py-1 text-right">
                        {{ number_format($row['unit_cost'], 2) }}
                    </td>
                    <td class="border px-2 py-1 text-right">
                        {{ number_format($row['value'], 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-semibold">
                <td colspan="3" class="border px-2 py-1 text-right">
                    Total Inventory Value
                </td>
                <td class="border px-2 py-1 text-right">
                    {{ number_format($totalValue, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

</x-layouts::app>
