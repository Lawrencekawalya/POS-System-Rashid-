{{-- Credit: Lucide --}}
@props(['variant' => 'outline'])

@php
    $classes = Flux::classes('shrink-0')->add('[:where(&)]:size-6');
@endphp

<svg {{ $attributes->class($classes) }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
    <polyline points="16 7 22 7 22 13" />
</svg>
