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

        /* All section buttons stay visible: they wrap onto a second row instead of scrolling sideways. */
        .nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 6px;
            margin-top: 10px;
        }

        .nav a {
            padding: 6px 12px;
            border-radius: 999px;
            background: var(--maroon);
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.3;
            text-decoration: none;
            white-space: nowrap;
        }

        .nav a:hover,
        .nav a:focus-visible,
        .nav a.is-active { background: var(--orange); }

        /* Very narrow phones: tighter buttons so they still fit in two rows. */
        @media (max-width: 359px) {
            .toolbar-inner { padding: 8px 12px; }
            .search { padding: 8px 12px; }
            .nav { gap: 5px; margin-top: 8px; }
            .nav a { padding: 6px 9px; font-size: 0.75rem; }
        }

        @media (min-width: 640px) {
            .nav { gap: 8px; }
            .nav a { padding: 6px 14px; font-size: 0.85rem; }
        }

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
            margin-top: 26px;
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

        .cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            list-style: none;
            margin: 12px 0 0;
            padding: 0;
        }

        @media (min-width: 640px) {
            .cards { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        }

        .card {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--card);
            border: 1px solid var(--rule);
            border-radius: 14px;
            box-shadow: 0 1px 2px rgb(43 29 22 / 0.06);
        }

        .card__media {
            aspect-ratio: 4 / 3;
            overflow: hidden; /* keeps portrait photos (bottles) cropped to 4:3 */
            background: var(--rule);
        }

        .card__media img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card__placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: rgb(255 255 255 / 0.92);
            background:
                repeating-linear-gradient(45deg, rgb(255 255 255 / 0.07) 0 10px, transparent 10px 20px),
                linear-gradient(135deg, var(--amber), var(--orange));
        }

        .card:nth-child(3n + 2) .card__placeholder {
            background:
                repeating-linear-gradient(45deg, rgb(255 255 255 / 0.06) 0 10px, transparent 10px 20px),
                linear-gradient(135deg, #a33a46, var(--maroon));
        }

        .card:nth-child(3n) .card__placeholder {
            background:
                repeating-linear-gradient(45deg, rgb(255 255 255 / 0.07) 0 10px, transparent 10px 20px),
                linear-gradient(135deg, var(--orange), #c95a1f);
        }

        .card__placeholder svg { width: 38%; max-width: 64px; height: auto; }

        .card__body {
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 4px;
            padding: 10px 12px 12px;
        }

        .card__name {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 600;
            line-height: 1.25;
        }

        .card__desc {
            display: -webkit-box;
            margin: 0;
            overflow: hidden;
            color: var(--note);
            font-size: 0.8rem;
            line-height: 1.35;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .card__credit {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 0.65rem;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .card__credit a { color: inherit; }

        .banner__note {
            font-size: 0.8rem !important;
            opacity: 0.85 !important;
        }

        .card__price {
            margin: auto 0 0;
            padding-top: 6px;
            color: var(--maroon);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

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

        /* "More" under descriptions that are cut off; tapping it shows the rest. */
        .card__more {
            align-self: flex-start;
            margin: 0;
            padding: 2px 0;
            border: 0;
            background: none;
            color: var(--maroon);
            font: inherit;
            font-size: 0.78rem;
            font-weight: 600;
            text-decoration: underline;
            cursor: pointer;
        }

        .card.is-open .card__desc { display: block; -webkit-line-clamp: unset; overflow: visible; }

        .to-top {
            position: fixed;
            right: 16px;
            bottom: calc(16px + env(safe-area-inset-bottom));
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border: 0;
            border-radius: 999px;
            background: var(--maroon);
            color: #fff;
            box-shadow: 0 4px 12px rgb(43 29 22 / 0.25);
            cursor: pointer;
            opacity: 0;
            transform: translateY(12px);
            pointer-events: none;
            transition: opacity 0.2s, transform 0.2s;
        }

        .to-top.is-visible { opacity: 1; transform: none; pointer-events: auto; }
        .to-top:hover, .to-top:focus-visible { background: var(--orange); }
        .to-top svg { width: 22px; height: 22px; }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .to-top { transition: none; }
        }

        @media print {
            .toolbar, .to-top, .card__more { display: none; }
            .card__desc { display: block; -webkit-line-clamp: unset; overflow: visible; }
            .banner { padding: 16px; clip-path: none; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .section { break-inside: avoid; }
            .card { break-inside: avoid; }
            .card__media:has(.card__placeholder) { display: none; }
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
        @if ($hasStandInPhotos)
            <p class="banner__note">Some photos are for illustration only.</p>
        @endif
    </header>

    @if ($groups !== [])
        <div class="toolbar">
            <div class="toolbar-inner">
                <input id="menu-search" class="search" type="search" placeholder="Search the menu…" autocomplete="off" aria-label="Search the menu">
                <nav class="nav" aria-label="Menu sections">
                    @foreach ($groups as $group)
                        <a href="#{{ $group['slug'] }}" title="{{ $group['name'] }}">{{ $group['nav'] }}</a>
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

                        <ul class="cards">
                            @foreach ($section['items'] as $item)
                                <li class="card" data-search="{{ Str::lower($item['name'].' '.$item['description']) }}">
                                    <div class="card__media">
                                        @if ($item['image'])
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" width="800" height="600" loading="lazy" decoding="async">
                                        @else
                                            <div class="card__placeholder" aria-hidden="true">
                                                @if ($section['icon'] === 'hot')
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h12v5a6 6 0 0 1-6 6v0a6 6 0 0 1-6-6V8Z"/><path d="M17 10h1.5a2.5 2.5 0 0 1 0 5H17"/><path d="M8 2.5c0 1.5 1 1.5 1 3M12 2.5c0 1.5 1 1.5 1 3"/></svg>
                                                @elseif ($section['icon'] === 'cold')
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h12l-1.5 14a1.5 1.5 0 0 1-1.5 1.3H9a1.5 1.5 0 0 1-1.5-1.3L6 6Z"/><path d="M6.6 11h10.8"/><path d="M14 6l2-4h2.5"/></svg>
                                                @else
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="13" r="6"/><circle cx="12" cy="13" r="3"/><path d="M3 3v6a2 2 0 0 0 2 2M3 3v18M5 3v6M21 3c-1.5 0-2.5 2-2.5 5s1 4 2.5 4v9"/></svg>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card__body">
                                        <h4 class="card__name">{{ $item['name'] }}</h4>
                                        @if ($item['description'])
                                            <p class="card__desc">{{ $item['description'] }}</p>
                                        @endif
                                        <p class="card__price">{{ number_format($item['price']) }}/=</p>
                                        @if ($item['credit'])
                                            <p class="card__credit">
                                                @if ($item['credit_url'])
                                                    <a href="{{ $item['credit_url'] }}" target="_blank" rel="noopener nofollow">{{ $item['credit'] }}</a>
                                                @else
                                                    {{ $item['credit'] }}
                                                @endif
                                            </p>
                                        @endif
                                    </div>
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

    <button type="button" class="to-top" id="to-top" aria-label="Back to top" tabindex="-1">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
    </button>

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
                updateMoreButtons();
                onScroll();
            });

            // Highlight the section button for the part of the menu on screen.
            const toolbar = document.querySelector('.toolbar');
            const links = new Map([...document.querySelectorAll('.nav a')].map(a => [a.getAttribute('href').slice(1), a]));
            const toTop = document.getElementById('to-top');
            const smooth = !matchMedia('(prefers-reduced-motion: reduce)').matches;
            let ticking = false;

            function onScroll() {
                ticking = false;
                // A section is "current" once its heading passes a third of the way down the visible area.
                const barBottom = toolbar.getBoundingClientRect().bottom;
                const line = barBottom + (window.innerHeight - barBottom) / 3;
                let current = null;

                for (const group of groups) {
                    if (!group.hidden && group.getBoundingClientRect().top <= line) {
                        current = group.id;
                    }
                }

                for (const [id, link] of links) {
                    const active = id === current;
                    link.classList.toggle('is-active', active);
                    active ? link.setAttribute('aria-current', 'true') : link.removeAttribute('aria-current');
                }

                const showTop = window.scrollY > window.innerHeight * 1.5;
                toTop.classList.toggle('is-visible', showTop);
                toTop.tabIndex = showTop ? 0 : -1;
            }

            window.addEventListener('scroll', () => {
                if (!ticking) {
                    ticking = true;
                    requestAnimationFrame(onScroll);
                }
            }, { passive: true });

            toTop.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: smooth ? 'smooth' : 'auto' });
                input.focus({ preventScroll: true });
            });

            // Descriptions are cut to three lines; add "More" where text is hidden.
            function updateMoreButtons() {
                for (const card of document.querySelectorAll('.card')) {
                    const desc = card.querySelector('.card__desc');
                    let button = card.querySelector('.card__more');

                    if (!desc || card.hidden || card.classList.contains('is-open')) {
                        continue;
                    }

                    const clipped = desc.scrollHeight > desc.clientHeight + 1;

                    if (clipped && !button) {
                        button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'card__more';
                        button.textContent = 'More';
                        button.setAttribute('aria-expanded', 'false');
                        button.addEventListener('click', () => {
                            const open = card.classList.toggle('is-open');
                            button.textContent = open ? 'Less' : 'More';
                            button.setAttribute('aria-expanded', String(open));
                        });
                        desc.after(button);
                    } else if (!clipped && button) {
                        button.remove();
                    }
                }
            }

            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(updateMoreButtons, 150);
            });

            updateMoreButtons();
            document.fonts?.ready.then(updateMoreButtons);
            onScroll();
        })();
    </script>
</body>
</html>
