<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $title }} Menu</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fredoka:600,700|instrument-sans:400,500,600" rel="stylesheet">
    <style>
        :root {
            --paper: #fbf6ee;
            --card: #fffdf9;
            --ink: #2b1d16;
            --muted: #6f5a4d;
            --note: #a5402a;
            --maroon: #7b2433;
            --orange: #e8742a;
            --amber: #f3a53c;
            --rule: #d9c7b5;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            line-height: 1.45;
        }

        .banner {
            position: relative;
            padding: 40px 16px 72px;
            text-align: center;
            color: #fff;
            background:
                repeating-linear-gradient(45deg, rgb(255 255 255 / 0.06) 0 12px, transparent 12px 24px),
                linear-gradient(120deg, var(--orange), var(--amber));
            clip-path: ellipse(140% 100% at 50% 0%);
        }

        .banner h1 {
            margin: 0;
            font-family: 'Fredoka', 'Instrument Sans', sans-serif;
            font-size: clamp(2.2rem, 8vw, 3.4rem);
            font-weight: 700;
            line-height: 1.05;
            text-shadow: 0 2px 0 rgb(123 36 51 / 0.35);
        }

        .banner p {
            margin: 10px 0 0;
            font-size: 0.95rem;
            opacity: 0.95;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: rgb(251 246 238 / 0.94);
            backdrop-filter: blur(6px);
            border-bottom: 1px solid var(--rule);
        }

        .toolbar-inner {
            max-width: 760px;
            margin: 0 auto;
            padding: 10px 16px;
        }

        .search {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--rule);
            border-radius: 999px;
            background: var(--card);
            color: var(--ink);
            font: inherit;
        }

        .search:focus {
            outline: 2px solid var(--orange);
            outline-offset: 1px;
        }

        .nav {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .nav::-webkit-scrollbar { display: none; }

        @media (min-width: 640px) {
            .nav { flex-wrap: wrap; overflow: visible; }
        }

        .nav a {
            flex: none;
            padding: 6px 14px;
            border-radius: 999px;
            background: var(--maroon);
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }

        .nav a:hover,
        .nav a:focus-visible { background: var(--orange); }

        main {
            max-width: 760px;
            margin: 0 auto;
            padding: 8px 16px 48px;
        }

        .group {
            scroll-margin-top: 170px;
            padding: 24px 0 8px;
        }

        .group > h2 {
            margin: 0 0 6px;
            font-family: 'Fredoka', 'Instrument Sans', sans-serif;
            font-size: 1.9rem;
            color: var(--maroon);
            text-align: center;
        }

        .group > .note {
            margin: 0 auto 8px;
            max-width: 620px;
            text-align: center;
        }

        .section {
            margin-top: 22px;
            padding-right: 18px;
            border-right: 6px solid var(--amber);
            box-shadow: inset -2px 0 0 0 var(--maroon);
        }

        .section h3 {
            display: inline-block;
            margin: 0 0 6px;
            padding: 4px 18px;
            background: var(--maroon);
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .note {
            margin: 0 0 8px;
            color: var(--note);
            font-size: 0.9rem;
        }

        .items {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .item { padding: 7px 0; }

        .line {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .name { font-weight: 500; }

        .dots {
            flex: 1;
            min-width: 24px;
            border-bottom: 2px dotted var(--rule);
            transform: translateY(-4px);
        }

        .price {
            flex: none;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .item .note { margin: 2px 0 0; font-size: 0.85rem; }

        .empty {
            display: none;
            padding: 48px 0;
            text-align: center;
            color: var(--muted);
        }

        footer {
            padding: 24px 16px 40px;
            text-align: center;
            color: var(--muted);
            font-size: 0.85rem;
        }

        [hidden] { display: none !important; }

        @media print {
            .toolbar { display: none; }
            .banner { padding: 16px; clip-path: none; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .section { break-inside: avoid; }
            .section h3 { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .group { break-before: page; }
            .group:first-of-type { break-before: auto; }
        }
    </style>
</head>
<body>
    <header class="banner">
        <h1>{{ $title }}<br>Menu</h1>
        <p>Prices in Uganda Shillings</p>
    </header>

    @if ($groups !== [])
        <div class="toolbar">
            <div class="toolbar-inner">
                <input id="menu-search" class="search" type="search" placeholder="Search the menu…" autocomplete="off" aria-label="Search the menu">
                <nav class="nav" aria-label="Menu sections">
                    @foreach ($groups as $group)
                        <a href="#{{ $group['slug'] }}">{{ $group['name'] }}</a>
                    @endforeach
                </nav>
            </div>
        </div>
    @endif

    <main>
        @forelse ($groups as $group)
            <section class="group" id="{{ $group['slug'] }}" data-group>
                <h2>{{ $group['name'] }}</h2>
                @if ($group['note'])
                    <p class="note">{{ $group['note'] }}</p>
                @endif

                @foreach ($group['sections'] as $section)
                    <div class="section" data-section>
                        <h3>{{ $section['name'] }}</h3>
                        @if ($section['note'])
                            <p class="note">{{ $section['note'] }}</p>
                        @endif

                        <ul class="items">
                            @foreach ($section['items'] as $item)
                                <li class="item" data-search="{{ Str::lower($item['name'].' '.$item['description']) }}">
                                    <div class="line">
                                        <span class="name">{{ $item['name'] }}</span>
                                        <span class="dots" aria-hidden="true"></span>
                                        <span class="price">{{ number_format($item['price']) }}/=</span>
                                    </div>
                                    @if ($item['description'])
                                        <p class="note">{{ $item['description'] }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </section>
        @empty
            <p class="empty" style="display:block">The menu is being updated. Please ask our staff.</p>
        @endforelse

        <p class="empty" id="no-results">No dishes match your search.</p>
    </main>

    <footer>{{ $title }} &middot; Please let us know about any allergies.</footer>

    <script>
        (() => {
            const input = document.getElementById('menu-search');
            if (!input) return;

            const groups = [...document.querySelectorAll('[data-group]')];
            const noResults = document.getElementById('no-results');

            input.addEventListener('input', () => {
                const query = input.value.trim().toLowerCase();
                let anyVisible = false;

                for (const group of groups) {
                    let groupVisible = false;

                    for (const section of group.querySelectorAll('[data-section]')) {
                        let sectionVisible = false;

                        for (const item of section.querySelectorAll('[data-search]')) {
                            const match = query === '' || item.dataset.search.includes(query);
                            item.hidden = !match;
                            sectionVisible ||= match;
                        }

                        section.hidden = !sectionVisible;
                        groupVisible ||= sectionVisible;
                    }

                    group.hidden = !groupVisible;
                    anyVisible ||= groupVisible;
                }

                noResults.style.display = anyVisible ? 'none' : 'block';
            });
        })();
    </script>
</body>
</html>
