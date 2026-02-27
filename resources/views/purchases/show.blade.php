{{--<x-layouts::app title="Purchase #{{ $purchase->id }}">

    <div class="flex justify-between mb-4">
        <h1 class="text-xl font-semibold">
            Purchase #{{ $purchase->id }}
        </h1>

        <a href="{{ route('purchases.index') }}" class="text-sm text-gray-600">← Back</a>
    </div>

    <div class="mb-4 text-sm">
        <p><strong>Date:</strong> {{ $purchase->purchased_at }}</p>
        <p><strong>Supplier:</strong> {{ $purchase->supplier_name ?? '-' }}</p>
        <p><strong>Reference:</strong> {{ $purchase->reference_no ?? '-' }}</p>
        <p><strong>Recorded by:</strong> {{ $purchase->user->name }}</p>
    </div>

    <table class="w-full border text-sm mb-4">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">Product</th>
                <th class="border px-2 py-1">Qty</th>
                <th class="border px-2 py-1">Unit Cost</th>
                <th class="border px-2 py-1">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $item)
                <tr>
                    <td class="border px-2 py-1">
                        {{ $item->product->name }}
                    </td>
                    <td class="border px-2 py-1 text-center">
                        {{ $item->quantity }}
                    </td>
                    <td class="border px-2 py-1">
                        {{ number_format($item->unit_cost, 2) }}
                    </td>
                    <td class="border px-2 py-1">
                        {{ number_format($item->subtotal, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="font-semibold">
        Total Cost: {{ number_format($purchase->total_cost, 2) }}
    </div>

</x-layouts::app>--}}
<x-layouts::app title="Purchase #{{ $purchase->id }}">
    <div class="min-h-screen bg-gray-50 pb-12">
        
        <div class="bg-white border-b border-gray-200 mb-8 print:hidden">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <a href="{{ route('purchases.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back to History
                    </a>
                    <div class="flex gap-3">
                        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print Voucher
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm border border-gray-200 rounded-2xl overflow-hidden">
                
                <div class="p-8 border-b border-gray-100 bg-gray-50/30">
                    <div class="flex flex-col md:flex-row justify-between gap-6">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 uppercase tracking-wider mb-2">
                                Purchase Voucher
                            </span>
                            <h1 class="text-3xl font-black text-gray-900 tracking-tight">
                                #{{ str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }}
                            </h1>
                            <p class="text-gray-500 text-sm mt-1">Ref: <span class="font-mono font-medium text-gray-700">{{ $purchase->reference_no ?? 'N/A' }}</span></p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-8 text-right">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">Date Recorded</p>
                                <p class="text-sm font-bold text-gray-900">{{ $purchase->purchased_at->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $purchase->purchased_at->format('H:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">Supplier</p>
                                <p class="text-sm font-bold text-gray-900">{{ $purchase->supplier_name ?? 'General Supplier' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="py-4 text-xs font-bold text-gray-500 uppercase">Item Description</th>
                                <th class="py-4 text-center text-xs font-bold text-gray-500 uppercase">Qty</th>
                                <th class="py-4 text-right text-xs font-bold text-gray-500 uppercase">Unit Cost</th>
                                <th class="py-4 text-right text-xs font-bold text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($purchase->items as $item)
                                <tr>
                                    <td class="py-5">
                                        <div class="text-sm font-bold text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-400">ID: {{ $item->product->id }}</div>
                                    </td>
                                    <td class="py-5 text-center text-sm text-gray-700 font-medium">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="py-5 text-right text-sm text-gray-700 font-mono">
                                        {{ number_format($item->unit_cost) }}
                                    </td>
                                    <td class="py-5 text-right text-sm font-bold text-gray-900 font-mono">
                                        {{ number_format($item->subtotal) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="bg-gray-50 px-8 py-8 border-t border-gray-100">
                    <div class="flex flex-col items-end">
                        <div class="w-full md:w-64 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="text-gray-900 font-medium">UGX {{ number_format($purchase->total_cost) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Tax (0%)</span>
                                <span class="text-gray-900 font-medium">0.00</span>
                            </div>
                            <div class="pt-3 border-t border-gray-200 flex justify-between items-center">
                                <span class="text-base font-bold text-gray-900">Total Investment</span>
                                <span class="text-2xl font-black text-indigo-600">
                                    <span class="text-sm font-normal uppercase">UGX</span> {{ number_format($purchase->total_cost) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-4 bg-white border-t border-gray-50 flex justify-between items-center">
                    <div class="flex items-center text-xs text-gray-400 italic">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Recorded by {{ $purchase->user->name }}
                    </div>
                    <div class="text-[10px] text-gray-300 uppercase tracking-widest font-bold">
                        Generated via System POS
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
