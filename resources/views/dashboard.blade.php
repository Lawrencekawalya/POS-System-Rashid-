<x-layouts::app :title="__('Dashboard')">

    {{-- 1. Top KPI Row (4 Columns) --}}
    <div class="grid gap-4 md:grid-cols-4">
        <div class="p-4 bg-black text-white rounded-xl border border-neutral-800 shadow-sm">
            <div class="text-neutral-400 text-xs uppercase tracking-wider font-bold">Cumulative Revenue</div>
            <div class="text-2xl font-bold mt-1 text-blue-400">{{ number_format($cumulativeRevenue, 0) }}</div>
            <div class="text-xs text-neutral-500 mt-2">All-time total</div>
        </div>

        <x-dashboard.kpi-card label="Net Sales Today" :value="number_format($netSales, 0)" :subtext="'Gross: ' . number_format($grossSales, 0)" />
        <x-dashboard.kpi-card label="Cash Expected" :value="number_format($cashExpected, 0)" :subtext="$cashExpected >= 0 ? 'Balanced' : 'Short'" />
        <x-dashboard.kpi-card label="Refunds Today" :value="number_format($refundTotal, 0)" :subtext="$refundCount . ' refunds'" />
    </div>

    {{-- 2. Insights Row (Top Sellers & Low Stock side-by-side) --}}
    <div class="grid gap-6 md:grid-cols-2 mt-6">

        {{-- Top Selling Products --}}
        <div class="rounded-xl border border-neutral-200 bg-white shadow-sm overflow-hidden">
            <div class="p-4 font-semibold border-b bg-gray-50 flex items-center justify-between">
                <span>🔥 Top 5 Sellers Today</span>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2 text-left">Product</th>
                        <th class="px-4 py-2 text-center">Qty</th>
                        <th class="px-4 py-2 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    {{-- {{ $product->name }}--}}
                    @forelse($topProducts as $index => $item)
                        {{-- @php $item = (array) $item; @endphp {{-- Ensure it is an array --}}
                        <tr class="hover:bg-blue-50/50 transition">
                            <td class="px-4 py-3 font-medium">
                                <span class="text-gray-400 mr-2">#{{ $index + 1 }}</span>
                                {{ $item->name }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold">
                                    {{ number_format($item->total_qty) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-700">
                                {{ number_format($item->total_revenue, 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-400 italic">No sales today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Low Stock Alerts --}}
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <h3 class="font-bold text-red-800 flex items-center gap-2 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Low Stock Alerts
            </h3>
            <div class="space-y-2">
                @forelse($lowStockProducts as $product)
                    <div class="flex justify-between items-center bg-white p-3 rounded-lg border border-red-100 shadow-sm">
                        <span class="text-sm font-semibold text-gray-700">
                            {{ $product['name'] }}
                        </span>
                        <span class="text-xs font-bold bg-red-600 text-white px-2 py-1 rounded">
                            {{ $product['stock'] }} left
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-green-600 italic">All stock levels healthy.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 3. Activity Feed (Full Width) --}}
    <div class="mt-6 rounded-xl border border-neutral-200 bg-white shadow-sm overflow-hidden">
        <div class="p-4 font-semibold border-b bg-gray-50 flex justify-between items-center">
            <span>Full Sales History</span>
            <span class="text-xs font-normal text-gray-500">Showing 20 per page</span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-neutral-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Time</th>
                    <th class="px-4 py-3 text-left font-semibold">Sale #</th>
                    <th class="px-4 py-3 text-center font-semibold">Cashier</th>
                    <th class="px-4 py-3 text-center font-semibold">Amount</th>
                    <th class="px-4 py-3 text-center font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse ($recentSales as $sale)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-500">{{ $sale->created_at->format('H:i') }}</td>
                        <td class="px-4 py-3 font-medium">#{{ $sale->id }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">
                            <div class="flex items-center justify-center">
                                <div
                                    class="h-7 w-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-bold mr-2">
                                    {{ substr($sale->user->name, 0, 2) }}
                                </div>
                                {{ $sale->user->name }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center font-mono font-bold">{{ number_format($sale->total_amount, 0) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($sale->isRefunded())
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Refunded</span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Success</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-neutral-400 italic">No sales recorded today.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- Pagination Links --}}
    <div class="mt-4">
        {{ $recentSales->links() }}
    </div>

</x-layouts::app>