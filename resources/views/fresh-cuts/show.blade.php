<x-layouts::app :title="$cut->name.' · Fresh Cuts'">
    @php
        $isAdmin = auth()->user()->isAdmin();
        $input = 'w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900';
        $label = 'mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-zinc-400';
        $panel = 'rounded-lg border border-gray-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900';
        $th = 'px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-zinc-400';
        $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
        $typeLabels = ['purchase' => 'Bought', 'sale' => 'Sold', 'refund' => 'Returned (refund)', 'write_off' => 'Written off'];
        $s = $summary;
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('fresh-cuts.index', ['date' => $isToday ? null : $date->toDateString()]) }}" wire:navigate class="text-sm text-indigo-600 hover:underline dark:text-indigo-400">&larr; All fresh cuts</a>

        <div class="mt-2 mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-zinc-100 sm:text-3xl">{{ $cut->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                    {{ $cut->portions_per_kg ? $fmt($cut->portions_per_kg).' portions per kg' : 'Portions per kg not set' }}
                    @if ((float) $cut->cost_price > 0) · about {{ number_format($cut->cost_price) }} per portion @endif
                    @unless ($cut->is_active) · Not in use @endunless
                </p>
            </div>
            <form method="GET" class="flex items-end gap-2">
                <div>
                    <label for="date" class="{{ $label }}">Show day</label>
                    <input type="date" name="date" id="date" value="{{ $date->toDateString() }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()" class="{{ $input }}">
                </div>
                @unless ($isToday)
                    <a href="{{ route('fresh-cuts.show', $cut) }}" wire:navigate class="rounded-md px-3 py-2 text-sm font-medium text-indigo-600 hover:underline dark:text-indigo-400">Today</a>
                @endunless
            </form>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-md border-l-4 border-green-400 bg-green-50 p-4 text-sm font-medium text-green-700 dark:bg-green-950/40 dark:text-green-300" role="alert">{{ session('success') }}</div>
        @endif
        @error('batch')
            <div class="mb-6 rounded-md border-l-4 border-red-400 bg-red-50 p-4 text-sm font-medium text-red-700 dark:bg-red-950/40 dark:text-red-300" role="alert">{{ $message }}</div>
        @enderror

        {{-- Day summary --}}
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ([
                ['Start of day', $s['opening'], ''],
                ['Bought', $s['bought'] ? '+'.$s['bought'] : 0, 'text-green-700 dark:text-green-400'],
                ['Sold', $s['sold'], ''],
                ['Returned', $s['returned'] ? '+'.$s['returned'] : 0, ''],
                ['Written off', $s['written_off'], $s['written_off'] ? 'text-red-600 dark:text-red-400' : ''],
                [$isToday ? 'Left now' : 'End of day', $s['closing'], $s['closing'] <= 0 ? 'text-red-600 dark:text-red-400' : 'text-indigo-600 dark:text-indigo-400'],
            ] as [$name, $value, $tone])
                <div class="{{ $panel }} p-3">
                    <div class="text-xs font-bold uppercase text-gray-500 dark:text-zinc-400">{{ $name }}</div>
                    <div class="mt-1 text-2xl font-bold {{ $tone }}">{{ $value }}</div>
                    @if ($loop->last && $s['kg_left'] !== null)
                        <div class="text-xs text-gray-500 dark:text-zinc-400">≈ {{ $fmt($s['kg_left']) }} kg</div>
                    @endif
                </div>
            @endforeach
        </div>
        @unless ($isToday)
            <p class="-mt-3 mb-6 text-sm text-gray-500 dark:text-zinc-400">Figures for {{ $date->format('l, j F Y') }}.</p>
        @endunless

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="min-w-0 space-y-6">

                {{-- Purchases, oldest used first --}}
                <section class="{{ $panel }} overflow-hidden">
                    <div class="border-b border-gray-200 px-4 py-3 dark:border-zinc-700">
                        <h3 class="font-bold">Purchases</h3>
                        <p class="text-xs text-gray-500 dark:text-zinc-400">Portions are taken from the oldest purchase first.</p>
                    </div>
                    @if (empty($s['lots']))
                        <p class="p-4 text-sm text-gray-500 dark:text-zinc-400">No purchases yet.</p>
                    @else
                        <div class="relative overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-zinc-700">
                                <thead class="bg-gray-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="{{ $th }}">Bought</th>
                                        <th class="{{ $th }}">Weight</th>
                                        <th class="{{ $th }}">Portions</th>
                                        <th class="{{ $th }}">Cost</th>
                                        <th class="{{ $th }}">Used</th>
                                        <th class="{{ $th }}">Left</th>
                                        @if ($isAdmin) <th class="{{ $th }}"><span class="sr-only">Actions</span></th> @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                                    @foreach (array_reverse($s['lots']) as $lot)
                                        @php $batch = $lot['batch']; @endphp
                                        <tr class="{{ $lot['remaining'] === 0 ? 'text-gray-400 dark:text-zinc-500' : '' }}">
                                            <td class="whitespace-nowrap px-4 py-2">
                                                @if ($batch)
                                                    {{ $batch->purchased_at->format('j M Y, H:i') }}
                                                    @if ($batch->supplier) <div class="text-xs text-gray-500 dark:text-zinc-400">{{ $batch->supplier }}</div> @endif
                                                @else
                                                    Earlier stock <div class="text-xs text-gray-500 dark:text-zinc-400">From Purchases, before Fresh Cuts</div>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-2">{{ $batch ? $fmt($batch->kg).' kg × '.$fmt($batch->portions_per_kg) : '—' }}</td>
                                            <td class="px-4 py-2">{{ $lot['portions'] }}</td>
                                            <td class="whitespace-nowrap px-4 py-2">{{ $batch && (float) $batch->total_cost > 0 ? number_format($batch->total_cost) : '—' }}</td>
                                            <td class="px-4 py-2">{{ $lot['used'] }}</td>
                                            <td class="px-4 py-2 font-bold {{ $lot['remaining'] > 0 ? 'text-indigo-600 dark:text-indigo-400' : '' }}">{{ $lot['remaining'] }}</td>
                                            @if ($isAdmin)
                                                <td class="px-4 py-2 text-right">
                                                    @if ($batch && $lot['used'] === 0 && $isToday)
                                                        <form method="POST" action="{{ route('fresh-cuts.purchases.destroy', [$cut, $batch]) }}" onsubmit="return confirm('Remove this purchase of {{ $fmt($batch->kg) }} kg?')">
                                                            @csrf @method('DELETE')
                                                            <button class="text-xs font-semibold text-red-600 hover:underline dark:text-red-400">Remove</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                {{-- Every movement --}}
                <section class="{{ $panel }} overflow-hidden">
                    <div class="border-b border-gray-200 px-4 py-3 dark:border-zinc-700">
                        <h3 class="font-bold">History</h3>
                    </div>
                    @if ($history->isEmpty())
                        <p class="p-4 text-sm text-gray-500 dark:text-zinc-400">Nothing recorded yet.</p>
                    @else
                        <div class="relative overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-zinc-700">
                                <thead class="bg-gray-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="{{ $th }}">When</th>
                                        <th class="{{ $th }}">What</th>
                                        <th class="{{ $th }} text-right">Portions</th>
                                        <th class="{{ $th }}">Details</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                                    @foreach ($history as $move)
                                        <tr>
                                            <td class="whitespace-nowrap px-4 py-2">{{ $move->created_at->format('j M Y, H:i') }}</td>
                                            <td class="whitespace-nowrap px-4 py-2">{{ $typeLabels[$move->type] ?? ucfirst(str_replace('_', ' ', $move->type)) }}</td>
                                            <td class="whitespace-nowrap px-4 py-2 text-right font-semibold {{ $move->quantity > 0 ? 'text-green-700 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ $move->quantity > 0 ? '+' : '' }}{{ $move->quantity }}
                                            </td>
                                            <td class="min-w-56 px-4 py-2 text-gray-600 dark:text-zinc-300">
                                                @if (in_array($move->type, ['sale', 'refund']) && $move->reference_id)
                                                    <a href="{{ route('sales.show', $move->reference_id) }}" wire:navigate class="text-indigo-600 hover:underline dark:text-indigo-400">Sale #{{ $move->reference_id }}</a>
                                                    @if ($move->remarks) · {{ $move->remarks }} @endif
                                                @else
                                                    {{ $move->remarks }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($history->hasPages())
                            <div class="border-t border-gray-200 px-4 py-3 dark:border-zinc-700">{{ $history->withQueryString()->links() }}</div>
                        @endif
                    @endif
                </section>
            </div>

            <aside class="space-y-6">
                @if ($isAdmin)
                    {{-- Record a purchase --}}
                    <form method="POST" action="{{ route('fresh-cuts.purchases.store', $cut) }}" class="{{ $panel }} space-y-3 p-4"
                        x-data="{ kg: @js(old('kg', '')), perKg: @js(old('portions_per_kg', $cut->portions_per_kg ? $fmt($cut->portions_per_kg) : '')) }">
                        @csrf
                        <h3 class="font-bold">Record a purchase</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="kg" class="{{ $label }}">Kg bought</label>
                                <input type="number" name="kg" id="kg" x-model="kg" min="0.01" step="0.01" required class="{{ $input }}">
                            </div>
                            <div>
                                <label for="purchase_portions_per_kg" class="{{ $label }}">Portions per kg</label>
                                <input type="number" name="portions_per_kg" id="purchase_portions_per_kg" x-model="perKg" min="0.1" step="0.1" required class="{{ $input }}">
                            </div>
                        </div>
                        <p class="rounded-md bg-indigo-50 px-3 py-2 text-sm text-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-300">
                            Adds <b x-text="Math.round((parseFloat(kg) || 0) * (parseFloat(perKg) || 0))">0</b> portions
                        </p>
                        @foreach (['kg', 'portions_per_kg', 'purchased_at', 'total_cost'] as $field)
                            @error($field) <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        @endforeach
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="total_cost" class="{{ $label }}">Total cost</label>
                                <input type="number" name="total_cost" id="total_cost" value="{{ old('total_cost') }}" min="0" step="1" placeholder="Optional" class="{{ $input }}">
                            </div>
                            <div>
                                <label for="supplier" class="{{ $label }}">Supplier</label>
                                <input type="text" name="supplier" id="supplier" value="{{ old('supplier') }}" placeholder="Optional" class="{{ $input }}">
                            </div>
                        </div>
                        <div>
                            <label for="purchased_at" class="{{ $label }}">Bought on</label>
                            <input type="datetime-local" name="purchased_at" id="purchased_at" value="{{ old('purchased_at', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required class="{{ $input }}">
                        </div>
                        <button class="w-full rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Add portions</button>
                    </form>

                    {{-- Write off --}}
                    <form method="POST" action="{{ route('fresh-cuts.write-offs.store', $cut) }}" class="{{ $panel }} space-y-3 p-4">
                        @csrf
                        <div>
                            <h3 class="font-bold">Write off portions</h3>
                            <p class="text-xs text-gray-500 dark:text-zinc-400">For portions that were spoiled, eaten by staff or are missing at a count.</p>
                        </div>
                        <div class="grid grid-cols-[6rem_1fr] gap-3">
                            <div>
                                <label for="portions" class="{{ $label }}">Portions</label>
                                <input type="number" name="portions" id="portions" value="{{ old('portions') }}" min="1" max="{{ max(1, $s['closing']) }}" step="1" required class="{{ $input }}">
                            </div>
                            <div>
                                <label for="reason" class="{{ $label }}">Reason</label>
                                <select name="reason" id="reason" class="{{ $input }}">
                                    @foreach ($reasons as $value => $text)
                                        <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="note" class="{{ $label }}">Note</label>
                            <input type="text" name="note" id="note" value="{{ old('note') }}" maxlength="150" placeholder="Optional" class="{{ $input }}">
                        </div>
                        @foreach (['portions', 'reason', 'note'] as $field)
                            @error($field) <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        @endforeach
                        <button class="w-full rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50" @disabled($s['closing'] <= 0 || ! $isToday)>Write off</button>
                    </form>
                @endif

                {{-- Dishes --}}
                <section class="{{ $panel }} p-4">
                    <h3 class="font-bold">Dishes using {{ $cut->name }}</h3>
                    @if ($cut->portionMenuItems->isEmpty())
                        <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">None yet. Link a dish from Kitchen Menu → Edit → Fresh-cut stock movement.</p>
                    @else
                        <ul class="mt-2 space-y-1 text-sm">
                            @foreach ($cut->portionMenuItems->sortBy('name') as $item)
                                <li class="flex justify-between gap-2">
                                    <a href="{{ route('menu-items.edit', $item) }}" wire:navigate class="hover:underline">{{ $item->name }}</a>
                                    <span class="whitespace-nowrap text-gray-500 dark:text-zinc-400">{{ $item->stock_quantity }} portion{{ $item->stock_quantity == 1 ? '' : 's' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                @if ($isAdmin)
                    {{-- Settings --}}
                    <form method="POST" action="{{ route('fresh-cuts.update', $cut) }}" class="{{ $panel }} space-y-3 p-4">
                        @csrf @method('PUT')
                        <h3 class="font-bold">Settings</h3>
                        <div>
                            <label for="cut_name" class="{{ $label }}">Name</label>
                            <input type="text" name="name" id="cut_name" value="{{ old('name', $cut->name) }}" required class="{{ $input }}">
                            @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="cut_portions_per_kg" class="{{ $label }}">Usual portions per kg</label>
                            <input type="number" name="portions_per_kg" id="cut_portions_per_kg" value="{{ $cut->portions_per_kg ? $fmt($cut->portions_per_kg) : '' }}" min="0.1" step="0.1" required class="{{ $input }}">
                        </div>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="is_active" value="1" @checked($cut->is_active) class="rounded border-gray-300 dark:border-zinc-600">
                            In use (shown when linking dishes)
                        </label>
                        <button class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold hover:bg-gray-50 dark:border-zinc-600 dark:hover:bg-zinc-800">Save settings</button>
                    </form>
                @endif
            </aside>
        </div>
    </div>
</x-layouts::app>
