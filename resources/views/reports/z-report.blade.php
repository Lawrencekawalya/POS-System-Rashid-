{{--<x-layouts::app title="Z-Report">

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
        {{-- <p class="text-center text-xs mb-2">Date: {{ $date }}</p> -
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

    {{--<style>
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
    </style>--}

    <style>
        .thermal-receipt {
            width: 80mm;
            margin: 0 auto;
            font-family: monospace;
            font-size: 12px;
            color: black;
            background: white;
        }

        @media print {

            @page {
                size: 80mm auto;
                /* THIS IS THE IMPORTANT PART */
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
            }
        }
    </style>

</x-layouts::app>--}}

<x-layouts::app title="Z-Report">

    <div class="print:hidden flex justify-between mb-4">
        <form method="GET" class="flex gap-2 items-end">
            <div>
                <label class="text-xs font-bold text-gray-500">From</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="border px-2 py-1 rounded text-sm focus:ring-2 focus:ring-black">
            </div>

            <div>
                <label class="text-xs font-bold text-gray-500">To</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="border px-2 py-1 rounded text-sm focus:ring-2 focus:ring-black">
            </div>

            <button class="px-4 py-1 bg-black text-white rounded text-sm hover:bg-gray-800 transition">
                View
            </button>
        </form>

        <button onclick="window.print()" class="px-4 py-1 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700 transition">
            Print Report
        </button>
    </div>

    <div class="thermal-receipt mx-auto shadow-sm border border-gray-100">

        <h1 class="text-center font-bold text-lg mb-1">Z REPORT</h1>
        <p class="text-center text-[10px] uppercase tracking-widest mb-2 underline">Financial Summary</p>
        
        <p class="text-center text-[10px] mb-2 border-y border-dashed py-1">
            {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} → {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        </p>

        <div class="text-xs space-y-1 mt-3">
            <div class="flex justify-between">
                <span>Gross Sales</span>
                <span>{{ number_format($grossSales) }}</span>
            </div>

            <div class="flex justify-between text-red-600 italic">
                <span>Total Refunds</span>
                <span>-{{ number_format($refundTotal) }}</span>
            </div>

            <div class="flex justify-between font-bold border-t border-black pt-1 mt-1">
                <span>Net Sales</span>
                <span>{{ number_format($netSales) }}</span>
            </div>

            {{-- Strictly Confirmed Expenses --}}
            <div class="flex justify-between text-red-600 italic">
                <span>Confirmed Expenses</span>
                <span>-{{ number_format($totalExpenses) }}</span>
            </div>

            <div class="flex justify-between font-bold border-t-2 border-black pt-1 mt-2 text-sm bg-gray-50 px-1">
                <span>CASH EXPECTED</span>
                <span>{{ number_format($cashExpected) }}</span>
            </div>
        </div>

        {{-- Breakdown Section --}}
        @if($expenses->count() > 0)
            <div class="mt-4">
                <p class="text-[10px] font-bold border-b border-dashed mb-1">VERIFIED PAYOUTS</p>
                @foreach($expenses as $expense)
                    <div class="flex justify-between text-[10px]">
                        <span class="truncate mr-2">{{ $expense->category }} ({{ $expense->user->name ?? 'Staff' }})</span>
                        <span>{{ number_format($expense->amount) }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Optional: Show Pending for Admin Awareness (Not subtracted from totals) --}}
        @php
            $pendingExpenses = \App\Models\Expense::whereBetween('expense_date', [$startDate, $endDate])
                                ->where('status', 'pending')->get();
        @endphp

        @if($pendingExpenses->count() > 0)
            <div class="mt-4 p-2 bg-yellow-50 border border-yellow-200 print:bg-transparent print:border-black">
                <p class="text-[9px] font-bold text-yellow-800 uppercase print:text-black italic underline">
                    * Pending Review (Not in Total)
                </p>
                @foreach($pendingExpenses as $pending)
                    <div class="flex justify-between text-[9px] text-yellow-700 print:text-black">
                        <span>{{ $pending->category }}</span>
                        <span>{{ number_format($pending->amount) }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-6 border-t border-dashed pt-2 text-center text-[9px] text-gray-500 italic">
            <p>Generated by: {{ auth()->user()->name }}</p>
            <p>{{ now()->format('Y-m-d H:i:s') }}</p>
        </div>

    </div>

    {{--<style>
        .thermal-receipt {
            width: 80mm;
            padding: 8mm 4mm;
            font-family: 'Courier New', Courier, monospace;
            background: white;
            color: black;
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
                width: 100%;
                border: none;
                box-shadow: none;
            }
        }
    </style>--}}

    <style>
        .thermal-receipt {
            width: 80mm;
            margin: 0 auto;
            font-family: monospace;
            font-size: 12px;
            color: black;
            background: white;
        }

        @media print {

            @page {
                size: 80mm auto;
                /* THIS IS THE IMPORTANT PART */
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
            }
        }
    </style>

</x-layouts::app>
