{{-- <x-layouts::app title="Products">

    @if (session('success'))
        <div class="mb-4 text-green-600">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Products</h1>

        <a href="{{ route('products.create') }}" class="px-4 py-2 bg-black text-white rounded">
            Add Product
        </a>
    </div>

    @if ($products->count())
        <table class="w-full border-collapse border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-3 py-2 text-left">Name</th>
                    <th class="border px-3 py-2" width="260px">Brand</th>
                    <th class="border px-3 py-2">Barcode</th>
                    <th class="border px-3 py-2">Unit</th>
                    <th class="border px-3 py-2">Cost</th>
                    <th class="border px-3 py-2">Selling</th>
                    <th class="border px-3 py-2">Status</th>
                    <th class="border px-3 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td class="border px-3 py-2">{{ $product->name }}</td>
                        <td class="border px-3 py-2">{{ $product->brand ?? '-' }}</td>
                        <td class="border px-3 py-2">{{ $product->barcode }}</td>
                        <td class="border px-3 py-2">{{ $product->unit_type }}</td>
                        <td class="border px-3 py-2">{{ number_format($product->cost_price, 2) }}</td>
                        <td class="border px-3 py-2">{{ number_format($product->selling_price, 2) }}</td>
                        <td class="border px-3 py-2">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </td>
                        <td class="border px-3 py-2">
                            <a href="{{ route('products.edit', $product) }}" class="text-blue-600">
                                Edit
                            </a>

                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this product?')"
                                    class="text-red-600 ml-2">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No products found.</p>
    @endif

</x-layouts::app> --}}

<x-layouts::app title="Products">

    @if (session('success'))
        <div class="mb-4 text-green-600">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Products</h1>

        <a href="{{ route('products.create') }}" class="px-4 py-2 bg-black text-white rounded">
            Add Product
        </a>
    </div>

    @if ($products->count())
        <table class="w-full border-collapse border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-3 py-2 text-left">Name</th>
                    <th class="border px-3 py-2" width="260px">Brand</th>
                    <th class="border px-3 py-2">Barcode</th>
                    <th class="border px-3 py-2">Unit</th>
                    <th class="border px-3 py-2">Cost</th>
                    <th class="border px-3 py-2">Selling</th>
                    <th class="border px-3 py-2">Stock</th>
                    <th class="border px-3 py-2">Status</th>
                    <th class="border px-3 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td class="border px-3 py-2">{{ $product->name }}</td>
                        <td class="border px-3 py-2">{{ $product->brand ?? '-' }}</td>
                        <td class="border px-3 py-2">{{ $product->barcode }}</td>
                        <td class="border px-3 py-2">{{ $product->unit_type }}</td>
                        <td class="border px-3 py-2">{{ number_format($product->cost_price, 2) }}</td>
                        <td class="border px-3 py-2">{{ number_format($product->selling_price, 2) }}</td>

                        {{-- Current Stock (derived, not stored) --}}
                        {{-- <td class="border px-3 py-2 font-semibold">
                            @if ($product->currentStock() <= 0)
                                <span class="text-red-600 text-xs italic">Out of stock</span>
                            @else
                                {{ $product->currentStock() }}
                            @endif
                        </td> --}}
                        <td class="border px-3 py-2 font-semibold">
                            @php
                                $stock = $product->currentStock();
                            @endphp

                            @if ($stock <= 0)
                                <span class="text-red-600 text-xs italic">Out of stock</span>
                            @elseif ($stock < 1)
                                <span class="text-orange-500">
                                    {{ $stock }} <span class="text-xs">(Low)</span>
                                </span>
                            @else
                                {{ $stock }}
                            @endif
                        </td>

                        <td class="border px-3 py-2">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </td>

                        <td class="border px-3 py-2">
                            <a href="{{ route('products.edit', $product) }}" class="text-blue-600">
                                Edit
                            </a>

                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this product?')"
                                    class="text-red-600 ml-2">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No products found.</p>
    @endif

</x-layouts::app>