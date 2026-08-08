<x-layouts::app title="Edit Menu Item">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <a href="{{ route('menu-items.index') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center gap-2 font-medium">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Kitchen Menu
            </a>
            <h2 class="mt-4 text-3xl font-extrabold text-gray-900">Edit Menu Item</h2>
            <p class="mt-1 text-sm text-gray-500">Update details for "{{ $menuItem->name }}".</p>
        </div>

        <div class="bg-white shadow-xl border border-gray-100 rounded-2xl overflow-hidden">
            <form action="{{ route('menu-items.update', $menuItem) }}" method="POST" class="p-8 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Item Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $menuItem->name) }}" required
                            class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-3 px-4"
                            placeholder="e.g. Grilled Tilapia">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="category" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Category</label>
                            <input type="text" name="category" id="category" value="{{ old('category', $menuItem->category) }}"
                                class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-3 px-4"
                                placeholder="e.g. Main Course">
                            @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Selling Price</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">UGX</span>
                                </div>
                                <input type="number" name="price" id="price" value="{{ old('price', $menuItem->price) }}" step="0.01" required
                                    class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-3 pl-14 pr-4"
                                    placeholder="0.00">
                            </div>
                            @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div x-data="{ tracksStock: @js((bool) old('stock_product_id', $menuItem->stock_product_id)) }" class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                        <h3 class="font-bold text-amber-900">Fresh-cut stock movement</h3>
                        <p class="mt-1 text-sm text-amber-800">Only link menu items that should deduct portions from stock.</p>

                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="stock_product_id" class="block text-sm font-bold text-gray-700">Fresh-cut product</label>
                                <select name="stock_product_id" id="stock_product_id" x-on:change="tracksStock = $event.target.value !== ''"
                                    class="mt-2 w-full rounded-xl border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Does not affect stock</option>
                                    @foreach ($portionProducts as $product)
                                        <option value="{{ $product->id }}" @selected(old('stock_product_id', $menuItem->stock_product_id) == $product->id)>
                                            {{ $product->name }} ({{ $product->currentStock() }} portions available)
                                        </option>
                                    @endforeach
                                </select>
                                @if ($portionProducts->isEmpty())
                                    <p class="mt-2 text-xs text-amber-800">No fresh-cut portion products are available. <a href="{{ route('products.create') }}" class="font-bold underline" wire:navigate>Create one first.</a></p>
                                @endif
                                @error('stock_product_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="stock_quantity" class="block text-sm font-bold text-gray-700">Portions used per menu item</label>
                                <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', $menuItem->stock_quantity ?? 1) }}" min="1" step="1"
                                    x-bind:disabled="!tracksStock"
                                    x-bind:class="!tracksStock && 'cursor-not-allowed bg-gray-100 text-gray-400'"
                                    class="mt-2 w-full rounded-xl border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('stock_quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t flex justify-end">
                    <button type="submit"
                        class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition-all transform active:scale-95">
                        Update Menu Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
