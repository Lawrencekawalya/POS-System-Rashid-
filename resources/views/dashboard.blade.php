{{-- <x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts::app> --}}


<x-layouts::app :title="__('Dashboard')">

    {{-- Top KPIs --}}
    <div class="grid gap-4 md:grid-cols-3">

        <x-dashboard.kpi-card label="Net Sales Today" :value="number_format($netSales, 0)" :subtext="'Gross: ' . number_format($grossSales, 0)" />

        <x-dashboard.kpi-card label="Cash Expected" :value="number_format($cashExpected, 0)" :subtext="$cashExpected >= 0 ? 'Balanced' : 'Short'" />

        <x-dashboard.kpi-card label="Refunds Today" :value="number_format($refundTotal, 0)" :subtext="$refundCount . ' refunds'" />

    </div>

    {{-- Activity feed --}}
    <div class="mt-6 rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="p-4 font-semibold border-b dark:border-neutral-700">
            Today’s Sales Activity
        </div>

        <table class="w-full text-sm">
            <thead class="bg-neutral-100 dark:bg-neutral-800">
                <tr>
                    <th class="px-3 py-2 text-left">Time</th>
                    <th class="px-3 py-2 text-left">Sale #</th>
                    <th class="px-3 py-2 text-right">Amount</th>
                    <th class="px-3 py-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentSales as $sale)
                    <tr class="border-t dark:border-neutral-700">
                        <td class="px-3 py-2">
                            {{ $sale->created_at->format('H:i') }}
                        </td>
                        <td class="px-3 py-2">
                            #{{ $sale->id }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            {{ number_format($sale->total_amount, 0) }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            @if ($sale->isRefunded())
                                <span class="text-red-600">Refunded</span>
                            @else
                                <span class="text-green-600">OK</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-center text-neutral-500">
                            No sales today
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-layouts::app>
