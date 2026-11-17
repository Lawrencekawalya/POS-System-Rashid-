<x-layouts::app title="Receipt">
    @php
        $status = $sale->payment_status ?? 'paid';
        $statusStyles = [
            'paid' => ['Paid', 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300'],
            'partial' => ['Partly paid', 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300'],
            'unpaid' => ['Unpaid', 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300'],
            'refunded' => ['Refunded', 'bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200'],
        ];
        [$statusLabel, $statusClass] = $statusStyles[$status] ?? [ucfirst($status), $statusStyles['refunded'][1]];

        $methodLabels = ['cash' => 'Cash', 'bank' => 'Bank', 'card' => 'Card', 'mobile_money' => 'Mobile money', 'room' => 'Charged to room'];
        $method = $methodLabels[$sale->payment_method] ?? ucfirst((string) $sale->payment_method);

        $refundedQty = fn ($item) => (int) $sale->refunds->where('sale_item_id', $item->id)->sum('quantity');
        $refundedTotal = (float) $sale->refunds->sum('amount');
        $balance = $sale->balance;

        $refundable = $sale->items
            ->map(fn ($item) => ['item' => $item, 'remaining' => $item->quantity - $refundedQty($item)])
            ->filter(fn ($row) => $row['remaining'] > 0)
            ->values();
    @endphp

    <div class="mx-auto max-w-6xl" x-data="{ refunding: @js($errors->any()) }">

        {{-- Header --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden">
            <a href="{{ auth()->user()->isAdmin() ? route('sales.index') : route('pos.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                {{ auth()->user()->isAdmin() ? 'Back to Sales' : 'Back to POS' }}
            </a>

            <div class="flex gap-2">
                @if ($refundable->isNotEmpty())
                    <button type="button" x-on:click="refunding = !refunding"
                        class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l5 5m-5-5l5-5" /></svg>
                        Refund
                    </button>
                @endif
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Print receipt
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950/40 dark:text-green-300 print:hidden">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6 print:hidden">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Sale #{{ $sale->id }}</h1>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ $sale->created_at->format('j M Y, H:i') }}
                &middot; {{ $sale->user->name }}
                &middot; {{ $method }}
                @if ($sale->room)
                    &middot; Room {{ $sale->room->name }}
                @endif
            </p>
        </div>

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_auto]">

            {{-- Sale details --}}
            <div class="space-y-6 print:hidden">
                {{-- Refund panel --}}
                @if ($refundable->isNotEmpty())
                    <form method="POST" action="{{ route('sales.partial-refund', $sale) }}" x-show="refunding" x-cloak x-transition
                        x-data="{
                            qty: @js($refundable->mapWithKeys(fn ($row) => [$row['item']->id => (int) old('items.'.$row['item']->id, 0)])),
                            prices: @js($refundable->mapWithKeys(fn ($row) => [$row['item']->id => (float) $row['item']->unit_price])),
                            remaining: @js($refundable->mapWithKeys(fn ($row) => [$row['item']->id => $row['remaining']])),
                            get total() { return Object.keys(this.qty).reduce((sum, id) => sum + (Number(this.qty[id]) || 0) * this.prices[id], 0) },
                            all() { for (const id in this.remaining) this.qty[id] = this.remaining[id] },
                        }"
                        class="rounded-xl border border-red-200 bg-red-50/60 p-5 dark:border-red-900 dark:bg-red-950/30">
                        @csrf

                        <div class="flex items-center justify-between gap-3">
                            <h2 class="font-semibold text-red-900 dark:text-red-300">Refund items</h2>
                            <button type="button" x-on:click="all()" class="text-sm font-medium text-red-700 hover:underline dark:text-red-300">Select all remaining</button>
                        </div>

                        @if ($errors->any())
                            <ul class="mt-3 list-disc pl-5 text-sm text-red-700 dark:text-red-300">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-4 divide-y divide-red-100 dark:divide-red-900/60">
                            @foreach ($refundable as $row)
                                <div class="flex items-center justify-between gap-4 py-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $row['item']->name }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row['remaining'] }} of {{ $row['item']->quantity }} refundable &middot; {{ number_format($row['item']->unit_price) }} each</p>
                                    </div>
                                    <input type="number" name="items[{{ $row['item']->id }}]" min="0" max="{{ $row['remaining'] }}"
                                        x-model.number="qty[{{ $row['item']->id }}]" aria-label="Quantity of {{ $row['item']->name }} to refund"
                                        class="w-20 rounded-lg border border-zinc-300 bg-white px-2 py-1.5 text-right text-sm dark:border-zinc-600 dark:bg-zinc-900">
                                </div>
                            @endforeach
                        </div>

                        <label for="refund_reason" class="mt-4 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Reason</label>
                        <input id="refund_reason" type="text" name="refund_reason" required maxlength="255" value="{{ old('refund_reason') }}"
                            placeholder="e.g. Customer returned the bottle"
                            class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900">

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-sm text-zinc-700 dark:text-zinc-200">Refund value: <span class="font-bold" x-text="total.toLocaleString()">0</span></p>
                            <div class="flex gap-2">
                                <button type="button" x-on:click="refunding = false" class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">Cancel</button>
                                <button type="submit" x-bind:disabled="total <= 0"
                                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                                    Process refund
                                </button>
                            </div>
                        </div>
                    </form>
                @endif

                {{-- Items and totals --}}
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Item</th>
                                <th class="px-4 py-3 text-right font-medium">Qty</th>
                                <th class="px-4 py-3 text-right font-medium">Price</th>
                                <th class="px-4 py-3 text-right font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">
                                        {{ $item->name }}
                                        @if ($refundedQty($item) > 0)
                                            <span class="ml-1 text-xs text-red-600 dark:text-red-400">{{ $refundedQty($item) }} refunded</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums text-zinc-700 dark:text-zinc-300">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums text-zinc-700 dark:text-zinc-300">{{ number_format($item->unit_price) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums font-medium text-zinc-900 dark:text-zinc-100">{{ number_format($item->subtotal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <dl class="space-y-2 border-t border-zinc-200 px-4 py-4 text-sm dark:border-zinc-700">
                        <div class="flex justify-between font-semibold text-zinc-900 dark:text-zinc-100">
                            <dt>Total</dt><dd class="tabular-nums">{{ number_format($sale->total_amount) }}</dd>
                        </div>
                        <div class="flex justify-between text-zinc-600 dark:text-zinc-300">
                            <dt>Paid</dt><dd class="tabular-nums">{{ number_format($sale->paid_amount) }}</dd>
                        </div>
                        @if ($sale->change_amount > 0)
                            <div class="flex justify-between text-zinc-600 dark:text-zinc-300">
                                <dt>Change</dt><dd class="tabular-nums">{{ number_format($sale->change_amount) }}</dd>
                            </div>
                        @endif
                        @if ($refundedTotal > 0)
                            <div class="flex justify-between text-red-600 dark:text-red-400">
                                <dt>Refunded</dt><dd class="tabular-nums">&minus;{{ number_format($refundedTotal) }}</dd>
                            </div>
                        @endif
                        @if ($balance > 0)
                            <div class="flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2 font-semibold text-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
                                <dt>Balance due</dt>
                                <dd class="flex items-center gap-3 tabular-nums">
                                    {{ number_format($balance) }}
                                    @if ($sale->room)
                                        <a href="{{ route('rooms.folio', $sale->room) }}" class="text-xs font-medium underline">Settle on folio</a>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Payments --}}
                <section>
                    <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Payments</h2>
                    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                        @forelse ($sale->payments as $payment)
                            <div class="flex items-center justify-between gap-4 border-b border-zinc-100 px-4 py-3 text-sm last:border-0 dark:border-zinc-800">
                                <div>
                                    <p class="text-zinc-900 dark:text-zinc-100">{{ $methodLabels[$payment->method] ?? ucfirst($payment->method) }}{{ $payment->amount < 0 ? ' refund' : '' }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $payment->created_at->format('j M Y, H:i') }}{{ $payment->remarks ? ' · '.$payment->remarks : '' }}</p>
                                </div>
                                <p class="tabular-nums font-medium {{ $payment->amount < 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-100' }}">
                                    {{ $payment->amount < 0 ? '−' : '' }}{{ number_format(abs($payment->amount)) }}
                                </p>
                            </div>
                        @empty
                            <p class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">No payments recorded yet.</p>
                        @endforelse
                    </div>
                </section>

                {{-- Refund history --}}
                @if ($sale->refunds->isNotEmpty())
                    <section>
                        <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Refunds</h2>
                        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                            @foreach ($sale->refunds as $refund)
                                <div class="flex items-center justify-between gap-4 border-b border-zinc-100 px-4 py-3 text-sm last:border-0 dark:border-zinc-800">
                                    <div class="min-w-0">
                                        <p class="text-zinc-900 dark:text-zinc-100">{{ $refund->quantity }} &times; {{ $refund->saleItem?->name ?? 'Item' }}</p>
                                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $refund->created_at->format('j M Y, H:i') }} &middot; {{ $refund->reason }}</p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="tabular-nums text-red-600 dark:text-red-400">&minus;{{ number_format($refund->amount) }}</span>
                                        <a href="{{ route('sales.refund-receipt', [$sale, $refund]) }}" class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">Receipt</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- Thermal receipt (printed) --}}
            <div class="lg:sticky lg:top-6">
                <div class="light thermal-receipt rounded-sm shadow-md print:shadow-none">
                    <div class="text-center">
                        <img src="{{ asset('images/og-share.png') }}" alt="" class="receipt-logo mx-auto">
                        <h1 class="mt-1 text-base font-bold">Palace Hotel</h1>
                        <p>Mbarara, Uganda</p>
                        <p class="mt-1">Receipt #{{ $sale->id }}</p>
                        <p>{{ $sale->created_at->format('Y-m-d H:i') }}</p>
                        <p>Cashier: {{ $sale->user->name }}</p>
                        @if ($sale->room)
                            <p class="mt-1 font-bold uppercase">Room: {{ $sale->room->name }}</p>
                        @endif
                    </div>

                    <div class="receipt-rule"></div>

                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="text-left">Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Amt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td class="text-left">{{ $item->name }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">{{ number_format($item->subtotal) }}</td>
                                </tr>
                                @if ($item->quantity > 1)
                                    <tr>
                                        <td colspan="3" class="text-right text-[10px]">@ {{ number_format($item->unit_price) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>

                    <div class="receipt-rule"></div>

                    <div class="space-y-0.5">
                        <div class="flex justify-between font-bold"><span>Total</span><span>{{ number_format($sale->total_amount) }}</span></div>
                        <div class="flex justify-between"><span>Paid ({{ $method }})</span><span>{{ number_format($sale->paid_amount) }}</span></div>
                        @if ($sale->change_amount > 0)
                            <div class="flex justify-between"><span>Change</span><span>{{ number_format($sale->change_amount) }}</span></div>
                        @endif
                        @if ($refundedTotal > 0)
                            <div class="flex justify-between"><span>Refunded</span><span>-{{ number_format($refundedTotal) }}</span></div>
                        @endif
                        @if ($balance > 0)
                            <div class="flex justify-between font-bold"><span>Balance due</span><span>{{ number_format($balance) }}</span></div>
                        @endif
                    </div>

                    <div class="receipt-rule"></div>

                    <p class="text-center">Thank you for shopping<br>Please come again!</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Thermal print styles --}}
    <style>
        [x-cloak] { display: none !important; }

        .thermal-receipt {
            width: 80mm;
            margin: 0 auto;
            padding: 5mm 4mm;
            font-family: ui-monospace, monospace;
            font-size: 12px;
            line-height: 1.35;
            color: black;
            background: white;
        }

        .receipt-logo {
            width: 22mm;
            height: 22mm;
            object-fit: cover;
        }

        .receipt-rule {
            margin: 6px 0;
            border-top: 1px dashed #000;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            html,
            body {
                width: 80mm;
                margin: 0;
                padding: 0;
                background: white;
            }

            body * {
                visibility: hidden;
            }

            .thermal-receipt,
            .thermal-receipt * {
                visibility: visible;
            }

            .thermal-receipt {
                position: absolute;
                left: 0;
                top: 0;
                box-shadow: none;
            }
        }
    </style>
</x-layouts::app>
