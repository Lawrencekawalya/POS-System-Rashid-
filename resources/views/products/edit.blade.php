{{--<x-layouts::app title="Edit Product">

    <h1 class="text-xl font-semibold mb-6">
        Edit Product
    </h1>

    {{-- Validation Errors 
    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-950/40 p-4 text-red-700 dark:text-red-300">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('products.update', $product) }}" class="max-w-xl space-y-4">

        @csrf
        @method('PUT')

        <div>
            <label class="block mb-1 font-medium">Name</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                class="w-full rounded border px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Brand</label>
            <input type="text" name="brand" value="{{ old('brand', $product->brand) }}"
                class="w-full rounded border px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Barcode</label>
            <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" required
                class="w-full rounded border px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Unit Type</label>
            <select name="unit_type" required class="w-full rounded border px-3 py-2">
                <option value="pcs" {{ old('unit_type', $product->unit_type) === 'pcs' ? 'selected' : '' }}>Pieces
                </option>
                <option value="bottles" {{ old('unit_type', $product->unit_type) === 'bottles' ? 'selected' : '' }}>
                    Bottles</option>
                <option value="cartons" {{ old('unit_type', $product->unit_type) === 'cartons' ? 'selected' : '' }}>
                    Cartons</option>
                <option value="portion" {{ old('unit_type', $product->unit_type) === 'portion' ? 'selected' : '' }}>
                    Fresh-cut portions</option>
            </select>
        </div>

        <div>
            <label class="block mb-1 font-medium">Cost Price</label>
            <input type="number" name="cost_price" step="0.01" min="0.01"
                value="{{ old('cost_price', $product->cost_price) }}" required class="w-full rounded border px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Selling Price</label>
            <input type="number" name="selling_price" step="0.01" min="0.01"
                value="{{ old('selling_price', $product->selling_price) }}" required
                class="w-full rounded border px-3 py-2">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1"
                {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
            <label class="font-medium">Active</label>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="rounded bg-black dark:bg-white px-4 py-2 text-white dark:text-zinc-900">
                Update Product
            </button>

            <a href="{{ route('products.index') }}" class="rounded border px-4 py-2">
                Cancel
            </a>
        </div>
    </form>

</x-layouts::app>--}}

<x-layouts::app title="Edit Product">
    <div class="min-h-screen overflow-hidden bg-gray-50 dark:bg-zinc-800">
        <div class="bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-700 mb-4">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-zinc-400">
                        <li><a href="/" class="hover:text-indigo-600 dark:hover:text-indigo-400">Dashboard</a></li>
                        <li><svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg></li>
                        <li><a href="{{ route('products.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Products</a></li>
                        <li><svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg></li>
                        <li class="text-gray-900 dark:text-zinc-100 font-medium">Edit Product</li>
                    </ol>
                </nav>
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100">Edit: {{ $product->name }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300' : 'bg-gray-100 dark:bg-zinc-800 text-gray-800 dark:text-zinc-100' }}">
                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('products.update', $product) }}" class="max-w-5xl mx-auto overflow-hidden px-4 sm:px-6 lg:mb-8" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Error Summary --}}
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 flex">
                    <svg class="h-5 w-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Please correct the following errors:</h3>
                        <ul class="mt-1 text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-0">

                <div class="space-y-8">
                    <div class="bg-white dark:bg-zinc-900 shadow-sm border border-gray-200 dark:border-zinc-700 rounded-xl p-6">
                        <h3 class="text-base font-bold text-gray-900 dark:text-zinc-100 mb-5">Product Details</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-200">Name</label>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-3 text-base focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-200">Brand</label>
                                <input type="text" name="brand" value="{{ old('brand', $product->brand) }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-3 text-base focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-200">Unit Type</label>
                                <select name="unit_type" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-3 text-base focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="pcs" {{ old('unit_type', $product->unit_type) == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                    <option value="bottles" {{ old('unit_type', $product->unit_type) == 'bottles' ? 'selected' : '' }}>Bottles</option>
                                    <option value="cartons" {{ old('unit_type', $product->unit_type) == 'cartons' ? 'selected' : '' }}>Cartons</option>
                                    <option value="portion" {{ old('unit_type', $product->unit_type) == 'portion' ? 'selected' : '' }}>Fresh-cut portions</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 shadow-sm border border-gray-200 dark:border-zinc-700 rounded-xl p-6">
                        <h3 class="text-base font-bold text-gray-900 dark:text-zinc-100 mb-5">Status & Visibility</h3>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-zinc-100">Visible in Store</p>
                                <p class="text-xs text-gray-500 dark:text-zinc-400">Toggle whether this product is available for customers.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 dark:bg-zinc-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                        <div class="mt-5 pt-5 border-t border-gray-100 dark:border-zinc-700">
                            <label for="menu_section" class="block text-sm font-semibold text-gray-800 dark:text-zinc-100">Guest menu section</label>
                            <p class="text-xs text-gray-500 dark:text-zinc-400">List this drink on the public menu under a bar heading.</p>
                            <select name="menu_section" id="menu_section"
                                class="mt-2 block w-full rounded-lg border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 shadow-sm px-4 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Not on the guest menu</option>
                                @foreach ($barSections as $section)
                                    <option value="{{ $section }}" @selected(old('menu_section', $product->menu_section) === $section)>{{ $section }}</option>
                                @endforeach
                            </select>
                            @error('menu_section') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                            <div class="mt-4">
                                <x-menu-photo-field :current="$product->image_url" :stand-in="$product->image_is_stock ? $product->image_credit : null" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="bg-white dark:bg-zinc-900 shadow-sm border border-gray-200 dark:border-zinc-700 rounded-xl p-6">
                        <h3 class="text-base font-bold text-gray-900 dark:text-zinc-100 mb-5">Pricing & Barcode</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-200">Barcode</label>
                                <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-3 text-base focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-200">Cost Price</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 sm:text-sm">UGX</span>
                                    </div>
                                    <input type="number" name="cost_price" step="0.01" value="{{ old('cost_price', $product->cost_price) }}" required
                                        class="block w-full pl-12 rounded-lg border-gray-300 dark:border-zinc-600 px-4 py-3 text-base focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-200">Selling Price</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 sm:text-sm">UGX</span>
                                    </div>
                                    <input type="number" name="selling_price" step="0.01" value="{{ old('selling_price', $product->selling_price) }}" required
                                        class="block w-full pl-12 rounded-lg border-gray-300 dark:border-zinc-600 px-4 py-3 text-base focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900 rounded-xl p-6">
                        <div class="flex">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-bold text-indigo-900 dark:text-indigo-300">Information</h3>
                                <p class="text-xs text-indigo-700 dark:text-indigo-300 mt-1">Updates to pricing will be reflected in all new sales. Past sales records remain unchanged.</p>
                            </div>
                        </div>
                    </div>

                    {{--<div class="flex items-center justify-between bg-white dark:bg-zinc-900 shadow-sm border border-gray-200 dark:border-zinc-700 rounded-xl p-4">
                        <a href="{{ route('products.index') }}" class="text-sm font-medium text-gray-600 dark:text-zinc-300 hover:text-gray-900 dark:hover:text-zinc-100">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all focus:ring-4 focus:ring-indigo-200 dark:focus:ring-indigo-800">
                            Update Product
                        </button>
                    </div>--}}
                    <div class="flex gap-30">
                        <a href="{{ route('products.index') }}" class="rounded border px-4 py-2">
                            Cancel
                        </a>

                        <button type="submit"
                            class="px-8 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all focus:ring-4 focus:ring-indigo-200 dark:focus:ring-indigo-800">
                            Update Product
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts::app>
