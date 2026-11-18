<x-layouts::app title="Fresh Cuts">
    @php
        $isAdmin = auth()->user()->isAdmin();
        $input = 'w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900';
        $label = 'mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-zinc-400';
        $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8" x-data="{ adding: @js($errors->has('name') || $errors->has('portions_per_kg')) }">

        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-zinc-100 sm:text-3xl">Fresh Cuts</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                    Meat bought by the kg and sold as portions. Portions go down when a linked dish is sold.
                </p>
            </div>

            <div class="flex flex-wrap items-end gap-2">
                <form method="GET" action="{{ route('fresh-cuts.index') }}" class="flex items-end gap-2">
                    <div>
                        <label for="date" class="{{ $label }}">Show day</label>
                        <input type="date" name="date" id="date" value="{{ $date->toDateString() }}" max="{{ now()->toDateString() }}"
                            onchange="this.form.submit()" class="{{ $input }}">
                    </div>
                    @unless ($isToday)
                        <a href="{{ route('fresh-cuts.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-indigo-600 hover:underline dark:text-indigo-400" wire:navigate>Today</a>
                    @endunless
                </form>

                @if ($isAdmin)
                    <button type="button" @click="adding = !adding"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Add fresh cut
                    </button>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-md border-l-4 border-green-400 bg-green-50 p-4 text-sm font-medium text-green-700 dark:bg-green-950/40 dark:text-green-300" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($isAdmin)
            <form method="POST" action="{{ route('fresh-cuts.store') }}" x-show="adding" x-cloak x-transition
                class="mb-6 grid gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 md:grid-cols-[1fr_12rem_auto] md:items-end">
                @csrf
                <div>
                    <label for="name" class="{{ $label }}">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g. Beef" required class="{{ $input }}">
                    @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="portions_per_kg" class="{{ $label }}">Portions per kg</label>
                    <input type="number" name="portions_per_kg" id="portions_per_kg" value="{{ old('portions_per_kg', 8) }}" min="0.1" step="0.1" required class="{{ $input }}">
                    @error('portions_per_kg') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Save</button>
            </form>
        @endif

        @if (! $isToday)
            <p class="mb-4 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                Showing portions at the end of {{ $date->format('l, j F Y') }}.
            </p>
        @endif

        @if ($cuts->isEmpty())
            <div class="rounded-lg border border-dashed border-gray-300 p-10 text-center text-sm text-gray-500 dark:border-zinc-600 dark:text-zinc-400">
                No fresh cuts yet.
                @if ($isAdmin) Use <b>Add fresh cut</b> to set one up, for example Beef at 8 portions per kg. @endif
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($cuts as ['cut' => $cut, 'summary' => $s])
                    @php
                        $tone = match (true) {
                            $s['closing'] <= 0 => 'text-red-600 dark:text-red-400',
                            $cut->portions_per_kg && $s['closing'] < $cut->portions_per_kg => 'text-amber-600 dark:text-amber-400',
                            default => 'text-gray-900 dark:text-zinc-100',
                        };
                        $openLots = collect($s['lots'])->where('remaining', '>', 0);
                    @endphp
                    <a href="{{ route('fresh-cuts.show', ['freshCut' => $cut, 'date' => $isToday ? null : $date->toDateString()]) }}" wire:navigate
                        class="flex flex-col rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-indigo-700 {{ $cut->is_active ? '' : 'opacity-60' }}">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-zinc-100">{{ $cut->name }}</h3>
                                <p class="text-xs text-gray-500 dark:text-zinc-400">
                                    {{ $cut->portions_per_kg ? $fmt($cut->portions_per_kg).' portions per kg' : 'Portions per kg not set' }}
                                    @unless ($cut->is_active) · Not in use @endunless
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold leading-none {{ $tone }}">{{ $s['closing'] }}</div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-zinc-400">
                                    portions left{{ $s['kg_left'] !== null ? ' · ≈ '.$fmt($s['kg_left']).' kg' : '' }}
                                </div>
                            </div>
                        </div>

                        <dl class="mt-4 grid grid-cols-4 gap-2 rounded-md bg-gray-50 p-2 text-center text-xs dark:bg-zinc-800">
                            <div><dt class="text-gray-500 dark:text-zinc-400">Start</dt><dd class="text-sm font-semibold">{{ $s['opening'] }}</dd></div>
                            <div><dt class="text-gray-500 dark:text-zinc-400">Bought</dt><dd class="text-sm font-semibold text-green-700 dark:text-green-400">{{ $s['bought'] ? '+'.$s['bought'] : 0 }}</dd></div>
                            <div><dt class="text-gray-500 dark:text-zinc-400">Sold</dt><dd class="text-sm font-semibold">{{ $s['sold'] - $s['returned'] }}</dd></div>
                            <div><dt class="text-gray-500 dark:text-zinc-400">Written off</dt><dd class="text-sm font-semibold {{ $s['written_off'] ? 'text-red-600 dark:text-red-400' : '' }}">{{ $s['written_off'] }}</dd></div>
                        </dl>

                        @if ($openLots->isNotEmpty())
                            <ul class="mt-3 space-y-1 text-xs text-gray-600 dark:text-zinc-300">
                                @foreach ($openLots as $lot)
                                    <li class="flex justify-between gap-2">
                                        <span>{{ $lot['batch'] ? $fmt($lot['batch']->kg).' kg bought '.$lot['batch']->purchased_at->format('j M') : 'Earlier stock' }}</span>
                                        <span class="font-semibold">{{ $lot['remaining'] }} of {{ $lot['portions'] }} left</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <p class="mt-auto pt-3 text-xs text-gray-500 dark:text-zinc-400">
                            @if ($cut->portionMenuItems->isEmpty())
                                No dishes linked yet.
                            @else
                                Used by: {{ $cut->portionMenuItems->map(fn ($item) => $item->name.' ('.$item->stock_quantity.')')->implode(', ') }}
                            @endif
                        </p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts::app>
