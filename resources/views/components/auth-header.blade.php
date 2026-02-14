<!-- @props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading size="xl">{{ $title }}</flux:heading>
    <flux:subheading>{{ $description }}</flux:subheading>
</div> -->


@props(['title', 'description'])

<div class="flex flex-col gap-2 text-center mb-8">
    <h1 class="font-['Cormorant_Garamond'] text-4xl font-bold tracking-[4px] text-transparent bg-clip-text bg-gradient-to-b from-white to-[#d4af37] uppercase">
        {{ $title }}
    </h1>
    @if ($description)
        <p class="text-[10px] tracking-[3px] text-[#d4af37]/60 uppercase font-light">
            {{ $description }}
        </p>
    @endif
    <div class="w-12 h-[1px] bg-gradient-to-r from-transparent via-[#d4af37] to-transparent mx-auto mt-2"></div>
</div>