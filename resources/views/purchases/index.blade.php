{{--  <x-layouts::app title="Purchase History">

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

</x-layouts::app>--}}

<x-layouts::app title="Purchase History">
    <div class="min-h-screen bg-gray-50 pb-12">
        
        <div class="bg-white border-b border-gray-200 mb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Inventory Purchases</h1>
                        <p class="mt-1 text-sm text-gray-500">Track and manage incoming stock and supplier invoices.</p>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4 gap-3">
                        <a href="{{ route('purchases.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            New Stock In
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Search & Filter Bar --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
                <form method="GET" action="{{ route('purchases.index') }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 ml-1">From Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" 
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 px-4 py-3 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 ml-1">To Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" 
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 px-4 py-3 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-black text-white text-sm font-medium rounded-lg transition">
                        Filter Results
                    </button>
                    @if(request()->anyFilled(['start_date', 'end_date']))
                        <a href="{{ route('purchases.index') }}" class="text-sm text-gray-500 hover:text-red-600 mb-2 underline">Clear Filters</a>
                    @endif
                </form>
            </div>

            @if (session('success'))
                <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 flex items-center text-green-700">
                    <svg class="h-5 w-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Inventory Table --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date & Ref</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Recorded By</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Investment (Cost)</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($purchases as $purchase)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $purchase->purchased_at->format('M d, Y') }}</div>
                                    <div class="text-xs font-mono text-indigo-600">{{ $purchase->reference_no ?? 'No Reference' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-gray-100 rounded flex items-center justify-center">
                                            <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276l-3.996-2.25a1 1 0 00-.947 0l-3.996 2.25c-.153.086-.273.21-.35.359L5.236 7.737l3.996 2.25a1 1 0 00.947 0l3.996-2.25c.153-.086.273-.21.35-.359l.686-1.102zM9 17a1 1 0 01-1.447.894l-4-2A1 1 0 013 15V9.236a1 1 0 011.447-.894l4 2a1 1 0 01.553.894V17z"/></svg>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $purchase->supplier_name ?? 'General Supplier' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $purchase->user->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-bold text-gray-900">UGX {{ number_format($purchase->total_cost) }}</div>
                                    <div class="text-xs text-gray-400">{{ $purchase->items->count() }} items received</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="inline-flex items-center gap-2">

                                        <a href="{{ route('purchases.show', $purchase) }}"
                                            class="inline-flex items-center px-3 py-1.5 border border-indigo-100 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                                            Details
                                        </a>

                                        <a href="{{ route('purchases.edit', $purchase) }}"
                                            class="inline-flex items-center px-3 py-1.5 border border-amber-100 text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">
                                            Edit
                                        </a>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <p class="text-gray-500">No purchase records found matching your criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($purchases instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $purchases->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
