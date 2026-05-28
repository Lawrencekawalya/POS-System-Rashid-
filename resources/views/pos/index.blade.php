<x-layouts::app title="POS Checkout">

    @php
        $mode = request('mode', 'pos');
    @endphp

    <!-- MODE SWITCH -->
    <div class="flex gap-2 mb-4">
        <a href="?mode=pos" class="px-4 py-2 rounded {{ $mode == 'pos' ? 'bg-black text-white' : 'bg-gray-200' }}">
            POS
        </a>
        <a href="?mode=room" class="px-4 py-2 rounded {{ $mode == 'room' ? 'bg-black text-white' : 'bg-gray-200' }}">
            Room Service
        </a>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        <!-- LEFT: STOCK -->
        <div class="lg:w-1/4">
            <h2 class="text-lg font-bold mb-3">Current Stock</h2>

            <div class="border rounded-lg bg-white shadow-sm overflow-hidden">
                <div class="max-h-[88vh] overflow-y-auto px-2 py-2">
                    @foreach($products as $product)
                        <div class="flex justify-between items-center p-2 border-b hover:bg-gray-50">
                            <div>
                                <div class="font-medium text-sm">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $product->barcode }}</div>
                            </div>
                            <span
                                class="px-2 py-1 rounded text-xs font-bold
                                            {{ $product->currentStock() <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                {{ $product->currentStock() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- RIGHT: POS -->
        <div class="lg:w-3/4">

            <h1 class="text-xl font-semibold mb-4">
                {{ $mode == 'room' ? 'Room Service' : 'POS Checkout' }}
            </h1>

            <!-- ERRORS -->
            @if ($errors->any())
                <div class="mb-3 p-3 bg-red-50 text-red-600 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- SUCCESS -->
            @if (session('success'))
                <div class="mb-3 p-3 bg-green-50 text-green-600 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- ROOM SELECTOR -->
            @if($mode == 'room')
                <div class="flex gap-2 mb-4">
                    <form method="GET" class="flex-grow">
                        <input type="hidden" name="mode" value="room">
                        <select name="room_id" onchange="this.form.submit()" class="border-2 px-4 py-3 w-full rounded-lg bg-white">
                            <option value="">Select Room</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                    Room {{ $room->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    
                    @if(request('room_id'))
                        <a href="{{ route('rooms.folio', request('room_id')) }}" 
                            class="bg-blue-100 text-blue-700 px-6 py-3 rounded-lg font-bold flex items-center gap-2 hover:bg-blue-200 transition-colors">
                            <flux:icon.document-text variant="micro" />
                            VIEW FOLIO
                        </a>
                    @endif
                </div>
            @endif

            <!-- ADD ITEM -->
            <div x-data="{ showCustom: false }">
                <form method="POST" action="{{ route('pos.add') }}" class="mb-4">
                    @csrf

                    <input type="hidden" name="mode" value="{{ $mode }}">
                    <input type="hidden" name="room_id" value="{{ request('room_id') }}">

                    <div class="flex flex-col md:flex-row gap-2">
                        <div class="relative flex-grow">
                            <input list="product-list" name="barcode" autofocus placeholder="Search product or scan barcode..."
                                class="border-2 px-4 py-3 w-full rounded-lg text-lg">

                            <datalist id="product-list">
                                @foreach($products as $product)
                                    <option value="{{ $product->barcode }}">
                                        {{ $product->name }} (Product)
                                    </option>
                                @endforeach

                                @if($mode === 'room')
                                    @foreach($menuItems as $mItem)
                                        <option value="{{ $mItem->name }}">
                                            {{ $mItem->name }} (Menu)
                                        </option>
                                    @endforeach
                                @endif
                            </datalist>

                        </div>

                        @if($mode == 'room')
                            <button type="button" @click="showCustom = !showCustom"
                                class="bg-gray-800 text-white px-6 py-3 rounded-lg font-bold hover:bg-black transition">
                                OTHER
                            </button>
                        @endif
                    </div>

                    <!-- CUSTOM ITEM PANEL (Alpine.js) -->
                    @if($mode == 'room')
                        <div x-show="showCustom" x-transition class="mt-4 p-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                            <h3 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Custom Order / Not on Menu</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">ITEM NAME</label>
                                    <input type="text" name="custom_name" placeholder="e.g. Special Grilled Fish"
                                        class="w-full border rounded-lg px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">PRICE</label>
                                    <input type="number" name="custom_price" step="0.01" placeholder="0.00"
                                        class="w-full border rounded-lg px-3 py-2">
                                </div>
                                <div class="flex items-end gap-2">
                                    <div class="flex items-center h-10 px-3 border rounded-lg bg-white">
                                        <input type="checkbox" name="save_to_menu" value="1" id="save_menu" class="mr-2">
                                        <label for="save_menu" class="text-xs font-medium text-gray-600 cursor-pointer">Add to Menu?</label>
                                    </div>
                                    <button type="submit" class="bg-blue-600 text-white px-4 h-10 rounded-lg font-bold hover:bg-blue-700 flex-grow">
                                        ADD
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </form>
            </div>

            <!-- CART TABLE -->
            <div class="border rounded-lg bg-white overflow-hidden mb-4 shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-4 py-3 font-semibold text-gray-700">Item</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 text-center">Qty</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 text-right">Price</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 text-right">Subtotal</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        @forelse ($cart as $key => $item)
                            <tr class="hover:bg-gray-50 transition-colors border-b last:border-0">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg {{ $item['item_type'] === 'product' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                            @if($item['item_type'] === 'product')
                                                <flux:icon.cube variant="micro" />
                                            @else
                                                <flux:icon.book-open variant="micro" />
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900">
                                                {{ $item['name'] }}
                                            </div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded {{ $item['item_type'] === 'product' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                                                    {{ $item['item_type'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <form method="POST" action="{{ route('pos.updateQuantity', $key) }}">
                                            @csrf
                                            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1"
                                                class="w-16 h-8 text-center font-bold border-gray-300 rounded"
                                                onblur="this.form.submit()">
                                        </form>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($item['unit_price'], 2) }}
                                </td>

                                <td class="px-4 py-3 text-right font-bold text-gray-900">
                                    {{ number_format($item['subtotal'], 2) }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <form method="POST" action="{{ route('pos.remove', $key) }}">
                                        @csrf
                                        <button class="text-red-500 hover:text-red-700 text-sm font-medium">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-gray-400">
                                    Scan items to build the cart.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            <!-- TOTAL + ACTION -->
            <div class="flex flex-col md:flex-row gap-4 items-start mb-10">

                <div class="text-3xl font-bold">
                    Total:
                    <span class="text-blue-600">
                        {{ number_format($total, 2) }}
                    </span>
                </div>

                <div class="w-full md:w-80">
                    <form method="POST" action="{{ route('pos.checkout') }}" x-data="{ method: 'cash' }">
                        @csrf

                        <div class="mb-3">
                            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Payment Method</label>
                            <select name="payment_method" x-model="method" class="border-2 px-4 py-3 w-full rounded-lg bg-white">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank/Transfer</option>
                                <option value="card">Credit Card</option>
                                @if($mode == 'room' && request('room_id'))
                                    <option value="room">Charge to Room</option>
                                @endif
                            </select>
                        </div>

                        <div x-show="method !== 'room'" x-transition>
                            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Amount Received</label>
                            <input type="number" name="paid_amount" step="0.01" value="0"
                                class="border-2 px-4 py-3 w-full rounded-lg mb-4">
                        </div>
                        
                        {{-- Dummy input for validation if method is room --}}
                        <template x-if="method === 'room'">
                            <input type="hidden" name="paid_amount" value="0">
                        </template>

                        @if($mode == 'room')
                            <input type="hidden" name="room_id" value="{{ request('room_id') }}">
                        @endif

                        <button class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-4 w-full rounded-lg uppercase shadow-lg transition-transform active:scale-95">
                            Complete Sale {{ $mode == 'room' && request('room_id') ? '(Room '.(\App\Models\Room::find(request('room_id'))?->name).')' : '' }}
                        </button>
                    </form>
                </div>
            </div>

            <hr class="my-10 border-gray-300">
            <h2 class="text-lg font-semibold mb-4">Sales Performance</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="p-4 bg-white border rounded-lg shadow-sm">
                    <div class="text-gray-500 text-xs uppercase tracking-wider font-bold">Today's Sales</div>
                    <div class="text-2xl font-bold">{{ number_format($totalSales, 2) }}</div>
                </div>

                <div class="p-4 bg-white border rounded-lg shadow-sm">
                    <div class="text-gray-500 text-xs uppercase tracking-wider font-bold">Refunds</div>
                    <div class="text-2xl font-bold text-red-600">{{ number_format($totalRefunds, 2) }}</div>
                </div>

                <div class="p-4 bg-white border rounded-lg shadow-sm">
                    <div class="text-gray-500 text-xs uppercase tracking-wider font-bold">Today's Net</div>
                    <div class="text-2xl font-bold text-green-600">{{ number_format($netTotal, 2) }}</div>
                </div>

                <div class="p-4 bg-black text-white border rounded-lg shadow-sm">
                    <div class="text-gray-400 text-xs uppercase tracking-wider font-bold">Cumulative Revenue</div>
                    <div class="text-2xl font-bold text-blue-400">{{ number_format($cumulativeRevenue, 2) }}</div>
                </div>
            </div>
            <hr class="my-10 border-gray-300">
            <h2 class="text-lg font-semibold mb-4">Today's Sales</h2>
            @if ($salesToday->count())
                    <div class="overflow-x-auto border rounded-lg shadow-sm bg-white">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="px-4 py-3 font-semibold text-gray-700">Receipt</th>
                                    <th class="px-4 py-3 font-semibold text-gray-700">Time</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Amount</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Status</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($salesToday as $sale)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 font-medium text-gray-900">#{{ $sale->id }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $sale->created_at->format('H:i') }}</td>
                                        <td class="px-4 py-3 text-right font-mono">{{ number_format($sale->total_amount, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($sale->isRefunded())
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Refunded
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Sold
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('sales.show', $sale) }}"
                                                class="text-blue-600 hover:text-blue-900 font-semibold">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                <tr>
                                    <td colspan="2" class="px-4 py-3 text-right font-bold text-gray-700">Total Today:</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 font-mono text-base">
                                        {{ number_format($salesToday->sum('total_amount'), 2) }}
                                    </td>
                                    <td colspan="2" class="bg-gray-50"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center border-2 border-dashed rounded-lg bg-gray-50">
                        <p class="text-gray-500">No sales recorded today.</p>
                    </div>
                @endif
        </div>
    </div>
</x-layouts::app>
