<x-layouts::app title="Room Folio - {{ $room->name }}">
    <div class="p-6 max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Room {{ $room->name }} Folio</h1>
                <p class="text-gray-500">Manage outstanding balances and settlements</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500 uppercase font-bold tracking-wider">Total Outstanding</div>
                <div class="text-4xl font-black text-red-600">{{ number_format($balance, 2) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="{ settlementAmount: {{ $balance }}, targetSaleId: '', targetSaleLabel: 'Total Balance' }">
            
            <!-- LEFT: UNPAID SALES -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                        <h2 class="font-bold text-gray-700 uppercase text-sm tracking-widest">Unpaid Orders</h2>
                        <div class="flex gap-2">
                            <button @click="settlementAmount = {{ $balance }}; targetSaleId = ''; targetSaleLabel = 'Total Balance'" 
                                class="text-xs bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded-full font-bold transition-colors">
                                Reset to Total
                            </button>
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">{{ $room->sales->count() }} Orders</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs font-bold text-gray-400 uppercase border-b">
                                    <th class="px-6 py-4">Date/ID</th>
                                    <th class="px-6 py-4">Items</th>
                                    <th class="px-6 py-4 text-right">Total</th>
                                    <th class="px-6 py-4 text-right">Balance</th>
                                    <th class="px-6 py-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($room->sales as $sale)
                                    @php $saleBal = $sale->total_amount - $sale->paid_amount; @endphp
                                    <tr class="hover:bg-gray-50 transition-colors" :class="targetSaleId == '{{ $sale->id }}' ? 'bg-blue-50' : ''">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900">#{{ $sale->id }}</div>
                                            <div class="text-xs text-gray-500">{{ $sale->created_at->format('d M, H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-600">
                                                {{ $sale->items->take(2)->map(fn($i) => $i->name)->join(', ') }}
                                                @if($sale->items->count() > 2)
                                                    <span class="text-gray-400">... +{{ $sale->items->count() - 2 }} more</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right font-medium text-gray-400">{{ number_format($sale->total_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-right text-red-600 font-bold">{{ number_format($saleBal, 2) }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <button @click="settlementAmount = {{ $saleBal }}; targetSaleId = '{{ $sale->id }}'; targetSaleLabel = 'Order #{{ $sale->id }}'" 
                                                class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black px-3 py-1.5 rounded uppercase tracking-tighter shadow-sm transition-transform active:scale-90">
                                                SETTLE
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                            No outstanding orders for this room.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PAYMENT HISTORY -->
                <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gray-50">
                        <h2 class="font-bold text-gray-700 uppercase text-sm tracking-widest">Recent Payments</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs font-bold text-gray-400 uppercase border-b">
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Method</th>
                                    <th class="px-6 py-4">Remarks</th>
                                    <th class="px-6 py-4 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($payments as $payment)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->created_at->format('d M, H:i') }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-700">
                                                {{ $payment->method }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 italic">{{ $payment->remarks ?? '-' }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-green-600">{{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">No payment records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- RIGHT: SETTLEMENT FORM -->
            <div class="lg:col-span-1">
                <div class="bg-gray-900 rounded-2xl p-6 text-white shadow-xl sticky top-6">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <flux:icon.banknotes variant="outline" class="w-6 h-6 text-green-400" />
                        Record Settlement
                    </h2>

                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-900/50 text-red-200 rounded-lg text-sm border border-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-4 p-3 bg-green-900/50 text-green-200 rounded-lg text-sm border border-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('rooms.settle', $room) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        {{-- Hidden field for specific sale --}}
                        <input type="hidden" name="sale_id" :value="targetSaleId">

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-xs font-bold text-gray-400 uppercase">Settlement for</label>
                                <span class="text-[10px] font-black bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded" x-text="targetSaleLabel"></span>
                            </div>
                            <input type="number" name="amount" step="0.01" x-model="settlementAmount" 
                                class="w-full bg-gray-800 border-gray-700 rounded-xl px-4 py-3 text-2xl font-black text-white focus:ring-green-500 focus:border-green-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Payment Method</label>
                            <select name="method" class="w-full bg-gray-800 border-gray-700 rounded-xl px-4 py-3 text-white">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="card">Credit Card</option>
                                <option value="mobile_money">Mobile Money</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Remarks (Optional)</label>
                            <textarea name="remarks" placeholder="Full payment at checkout..." 
                                class="w-full bg-gray-800 border-gray-700 rounded-xl px-4 py-3 text-white text-sm h-20"></textarea>
                        </div>

                        <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-500 text-white font-black py-4 rounded-xl shadow-lg transition-all transform active:scale-95 uppercase tracking-widest mt-4">
                            PROCESS PAYMENT
                        </button>
                    </form>

                    <div class="mt-8 pt-8 border-t border-gray-800 text-center">
                        <p class="text-gray-500 text-xs">
                            Processing a payment will automatically clear outstanding orders starting from the oldest first.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layouts::app>
