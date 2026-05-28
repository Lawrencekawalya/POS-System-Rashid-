<x-layouts::app title="Unpaid Room Charges">
    <div class="min-h-screen bg-gray-50 pb-12">
        
        <div class="bg-white border-b border-gray-200 mb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            Unpaid Room Charges
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Monitor and manage outstanding guest balances from room service and bar orders.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Quick Stats Area --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 p-5">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Outstanding Orders</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $sales->total() }}</dd>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 p-5">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Uncollected Debt</dt>
                    <dd class="mt-1 text-3xl font-semibold text-red-600">
                        {{ number_format($sales->sum(fn($s) => $s->total_amount - $s->paid_amount)) }}
                    </dd>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 p-5 text-center flex items-center justify-center">
                   <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Awaiting Settlement</span>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                @if ($sales->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Order #</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Room</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Balance Due</th>
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
                                            <div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-bold bg-blue-50 text-blue-700">
                                                Room {{ $sale->room?->name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 font-medium">{{ $sale->created_at->format('M d, Y') }}</div>
                                            <div class="text-xs text-gray-400">{{ $sale->created_at->format('H:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-400">
                                            {{ number_format($sale->total_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-red-600">
                                            {{ number_format($sale->total_amount - $sale->paid_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($sale->payment_status === 'unpaid')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-red-100 text-red-700 border border-red-200">
                                                    Unpaid
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-orange-100 text-orange-700 border border-orange-200">
                                                    Partial
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                                            @if($sale->room_id)
                                                <a href="{{ route('rooms.folio', $sale->room_id) }}" class="inline-flex items-center px-3 py-1 bg-gray-900 text-white shadow-sm text-xs font-bold rounded-lg hover:bg-black transition">
                                                    Go to Folio
                                                </a>
                                            @endif
                                            <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                                                Order Details
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
                        <h3 class="mt-2 text-sm font-medium text-gray-900">All room charges are settled!</h3>
                        <p class="mt-1 text-sm text-gray-500">There are currently no outstanding room service balances.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
