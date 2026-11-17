@props(['current' => null, 'standIn' => null])

{{-- Photo shown on the guest menu card. The form must use enctype="multipart/form-data". --}}
<div x-data="{ preview: @js($current), removed: false }">
    <label for="image" class="block text-sm font-bold text-gray-700 dark:text-zinc-200">
        Photo <span class="font-normal text-gray-500 dark:text-zinc-400">(optional)</span>
    </label>

    <div class="mt-2 flex items-start gap-4">
        <div class="flex h-24 w-32 flex-none items-center justify-center overflow-hidden rounded-lg border border-dashed border-gray-300 bg-white dark:border-zinc-600 dark:bg-zinc-900">
            <template x-if="preview && !removed">
                <img :src="preview" alt="" class="h-full w-full object-cover">
            </template>
            <template x-if="!preview || removed">
                <svg class="h-8 w-8 text-gray-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.6-4.6a2 2 0 012.8 0L16 16m-2-2l1.6-1.6a2 2 0 012.8 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </template>
        </div>

        <div class="min-w-0 flex-1 space-y-2">
            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp"
                x-on:change="if ($event.target.files[0]) { preview = URL.createObjectURL($event.target.files[0]); removed = false }"
                class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-zinc-300 dark:file:bg-indigo-950/40 dark:file:text-indigo-300">
            <p class="text-xs text-gray-500 dark:text-zinc-400">JPG, PNG or WebP up to 10 MB. Photos are resized automatically for the menu.</p>

            @if ($standIn)
                <p class="rounded-md bg-amber-50 px-2 py-1.5 text-xs text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                    This is a stand-in photo ({{ $standIn }}). Upload your own photo of the real dish to replace it.
                </p>
            @endif

            @if ($current)
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-zinc-200">
                    <input type="checkbox" name="remove_image" value="1" x-model="removed" class="rounded border-gray-300 dark:border-zinc-600 text-red-600 focus:ring-red-500">
                    Remove photo
                </label>
            @endif

            @error('image') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
