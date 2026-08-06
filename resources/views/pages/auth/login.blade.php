<x-layouts::auth>
    <div class="flex flex-col gap-6 p-8 rounded-2xl border border-white/10 bg-[#14121c]/75 backdrop-blur-xl shadow-2xl relative overflow-hidden">
        
        <div class="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-[#d4af37]/80 to-transparent"></div>

        <div class="text-center">
            <h1 class="font-['Cormorant_Garamond'] text-3xl font-bold tracking-widest text-transparent bg-clip-text bg-gradient-to-b from-white to-[#d4af37] uppercase">
                Welcome Back
            </h1>
            {{--<p class="text-xs tracking-[3px] text-[#d4af37]/70 uppercase mt-2">The Scent of Elegance</p>--}}
        </div>

        <x-auth-session-status class="text-center text-sm" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input 
                name="email" 
                :label="__('Email Address')" 
                :value="old('email')" 
                type="email" 
                required
                autofocus 
                class="!bg-black/20 !border-white/10 focus:!border-[#d4af37]/50" 
            />

            <div class="relative">
                <flux:input 
                    name="password" 
                    :label="__('Password')" 
                    type="password" 
                    required
                    viewable 
                    class="!bg-black/20 !border-white/10 focus:!border-[#d4af37]/50"
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-xs end-0 text-[#d4af37]/80 hover:text-[#d4af37]" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot password?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" class="text-zinc-400" />

            <div class="flex items-center justify-end mt-2">
                <flux:button 
                    type="submit" 
                    class="w-full !bg-[#d4af37] !text-[#0b0c1e] hover:!bg-[#b8962d] transition-transform active:scale-95 font-bold tracking-widest uppercase text-xs h-12"
                >
                    {{ __('Sign In') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
