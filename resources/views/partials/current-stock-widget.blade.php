<div class="mt-4 shrink-0 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-3 py-2">
        <h3 class="text-xs font-semibold uppercase text-zinc-700">Current Stock Levels</h3>
        <a href="{{ route('products.current-stock') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800" wire:navigate>
            See all
        </a>
    </div>

    <ul class="space-y-1 px-3 py-2 text-sm">
        @forelse ($currentStockProducts as $product)
            <li class="flex items-center justify-between gap-2 border-b border-gray-100 py-1.5 last:border-0">
                <span class="truncate">{{ $product['name'] }}</span>
                <span @class([
                    'shrink-0 rounded px-1.5 py-0.5 text-xs font-semibold',
                    'bg-red-100 text-red-700' => $product['stock'] <= 5,
                    'bg-green-100 text-green-700' => $product['stock'] > 5,
                ])>
                    {{ $product['stock'] }} left
                </span>
            </li>
        @empty
            <li class="py-2 text-xs text-zinc-500">No active products yet.</li>
        @endforelse
    </ul>
</div>
