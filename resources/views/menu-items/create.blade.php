<x-layouts::app title="Add Menu Item">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <a href="{{ route('menu-items.index') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center gap-2 font-medium">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Kitchen Menu
            </a>
            <h2 class="mt-4 text-3xl font-extrabold text-gray-900">Add New Menu Item</h2>
            <p class="mt-1 text-sm text-gray-500">Enter the details for the new kitchen item.</p>
        </div>

        <div class="bg-white shadow-xl border border-gray-100 rounded-2xl overflow-hidden">
            <form action="{{ route('menu-items.store') }}" method="POST" class="p-8 space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Item Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-3 px-4"
                            placeholder="e.g. Grilled Tilapia">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="category" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Category</label>
                            <input type="text" name="category" id="category" value="{{ old('category') }}"
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
                                <input type="number" name="price" id="price" value="{{ old('price') }}" step="0.01" required
                                    class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-3 pl-14 pr-4"
                                    placeholder="0.00">
                            </div>
                            @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t flex justify-end">
                    <button type="submit"
                        class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition-all transform active:scale-95">
                        Save Menu Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
