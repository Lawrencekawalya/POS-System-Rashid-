<x-layouts::app title="Current Stock Levels">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Current Stock Levels</h2>
                <p class="mt-1 text-sm text-gray-500">Live stock quantities for all active products.</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800" wire:navigate>
                Manage products
            </a>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Product</th>
                            <th class="px-6 py-3">Barcode</th>
                            <th class="px-6 py-3">Unit</th>
                            <th class="px-6 py-3 text-right">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-6 py-4 font-mono text-gray-600">{{ $product->barcode }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $product->unit_type }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span @class([
                                        'rounded px-2 py-1 text-xs font-bold',
                                        'bg-red-100 text-red-700' => $product->currentStock() <= 5,
                                        'bg-green-100 text-green-700' => $product->currentStock() > 5,
                                    ])>
                                        {{ $product->currentStock() }} left
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">No active products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
