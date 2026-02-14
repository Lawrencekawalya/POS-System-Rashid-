<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Login' }} | Scent of Elegance</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700|montserrat:300,400,500,600" rel="stylesheet" />

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(145deg, #0b0c1e 0%, #1a1b2f 100%);
            color: white;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        /* Luxury Gold Glows */
        .auth-glow {
            position: absolute;
            width: 60vw;
            height: 60vw;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.15), transparent 70%);
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
            z-index: 0;
        }

        /* Animated Particles (simplified version of your home page) */
        .particle {
            position: absolute;
            background: rgba(212, 175, 55, 0.3);
            border-radius: 50%;
            animation: float-up 20s infinite linear;
        }

        @keyframes float-up {
            from { transform: translateY(100vh); opacity: 0; }
            to { transform: translateY(-10vh); opacity: 1; }
        }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <!-- <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900"> -->
    <body class="antialiased">
        <div class="auth-glow" style="top: -10%; right: -10%;"></div>
        <div class="auth-glow" style="bottom: -10%; left: -10%;"></div>
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="flex h-9 w-9 mb-1 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>

