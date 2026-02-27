<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <div class="pointer-events-none">
                <x-app-logo :sidebar="true" wire:navigate />
            </div>
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group :heading="__('Platform')" class="grid">

                {{-- Sales: visible to everyone --}}
                @if (auth()->user()->isCashier())
                    <flux:sidebar.item icon="cube" :href="route('pos.index')" :current="request()->routeIs('pos.*')"
                        wire:navigate>
                        {{ __('Sales') }}
                    </flux:sidebar.item>
                    {{-- Expenses: added for cashier --}}
                    <flux:sidebar.group label="{{ __('Expenses') }}" class="mt-4">
                        <flux:sidebar.item icon="plus-circle" :href="route('expenses.create')"
                            :current="request()->routeIs('expenses.create')" wire:navigate>
                            {{ __('New Expense') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="document-text" :href="route('expenses.index')"
                            :current="request()->routeIs('expenses.index') && !request()->routeIs('expenses.create')"
                            wire:navigate>
                            {{ __('Expense Log') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif

                {{-- Admin-only navigation --}}
                @if (auth()->user()->isAdmin())
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                        wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="cube" :href="route('products.index')"
                        :current="request()->routeIs('products.*')" wire:navigate>
                        {{ __('Products') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="document" :href="route('sales.index')" :current="request()->routeIs('sales.*')"
                        wire:navigate>
                        {{ __('Sales History') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="chart-bar" :href="route('reports.z')"
                        :current="request()->routeIs('reports.z')" wire:navigate>
                        {{ __('Z Report') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="banknotes" :href="route('cash.reconcile.index')"
                        :current="request()->routeIs('cash.reconcile.index')" wire:navigate>
                        {{ __('Cash Reconciliation') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="document-text" :href="route('expenses.index')" :current="request()->routeIs('expenses.index')" wire:navigate>
        {{ __('All Expenses') }}
    </flux:sidebar.item>

                    <flux:sidebar.item icon="truck" :href="route('purchases.index')"
                        :current="request()->routeIs('purchases.*')" wire:navigate>
                        {{ __('Purchase History') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="cloud" :href="route('reports.inventory')"
                        :current="request()->routeIs('reports.inventory')" wire:navigate>
                        {{ __('Inventory Valuation') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="trend" :href="route('reports.profitability')"
                        :current="request()->routeIs('reports.profitability')" wire:navigate>
                        {{ __('Profitability') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="refresh-ccw" :href="route('reports.turnover')"
                        :current="request()->routeIs('reports.turnover')" wire:navigate>
                        {{ __('Stock Turnover') }}
                    </flux:sidebar.item>

                    {{-- User management --}}
                    <flux:sidebar.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.*')"
                        wire:navigate>
                        {{ __('Users') }}
                    </flux:sidebar.item>
                @endif
            </flux:sidebar.group>

        </flux:sidebar.nav>
        {{-- 🔔 Low stock alerts --}}
        @include('partials.low-stock-widget')
        <flux:spacer />

        <flux:sidebar.nav>
            {{-- <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                {{ __('Repository') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                {{ __('Documentation') }}
            </flux:sidebar.item> --}}
        </flux:sidebar.nav>

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>


    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
