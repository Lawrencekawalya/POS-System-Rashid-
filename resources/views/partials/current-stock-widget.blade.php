<div class="mt-4 shrink-0 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm">
    <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-3 py-2">
        <h3 class="text-xs font-semibold uppercase text-zinc-700 dark:text-zinc-200">Current Stock Levels</h3>
        <a href="{{ route('products.current-stock') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300" wire:navigate>
            See all
        </a>
    </div>

    <ul class="space-y-1 px-3 py-2 text-sm">
        @forelse ($currentStockProducts as $product)
            <li class="flex items-center justify-between gap-2 border-b border-gray-100 dark:border-zinc-700 py-1.5 last:border-0">
                <span class="truncate">{{ $product['name'] }}</span>
                <span @class([
                    'shrink-0 rounded px-1.5 py-0.5 text-xs font-semibold',
                    'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300' => $product['stock'] <= 5,
                    'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300' => $product['stock'] > 5,
                ])>
                    {{ $product['stock'] }} left
                </span>
            </li>
        @empty
            <li class="py-2 text-xs text-zinc-500 dark:text-zinc-400">No active products yet.</li>
        @endforelse
    </ul>
</div>
