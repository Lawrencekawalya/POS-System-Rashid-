@if ($lowStockProducts->count())
    <div class="mt-4 shrink-0 overflow-hidden rounded-lg border border-red-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-red-100 bg-red-50 px-3 py-2">
            <h3 class="text-xs font-semibold uppercase text-red-600">
                Low Stock Alerts
            </h3>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('products.low-stock') }}" class="text-xs font-semibold text-red-700 hover:text-red-900" wire:navigate>
                    See all
                </a>
            @endif
        </div>

        <ul class="space-y-1 px-3 py-2 text-sm">
            @foreach ($lowStockProducts as $product)
                <li class="flex items-center justify-between gap-2 border-b border-gray-100 py-1.5 last:border-0">
                    <span class="truncate">{{ $product['name'] }}</span>
                    <span class="shrink-0 rounded bg-red-100 px-1.5 py-0.5 text-xs font-semibold text-red-700">
                        {{ $product['stock'] }} left
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
