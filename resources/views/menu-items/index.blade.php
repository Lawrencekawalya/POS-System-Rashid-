<x-layouts::app title="Kitchen Menu">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-zinc-100 sm:text-3xl sm:truncate">
                    Kitchen Menu
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">Manage kitchen items and optionally link fresh cuts to portion-based stock.</p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('menu-items.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Menu Item
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-950/40 border-l-4 border-green-400 text-green-700 dark:text-green-300 shadow-sm" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Results update while typing; Enter or no JavaScript still does a normal search. --}}
        <div x-data="{
                q: @js((string) request('search', '')),
                loading: false,
                request: null,
                async run() {
                    const url = new URL(@js(route('menu-items.index')));
                    if (this.q.trim()) url.searchParams.set('search', this.q.trim());
                    this.request?.abort();
                    this.request = new AbortController();
                    this.loading = true;
                    try {
                        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: this.request.signal });
                        this.$refs.results.innerHTML = await response.text();
                        history.replaceState(history.state, '', url);
                    } catch (error) {
                        if (error.name !== 'AbortError') throw error;
                    } finally {
                        this.loading = false;
                    }
                },
            }">
        <div class="bg-white dark:bg-zinc-900 shadow-sm border border-gray-200 dark:border-zinc-700 rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('menu-items.index') }}" @submit.prevent="run()" role="search">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg>
                    <input type="search" x-ref="search" name="search" x-model="q" @input.debounce.250ms="run()" @keydown.escape="q = ''; run()"
                        value="{{ request('search') }}" placeholder="Type to search by name or category..." autocomplete="off" aria-label="Search menu items"
                        class="w-full rounded-md border py-2 pl-10 pr-10 focus:border-indigo-500 focus:ring-indigo-500">
                    <svg x-show="loading" x-cloak class="absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
                    <button type="button" x-show="q && !loading" x-cloak @click="q = ''; run(); $refs.search.focus()"
                        class="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:text-zinc-500 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                        aria-label="Clear search" title="Clear search">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </form>
        </div>

        <div x-ref="results">
        @fragment('results')
        <div class="bg-white dark:bg-zinc-900 shadow-sm border border-gray-200 dark:border-zinc-700 rounded-lg overflow-hidden">
            @if ($menuItems->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-800">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Name</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Category</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Price</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Stock Impact</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                            @foreach ($menuItems as $item)
                                <tr class=" hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-zinc-100">{{ $item->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300">
                                            {{ $item->category ?? 'General' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900 dark:text-zinc-100">
                                            {{ number_format($item->price, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if ($item->stockProduct)
                                            <span class="font-semibold text-amber-700 dark:text-amber-300">{{ $item->stock_quantity }} portion(s)</span>
                                            <span class="text-gray-500 dark:text-zinc-400">of {{ $item->stockProduct->name }}</span>
                                        @else
                                            <span class="text-gray-500 dark:text-zinc-400">No stock movement</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('menu-items.edit', $item) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Edit</a>
                                            
                                            <form action="{{ route('menu-items.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this menu item?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-zinc-800 border-t">
                    {{ $menuItems->appends(request()->query())->links() }}
                </div>
            @else
                <div class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    @if (request()->filled('search'))
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-zinc-100">No menu items match "{{ request('search') }}"</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">Try another name or category, or add it as a new menu item.</p>
                    @else
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-zinc-100">No menu items found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">Get started by creating a new menu item.</p>
                    @endif
                    <div class="mt-6">
                        <a href="{{ route('menu-items.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Add Menu Item
                        </a>
                    </div>
                </div>
            @endif
        </div>
        @endfragment
        </div>
        </div>
    </div>
</x-layouts::app>
