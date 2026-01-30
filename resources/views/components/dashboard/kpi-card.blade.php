<div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
    <div class="text-sm text-neutral-500 mb-1">
        {{ $label }}
    </div>

    <div class="text-2xl font-semibold">
        {{ $value }}
    </div>

    @if (!empty($subtext))
        <div class="text-xs text-neutral-400 mt-1">
            {{ $subtext }}
        </div>
    @endif
</div>
