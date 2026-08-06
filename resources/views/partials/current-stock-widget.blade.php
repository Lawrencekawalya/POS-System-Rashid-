<div class="mt-4 px-3">
    <h3 class="text-xs font-semibold text-zinc-600 dark:text-zinc-300 mb-2 uppercase">
        Current Stock Levels
    </h3>

    <div class="max-h-64 overflow-y-auto pr-1 space-y-1">
        @foreach($products as $product)
            <div class="flex justify-between items-center text-sm">
                <div class="truncate">
                    {{ $product->name }}
                </div>

                <span class="text-xs font-semibold px-2 py-0.5 rounded
                    {{ $product->currentStock() <= 5
                        ? 'bg-red-100 text-red-700'
                        : 'bg-green-100 text-green-700' }}">
                    {{ $product->currentStock() }}
                </span>
            </div>
        @endforeach
    </div>
</div>
