<x-layouts::app title="Record Purchase">

    <h1 class="text-xl font-semibold mb-4">Record Purchase (Stock In)</h1>

    @if (session('success'))
        <div class="mb-4 text-green-600">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 text-red-600">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('purchases.store') }}">
        @csrf

        <div class="grid grid-cols-2 gap-4 mb-4">
            <input type="text" name="supplier_name" placeholder="Supplier name (optional)"
                class="border px-3 py-2 w-full">

            <input type="text" name="reference_no" placeholder="Invoice / Reference number"
                class="border px-3 py-2 w-full">
        </div>

        <table class="w-full border mb-4 text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1">Product</th>
                    <th class="border px-2 py-1">Qty</th>
                    <th class="border px-2 py-1">Unit Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $index => $product)
                    <tr>
                        <td class="border px-2 py-1">
                            {{ $product->name }}
                            <input type="hidden" name="items[{{ $index }}][product_id]"
                                value="{{ $product->id }}">
                        </td>
                        <td class="border px-2 py-1">
                            <input type="number" name="items[{{ $index }}][quantity]" min="0"
                                class="border px-2 py-1 w-full">
                        </td>
                        <td class="border px-2 py-1">
                            <input type="number" name="items[{{ $index }}][unit_cost]" min="0"
                                step="0.01" class="border px-2 py-1 w-full">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button class="bg-black text-white px-4 py-2">
            Save Purchase
        </button>
    </form>

</x-layouts::app>
