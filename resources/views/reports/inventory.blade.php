{{--<x-layouts::app title="Inventory Valuation">

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

</x-layouts::app>--}}

<x-layouts::app title="Inventory Valuation">
    <div class="min-h-screen bg-gray-50 pb-12">
        
        <div class="bg-white border-b border-gray-200 mb-8 print:hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl tracking-tight">
                            Inventory Valuation
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Estimated total asset value based on current stock levels and last purchase costs.
                        </p>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4 gap-3">
                        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Download Report
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-indigo-900 rounded-2xl shadow-xl overflow-hidden mb-8 text-white">
                <div class="px-8 py-10 flex flex-col md:flex-row justify-between items-center">
                    <div>
                        <p class="text-indigo-200 text-sm font-bold uppercase tracking-widest mb-1">Total Warehouse Value</p>
                        <h2 class="text-4xl md:text-5xl font-black italic">
                            <span class="text-xl font-normal not-italic opacity-70">UGX</span> {{ number_format($totalValue) }}
                        </h2>
                    </div>
                    <div class="mt-6 md:mt-0 text-right">
                        <p class="text-indigo-200 text-sm font-bold uppercase tracking-widest mb-1">Total Skus</p>
                        <p class="text-3xl font-bold">{{ count($products) }} <span class="text-sm font-normal opacity-70 tracking-normal">Unique Products</span></p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Product Description</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Stock On Hand</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Avg. Unit Cost</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Asset Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($products as $row)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $row['product']->name }}</div>
                                        <div class="text-xs text-gray-400">Category: {{ $row['product']->category->name ?? 'General' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($row['stock'] <= 5)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                {{ $row['stock'] }}
                                            </span>
                                        @else
                                            <span class="text-sm font-medium text-gray-700">{{ $row['stock'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 font-mono">
                                        {{ number_format($row['unit_cost']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900 font-mono">
                                        {{ number_format($row['value']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-5 text-right text-sm font-bold text-gray-500 uppercase tracking-widest">
                                    Final Valuation Sum
                                </td>
                                <td class="px-6 py-5 text-right whitespace-nowrap text-lg font-black text-indigo-700 font-mono">
                                    {{ number_format($totalValue) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="mt-6 text-center">
                <p class="text-xs text-gray-400">
                    Report generated on {{ now()->format('M d, Y H:i A') }} • Prices based on Last In, First Out (LIFO) valuation logic.
                </p>
            </div>
        </div>
    </div>
</x-layouts::app>
