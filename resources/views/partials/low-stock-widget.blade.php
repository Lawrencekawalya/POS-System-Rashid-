@if ($lowStockProducts->count())
    <div class="mt-4 shrink-0 overflow-hidden rounded-lg border border-red-100 dark:border-red-900 bg-white dark:bg-zinc-900 shadow-sm">
        <div class="flex items-center justify-between border-b border-red-100 dark:border-red-900 bg-red-50 dark:bg-red-950/40 px-3 py-2">
            <h3 class="text-xs font-semibold uppercase text-red-600 dark:text-red-400">
                Low Stock Alerts
            </h3>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('products.low-stock') }}" class="text-xs font-semibold text-red-700 dark:text-red-300 hover:text-red-900 dark:hover:text-red-300" wire:navigate>
                    See all
                </a>
            @endif
        </div>

        <ul class="space-y-1 px-3 py-2 text-sm">
            @foreach ($lowStockProducts as $product)
                <li class="flex items-center justify-between gap-2 border-b border-gray-100 dark:border-zinc-700 py-1.5 last:border-0">
                    <span class="truncate">{{ $product['name'] }}</span>
                    <span class="shrink-0 rounded bg-red-100 dark:bg-red-900/40 px-1.5 py-0.5 text-xs font-semibold text-red-700 dark:text-red-300">
                        {{ $product['stock'] }} left
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
