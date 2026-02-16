<!-- <x-layouts::app title="Record Purchase">

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

</x-layouts::app> -->



<x-layouts::app title="Record Purchase">

    <div class="max-w-6xl mx-auto">

        <h1 class="text-2xl font-semibold mb-6">
            Record Purchase (Stock In)
        </h1>

        @if (session('success'))
            <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('purchases.store') }}" class="bg-white shadow rounded p-6">
            @csrf

            <!-- Supplier & Reference -->
            <div class="grid md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-1">Supplier Name</label>
                    <input type="text"
                        name="supplier_name"
                        placeholder="Optional"
                        class="border rounded px-3 py-2 w-full focus:ring focus:ring-gray-200">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Invoice / Reference No</label>
                    <input type="text"
                        name="reference_no"
                        class="border rounded px-3 py-2 w-full focus:ring focus:ring-gray-200">
                </div>
            </div>

            <!-- Items Header -->
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-sm font-semibold uppercase text-gray-600">
                    Purchase Items
                </h2>

                <button type="button"
                    onclick="addRow()"
                    class="px-4 py-2 bg-black text-white rounded text-sm">
                    + Add Item
                </button>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full border text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="border px-2 py-2 w-10">#</th>
                            <th class="border px-2 py-2">Product</th>
                            <th class="border px-2 py-2 w-24">Qty</th>
                            <th class="border px-2 py-2 w-32">Unit Cost</th>
                            <th class="border px-2 py-2 w-32">Subtotal</th>
                            <th class="border px-2 py-2 w-16"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                    </tbody>
                </table>
            </div>

            <!-- Total -->
            <div class="mt-6 flex justify-end">
                <div class="text-lg font-semibold">
                    Total: UGX <span id="grand-total">0.00</span>
                </div>
            </div>

            <!-- Submit -->
            <div class="mt-6 text-right">
                <button type="submit"
                    class="bg-black text-white px-6 py-2 rounded hover:bg-gray-900">
                    Save Purchase
                </button>
            </div>

        </form>
    </div>
    <script>
    document.addEventListener('livewire:navigated', function () {

        let index = 0;

        const productsOptions = `
            <option value="">Select product</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}">
                    {{ $product->name }}
                </option>
            @endforeach
        `;

        window.addRow = function () {
            const body = document.getElementById('items-body');
            if (!body) return;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="border px-2 py-2 text-center row-number"></td>

                <td class="border px-2 py-2">
                    <select name="items[${index}][product_id]" required
                        class="border rounded px-2 py-1 w-full product-select">
                        ${productsOptions}
                    </select>
                </td>

                <td class="border px-2 py-2">
                    <input type="number"
                        name="items[${index}][quantity]"
                        min="1"
                        value="1"
                        class="border rounded px-2 py-1 w-full qty">
                </td>

                <td class="border px-2 py-2">
                    <input type="number"
                        name="items[${index}][unit_cost]"
                        min="0"
                        step="0.01"
                        value="0"
                        class="border rounded px-2 py-1 w-full cost">
                </td>

                <td class="border px-2 py-2 text-right subtotal">0.00</td>

                <td class="border px-2 py-2 text-center">
                    <button type="button"
                        class="text-red-600 font-bold remove-btn">
                        ✕
                    </button>
                </td>
            `;

            body.appendChild(row);
            new TomSelect(row.querySelector('.product-select'), {
                create: false,
                sortField: { field: "text", direction: "asc" }
            });
            index++;
            updateRowNumbers();
            attachEvents();
        };

        function attachEvents() {
            document.querySelectorAll('.qty, .cost').forEach(input => {
                input.oninput = function () {
                    const row = this.closest('tr');
                    const qty = parseFloat(row.querySelector('.qty').value) || 0;
                    const cost = parseFloat(row.querySelector('.cost').value) || 0;
                    const subtotal = qty * cost;
                    row.querySelector('.subtotal').innerText = subtotal.toFixed(2);
                    calculateTotal();
                };
            });

            document.querySelectorAll('.remove-btn').forEach(btn => {
                btn.onclick = function () {
                    this.closest('tr').remove();
                    updateRowNumbers();
                    calculateTotal();
                };
            });
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal').forEach(cell => {
                total += parseFloat(cell.innerText) || 0;
            });

            document.getElementById('grand-total').innerText = total.toFixed(2);
        }

        function updateRowNumbers() {
            document.querySelectorAll('.row-number').forEach((cell, i) => {
                cell.innerText = i + 1;
            });
        }

        // Initialize first row
        addRow();
    });
    </script>
</x-layouts::app>
