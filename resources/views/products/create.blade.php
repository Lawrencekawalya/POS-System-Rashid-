<x-layouts::app title="Add Product">

    <h1 class="text-xl font-semibold mb-6">
        Add Product
    </h1>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-4 text-red-700">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('products.store') }}" class="max-w-xl space-y-4">
        @csrf

        <div>
            <label class="block mb-1 font-medium">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full rounded border px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Brand</label>
            <input type="text" name="brand" value="{{ old('brand') }}" class="w-full rounded border px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Barcode</label>
            <input type="text" name="barcode" value="{{ old('barcode') }}" required
                class="w-full rounded border px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Unit Type</label>
            <select name="unit_type" required class="w-full rounded border px-3 py-2">
                <option value="">-- Select Unit --</option>
                <option value="pcs" {{ old('unit_type') === 'pcs' ? 'selected' : '' }}>Pieces</option>
                <option value="bottles" {{ old('unit_type') === 'bottles' ? 'selected' : '' }}>Bottles</option>
                <option value="cartons" {{ old('unit_type') === 'cartons' ? 'selected' : '' }}>Cartons</option>
            </select>
        </div>

        <div>
            <label class="block mb-1 font-medium">Cost Price</label>
            <input type="number" name="cost_price" step="0.01" min="0.01" value="{{ old('cost_price') }}"
                required class="w-full rounded border px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Selling Price</label>
            <input type="number" name="selling_price" step="0.01" min="0.01" value="{{ old('selling_price') }}"
                required class="w-full rounded border px-3 py-2">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="font-medium">Active</label>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="rounded bg-black px-4 py-2 text-white">
                Save Product
            </button>

            <a href="{{ route('products.index') }}" class="rounded border px-4 py-2">
                Cancel
            </a>
        </div>
    </form>

</x-layouts::app>
