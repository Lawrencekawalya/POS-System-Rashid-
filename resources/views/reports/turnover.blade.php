{{-- <x-layouts::app title="Inventory Turnover">

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
                    @elseif ($row['turnover'] < 1) <span class="text-red-600">Slow</span>
                        @elseif ($row['turnover'] < 3) <span class="text-yellow-600">Average</span>
                            @else
                            <span class="text-green-600">Fast</span>
                            @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</x-layouts::app>--}}

<x-layouts::app title="Inventory Turnover">
    <div class="min-h-screen bg-gray-50 pb-12">

        <div class="bg-white border-b border-gray-200 mb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Inventory Turnover</h1>
                        <p class="mt-1 text-sm text-gray-500">Analyze how quickly stock is being sold and replaced over
                            time.</p>
                    </div>

                    {{-- Date Filter --}}
                    <form method="GET" class="mt-4 md:mt-0 flex items-center gap-2">
                        <div class="flex items-center bg-white border border-gray-300 rounded-lg px-3 py-1 shadow-sm">
                            <input type="date" name="start_date" value="{{ $startDate }}"
                                class="border-none focus:ring-0 text-sm">
                            <span class="text-gray-400 mx-2">to</span>
                            <input type="date" name="end_date" value="{{ $endDate }}"
                                class="border-none focus:ring-0 text-sm">
                        </div>
                        <button
                            class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-bold transition">
                            Apply
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Analytics Legend --}}
            <div class="flex flex-wrap gap-4 mb-6">
                <div
                    class="flex items-center text-xs font-medium text-gray-500 bg-white border border-gray-200 px-3 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span> Fast Moving (3+)
                </div>
                <div
                    class="flex items-center text-xs font-medium text-gray-500 bg-white border border-gray-200 px-3 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-yellow-500 mr-2"></span> Average (1-3)
                </div>
                <div
                    class="flex items-center text-xs font-medium text-gray-500 bg-white border border-gray-200 px-3 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span> Slow Moving (<1) </div>
                        <div
                            class="flex items-center text-xs font-medium text-gray-500 bg-white border border-gray-200 px-3 py-1.5 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-gray-400 mr-2"></span> Dead Stock (0)
                        </div>
                </div>

                <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Product</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Qty Sold</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Avg. Stock Level</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Turnover Rate</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Movement Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($products as $row)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $row['product']->name }}</div>
                                       {{--<div class="text-xs text-gray-400">{{ $row['product']->category->name ?? 'Uncategorized' }}</div>--}}
                                        <div class="text-xs text-gray-400">ID: {{ $row['product']->id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-700">
                                        {{ number_format($row['qty_sold']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ number_format($row['avg_stock'], 1) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="text-sm font-mono font-bold text-gray-900">
                                            {{ number_format($row['turnover'], 2) }}x</div>
                                        {{-- Mini Visual Bar --}}
                                        <div class="w-16 h-1 bg-gray-100 mx-auto mt-1 rounded-full overflow-hidden">
                                            <div class="h-full {{ $row['turnover'] >= 3 ? 'bg-green-500' : ($row['turnover'] >= 1 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                style="width: {{ min(($row['turnover'] / 5) * 100, 100) }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if ($row['turnover'] == 0)
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                                DEAD STOCK
                                            </span>
                                        @elseif ($row['turnover'] < 1)
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                                SLOW
                                            </span>
                                        @elseif ($row['turnover'] < 3)
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-yellow-50 text-yellow-700 border border-yellow-100">
                                                AVERAGE
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                                                FAST MOVING
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
</x-layouts::app>