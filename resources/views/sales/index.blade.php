{{--<x-layouts::app title="Sales History">

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

</x-layouts::app>--}}

<x-layouts::app title="Sales History">
    <div class="min-h-screen bg-gray-50 pb-12">
        
        <div class="bg-white border-b border-gray-200 mb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            Sales History
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            View and manage all transaction records and customer receipts.
                        </p>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4">
                        <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Quick Stats Area --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 p-5">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Sales Count</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $sales->total() }}</dd>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 p-5">
                    <dt class="text-sm font-medium text-gray-500 truncate">Gross Revenue</dt>
                    <dd class="mt-1 text-3xl font-semibold text-indigo-600">UGX {{ number_format($sales->sum('total_amount')) }}</dd>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 p-5">
                    <dt class="text-sm font-medium text-gray-500 truncate">Refunded Items</dt>
                    <dd class="mt-1 text-3xl font-semibold text-red-600">--</dd> 
                </div>
            </div>

            {{-- Table Card --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                @if ($sales->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Receipt #</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cashier</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach ($sales as $sale)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-indigo-600">
                                            #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 font-medium">{{ $sale->created_at->format('M d, Y') }}</div>
                                            <div class="text-xs text-gray-400">{{ $sale->created_at->format('H:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <div class="flex items-center">
                                                <div class="h-7 w-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-bold mr-2">
                                                    {{ substr($sale->user->name, 0, 2) }}
                                                </div>
                                                {{ $sale->user->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                            UGX {{ number_format($sale->total_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($sale->isRefunded())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <span class="h-2 w-2 mr-1.5 rounded-full bg-red-400"></span>
                                                    Refunded
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <span class="h-2 w-2 mr-1.5 rounded-full bg-green-400"></span>
                                                    Completed
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 transition">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Footer --}}
                    <div class="bg-white px-4 py-4 border-t border-gray-200 sm:px-6">
                        {{ $sales->links() }}
                    </div>

                @else
                    <div class="text-center py-20">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No sales records</h3>
                        <p class="mt-1 text-sm text-gray-500">New transactions will appear here once they are completed.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
