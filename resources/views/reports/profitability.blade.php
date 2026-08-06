{{--<x-layouts::app title="Product Profitability">

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

</x-layouts::app>--}}

<x-layouts::app title="Product Profitability">
    <div class="max-w-7xl mx-auto px-4 py-6">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Product Profitability</h1>
                <p class="text-sm text-gray-500">Track which items are driving your business growth.</p>
            </div>

            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->toDateString()) }}"
                        class="border-gray-200 rounded-lg text-sm focus:ring-black focus:border-black">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date', now()->toDateString()) }}"
                        class="border-gray-200 rounded-lg text-sm focus:ring-black focus:border-black">
                </div>
                <button type="submit" class="bg-black text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Apply Filter
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase">Total Revenue</p>
                <p class="text-2xl font-black text-gray-900">{{ number_format(collect($products)->sum('revenue')) }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase">Total COGS</p>
                <p class="text-2xl font-black text-gray-600">{{ number_format(collect($products)->sum('cogs')) }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-blue-100 shadow-sm bg-blue-50/30">
                <p class="text-xs font-bold text-blue-500 uppercase">Gross Profit</p>
                <p class="text-2xl font-black text-blue-600">{{ number_format(collect($products)->sum('profit')) }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-green-100 shadow-sm bg-green-50/30">
                <p class="text-xs font-bold text-green-500 uppercase">Avg. Margin</p>
                <p class="text-2xl font-black text-green-600">
                    {{ collect($products)->avg('margin') > 0 ? number_format(collect($products)->avg('margin'), 1) : 0 }}%
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Product Detail</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Qty</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Revenue</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">COGS</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Net Profit</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($products as $row)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800">{{ $row['product']->name }}</div>
                                <div class="text-xs text-gray-400 font-mono">{{ $row['product']->barcode }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-medium text-gray-600">
                                {{ number_format($row['qty_sold']) }}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900 font-medium">
                                {{ number_format($row['revenue']) }}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-400">
                                {{ number_format($row['cogs']) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-2 py-1 rounded text-sm font-bold {{ $row['profit'] < 0 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                                    {{ number_format($row['profit']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                        <div class="h-full {{ $row['margin'] > 20 ? 'bg-green-500' : 'bg-yellow-500' }}" style="width: {{ $row['margin'] }}%"></div>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700">{{ number_format($row['margin'], 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts::app>