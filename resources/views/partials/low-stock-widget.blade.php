@if ($lowStockProducts->count())
    <div class="mt-4 px-3">
        <h3 class="text-xs font-semibold text-red-600 mb-2 uppercase">
            Low Stock Alerts
        </h3>

        <ul class="space-y-1 text-sm">
            @foreach ($lowStockProducts as $product)
                <li class="flex justify-between">
                    <span class="truncate">{{ $product['name'] }}</span>
                    <span class="text-red-600 font-semibold">
                        {{ $product['stock'] }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
