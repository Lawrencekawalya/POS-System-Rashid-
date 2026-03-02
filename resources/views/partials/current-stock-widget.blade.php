<div class="border rounded-lg bg-white shadow-sm overflow-hidden">
    <div class="max-h-[88vh] overflow-y-auto px-2 py-2">
        <h3 class="text-xs font-semibold text-red-600 mb-2 uppercase">
            Current Stock Levels
        </h3>
        @foreach($products as $product)
            <div class="flex justify-between items-center p-2 border-b last:border-0 hover:bg-gray-50">
                <div>
                    <div class="font-medium text-sm text-gray-800">
                        {{ $product->name }}
                    </div>
                    <div class="text-xs text-gray-500 font-mono">
                        {{ $product->barcode }}
                    </div>
                </div>

                <div class="text-right">
                    <span
                        class="inline-block px-2 py-1 rounded text-xs font-bold 
                        {{ $product->currentStock() <= 5 
                            ? 'bg-red-100 text-red-700' 
                            : 'bg-green-100 text-green-700' }}">
                        {{ $product->currentStock() }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>