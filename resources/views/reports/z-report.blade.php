<x-layouts::app title="Z-Report">

    <div class="print:hidden flex justify-between mb-4">
        <form method="GET" class="flex gap-2 items-end">
            <div>
                <label class="text-xs">From</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="border px-2 py-1 rounded text-sm">
            </div>

            <div>
                <label class="text-xs">To</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="border px-2 py-1 rounded text-sm">
            </div>

            <button class="px-3 py-1 bg-black text-white rounded text-sm">
                View
            </button>
        </form>

        <button onclick="window.print()" class="px-3 py-1 bg-black text-white rounded text-sm">
            Print
        </button>
    </div>

    <div class="thermal-receipt mx-auto">

        <h1 class="text-center font-bold mb-2">Z REPORT</h1>
        {{-- <p class="text-center text-xs mb-2">Date: {{ $date }}</p> --}}
        <p class="text-center text-xs mb-2">
            Period: {{ $startDate }} → {{ $endDate }}
        </p>

        <hr class="my-1">

        <div class="text-xs space-y-1">
            <div class="flex justify-between">
                <span>Gross Sales</span>
                <span>{{ number_format($grossSales, 2) }}</span>
            </div>

            <div class="flex justify-between">
                <span>Refunds</span>
                <span>-{{ number_format($refundTotal, 2) }}</span>
            </div>

            <div class="flex justify-between font-semibold">
                <span>Net Sales</span>
                <span>{{ number_format($netSales, 2) }}</span>
            </div>

            <hr class="my-1">

            <div class="flex justify-between">
                <span>Cash Expected</span>
                <span>{{ number_format($cashExpected, 2) }}</span>
            </div>
        </div>

        <hr class="my-2">

        <p class="text-xs text-center">
            Generated {{ now()->format('Y-m-d H:i') }}
        </p>

    </div>

    <style>
        .thermal-receipt {
            width: 80mm;
            padding: 4mm;
            font-family: monospace;
            background: white;
        }

        @media print {
            body {
                background: white !important;
            }

            .print\:hidden {
                display: none !important;
            }

            .thermal-receipt {
                margin: 0;
                padding: 0;
            }
        }
    </style>

</x-layouts::app>
