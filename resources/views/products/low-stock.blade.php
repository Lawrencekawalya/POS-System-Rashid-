<x-layouts::app title="Low Stock Products">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Low Stock Products</h2>
                <p class="mt-1 text-sm text-gray-500">Active products with five or fewer units remaining.</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800" wire:navigate>
                View all products
            </a>
        </div>

        <div class="overflow-hidden rounded-lg border border-red-100 bg-white shadow-sm">
            @forelse ($products as $product)
                <div class="flex flex-col gap-3 border-b border-gray-100 p-4 last:border-0 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $product->name }}</p>
                        <p class="text-sm text-gray-500">{{ $product->barcode }} · {{ $product->unit_type }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-bold text-red-700">
                            {{ $product->currentStock() }} remaining
                        </span>
                        <a href="{{ route('products.edit', $product) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800" wire:navigate>
                            Edit
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <p class="font-medium text-gray-900">All active products are sufficiently stocked.</p>
                    <p class="mt-1 text-sm text-gray-500">Products appear here when their stock reaches five units or fewer.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts::app>
