<x-layouts::app :title="__('Menu QR Code')">
    <style>
        .qr-card {
            width: 9cm;
            background: #fffdf9;
            color: #2b1d16;
            border: 1px solid #e5d6c6;
            border-radius: 14px;
            overflow: hidden;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            text-align: center;
        }

        .qr-card__band {
            padding: 18px 16px 22px;
            color: #fff;
            background:
                repeating-linear-gradient(45deg, rgb(255 255 255 / 0.06) 0 10px, transparent 10px 20px),
                linear-gradient(120deg, #e8742a, #f3a53c);
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .qr-card__band h2 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .qr-card__tag {
            display: inline-block;
            margin: 18px 0 4px;
            padding: 4px 16px;
            background: #7b2433;
            color: #fff;
            font-weight: 600;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .qr-card__code svg {
            display: block;
            width: 6.2cm;
            height: 6.2cm;
            margin: 6px auto 0;
        }

        .qr-card__hint {
            margin: 4px 16px 0;
            color: #a5402a;
            font-size: 0.85rem;
        }

        .qr-card__url {
            margin: 6px 16px 18px;
            color: #6f5a4d;
            font-size: 0.75rem;
            word-break: break-all;
        }

        @media print {
            @page { margin: 1.5cm; }
            body * { visibility: hidden; }
            #qr-card, #qr-card * { visibility: visible; }
            #qr-card { position: absolute; top: 0; left: 0; }
        }
    </style>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100">Menu QR Code</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">Print this card for tables and rooms. Guests scan it to open the menu on their phone.</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 items-start">
            <div id="qr-card" class="light qr-card shadow-sm mx-auto lg:mx-0">
                <div class="qr-card__band">
                    <h2>{{ $title }}</h2>
                </div>
                <div class="qr-card__tag">Scan for our menu</div>
                <div class="qr-card__code" role="img" aria-label="QR code linking to {{ $url }}">{!! $svg !!}</div>
                <p class="qr-card__hint">Point your phone camera at the code</p>
                <p class="qr-card__url">{{ $url }}</p>
            </div>

            <div class="flex-1 w-full space-y-4">
                @if ($isLocalAddress)
                    <div class="rounded-xl border border-amber-300 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 p-4">
                        <p class="font-bold text-amber-900 dark:text-amber-300">Don't print this one yet</p>
                        <p class="mt-1 text-sm text-amber-800 dark:text-amber-300">
                            This code points to <span class="font-mono">{{ $url }}</span>, which only opens on this computer or local network.
                            Open this page on the live site and print it from there, so guests' phones can reach the menu.
                        </p>
                    </div>
                @endif

                <div class="rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 space-y-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-800 dark:text-zinc-100">Opens</p>
                        <a href="{{ $url }}" target="_blank" class="font-mono text-sm text-indigo-600 dark:text-indigo-400 underline break-all">{{ $url }}</a>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" onclick="window.print()"
                            class="px-4 py-2 rounded-lg bg-black dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-gray-800 dark:hover:bg-zinc-200">
                            Print card
                        </button>
                        <a href="{{ route('menu.qr.svg') }}"
                            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-zinc-600 text-sm font-semibold text-gray-700 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-800">
                            Download QR (SVG)
                        </a>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-zinc-400">
                        The SVG stays sharp at any size, so it works for a print shop, table tents or room cards.
                        Test-scan a printed copy before laminating.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
