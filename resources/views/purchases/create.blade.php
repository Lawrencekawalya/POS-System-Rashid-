{{-- <x-layouts::app title="Record Purchase">

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
                    <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" placeholder="Optional"
                        class="border rounded px-3 py-2 w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Invoice / Reference No</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no') }}"
                        class="border rounded px-3 py-2 w-full">
                </div>
            </div>

            <!-- Items Header -->
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-sm font-semibold uppercase text-gray-600">
                    Purchase Items
                </h2>

                <button type="button" onclick="addRow()" class="px-4 py-2 bg-black text-white rounded text-sm">
                    + Add Item
                </button>
            </div>

            <!-- Items Table -->
            <div>
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
                    <tbody id="items-body"></tbody>
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
                <button type="submit" class="bg-black text-white px-6 py-2 rounded">
                    Save Purchase
                </button>
            </div>

        </form>
    </div>

    <script>
        document.addEventListener('livewire:navigated', function () {

            let index = 0;

            const oldItems = @json(old('items', []));

            const productsOptions = `
        <option value="">Select product</option>
        @foreach ($products as $product)
            <option value="{{ $product->id }}">
                {{ $product->name }}
            </option>
        @endforeach
    `;

            function formatCurrency(number) {
                return new Intl.NumberFormat('en-UG').format(number.toFixed(2));
            }

            function getSelectedProducts() {
                return Array.from(document.querySelectorAll('.product-select'))
                    .map(select => select.value)
                    .filter(val => val !== "");
            }

            window.addRow = function (preset = null) {

                const body = document.getElementById('items-body');
                if (!body) return;

                const selectedProducts = getSelectedProducts();

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
                    value="${preset ? preset.quantity : 1}"
                    class="border rounded px-2 py-1 w-full qty">
            </td>

            <td class="border px-2 py-2">
                <input type="number"
                    name="items[${index}][unit_cost]"
                    min="0"
                    step="0.01"
                    value="${preset ? preset.unit_cost : 0}"
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

                const select = row.querySelector('.product-select');

                const tom = new TomSelect(select, {
                    create: false,
                    sortField: { field: "text", direction: "asc" },
                    // dropdownParent: document.body
                });

                if (preset) {
                    tom.setValue(preset.product_id);
                }

                // Prevent duplicate selection
                tom.on('change', function (value) {
                    const duplicates = getSelectedProducts()
                        .filter(v => v === value);

                    if (duplicates.length > 1) {
                        alert("Product already selected.");
                        tom.clear();
                    }
                });

                index++;
                updateRowNumbers();
                attachEvents();
                calculateTotal();
            };

            function attachEvents() {
                document.querySelectorAll('.qty, .cost').forEach(input => {
                    input.oninput = function () {
                        const row = this.closest('tr');
                        const qty = parseFloat(row.querySelector('.qty').value) || 0;
                        const cost = parseFloat(row.querySelector('.cost').value) || 0;
                        const subtotal = qty * cost;

                        row.querySelector('.subtotal').innerText = formatCurrency(subtotal);
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

                document.querySelectorAll('.qty').forEach((qtyInput, i) => {
                    const row = qtyInput.closest('tr');
                    const qty = parseFloat(row.querySelector('.qty').value) || 0;
                    const cost = parseFloat(row.querySelector('.cost').value) || 0;
                    total += qty * cost;
                });

                document.getElementById('grand-total').innerText = formatCurrency(total);
            }

            function updateRowNumbers() {
                document.querySelectorAll('.row-number').forEach((cell, i) => {
                    cell.innerText = i + 1;
                });
            }

            // Load old input if validation failed
            if (oldItems.length > 0) {
                oldItems.forEach(item => addRow(item));
            } else {
                addRow();
            }
        });
    </script>

</x-layouts::app>--}}

<x-layouts::app title="Record Purchase">
    <div class="min-h-screen bg-gray-50 pb-12">
        <div class="bg-white border-b border-gray-200 mb-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Record Stock In</h1>
                        <p class="text-sm text-gray-500">Add new inventory items to your stock levels.</p>
                    </div>
                    <a href="{{ route('purchases.index') }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        &larr; Back to History
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('purchases.store') }}">
                @csrf

                {{-- Feedback Messages --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 text-red-700">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 font-medium">{{ $errors->first() }}</div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white shadow-sm border border-gray-200 rounded-xl ">
                            <div
                                class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Purchase Items</h2>
                                <button type="button" onclick="addRow()"
                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Row
                                </button>
                            </div>

                            <div>
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">#
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">
                                                Product</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-24">
                                                Qty</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-32">
                                                Unit Cost</th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase w-32">
                                                Subtotal</th>
                                            <th class="px-4 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-body" class="divide-y divide-gray-100 bg-white">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-6">
                            <h3 class="text-sm font-bold text-gray-900 mb-4 uppercase">Purchase Details</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Supplier
                                        Name</label>
                                    <input type="text" name="supplier_name" value="{{ old('supplier_name') }}"
                                        placeholder="e.g. Nile Bottling"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 px-3 py-2 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reference /
                                        Invoice #</label>
                                    <input type="text" name="reference_no" value="{{ old('reference_no') }}"
                                        placeholder="INV-00123"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 px-3 py-2 focus:border-indigo-500 sm:text-sm font-mono">
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-900 rounded-xl p-6 shadow-lg text-white">
                            <div class="text-indigo-200 text-xs font-bold uppercase mb-1">Total Purchase Value</div>
                            <div class="text-3xl font-black">
                                <span class="text-lg font-normal">UGX</span> <span id="grand-total">0.00</span>
                            </div>
                            <button type="submit"
                                class="w-full mt-6 bg-white text-indigo-900 hover:bg-indigo-50 font-bold py-3 px-4 rounded-lg shadow transition transform active:scale-95">
                                Save Purchase
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:navigated', function () {
            let index = 0;
            const body = document.getElementById('items-body');
            const oldItems = @json(old('items', []));

            const productsOptions = `
        <option value="">Search product...</option>
        @foreach ($products as $product)
            <option value="{{ $product->id }}" data-cost="{{ $product->cost_price }}">
                {{ $product->name }}
            </option>
        @endforeach
    `;

            function formatCurrency(number) {
                return new Intl.NumberFormat('en-UG').format(number.toFixed(0));
            }

            window.addRow = function (preset = null) {
                const row = document.createElement('tr');
                row.className = "hover:bg-gray-50 transition-colors";
                row.innerHTML = `
            <td class="px-4 py-3 text-sm text-gray-400 font-mono row-number"></td>
            <td class="px-4 py-3">
                <select name="items[${index}][product_id]" required
                    class="product-select block w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                    ${productsOptions}
                </select>
            </td>
            <td class="px-4 py-3">
                <input type="number" name="items[${index}][quantity]" min="1" 
                    value="${preset ? preset.quantity : 1}"
                    class="qty block w-full rounded-xl border border-gray-300 px-2 py-1 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </td>
            <td class="px-4 py-3">
                <input type="number" name="items[${index}][unit_cost]" min="0" step="0.01" 
                    value="${preset ? preset.unit_cost : 0}"
                    class="cost block w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
            </td>
            <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 subtotal">0.00</td>
            <td class="px-4 py-3 text-center">
                <button type="button" class="remove-btn text-gray-400 hover:text-red-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </td>
        `;

                body.appendChild(row);

                const select = row.querySelector('.product-select');
                const tom = new TomSelect(select, {
                    create: false,
                    placeholder: "Select Product",
                    onItemAdd: function (value) {
                        // AUTO-INPUT COST LOGIC
                        const option = this.options[value];
                        const costPrice = option.$option.dataset.cost;
                        const costInput = row.querySelector('.cost');

                        if (costPrice) {
                            costInput.value = costPrice;
                            // Trigger input event to refresh subtotal
                            costInput.dispatchEvent(new Event('input'));
                        }
                    }
                });

                if (preset) {
                    tom.setValue(preset.product_id);
                }

                index++;
                updateRowNumbers();
                attachEvents(row);
                calculateTotal();
            };

            function attachEvents(row) {
                const qtyInput = row.querySelector('.qty');
                const costInput = row.querySelector('.cost');

                [qtyInput, costInput].forEach(input => {
                    input.oninput = function () {
                        const qty = parseFloat(qtyInput.value) || 0;
                        const cost = parseFloat(costInput.value) || 0;
                        const subtotal = qty * cost;
                        row.querySelector('.subtotal').innerText = formatCurrency(subtotal);
                        calculateTotal();
                    };
                });

                row.querySelector('.remove-btn').onclick = function () {
                    row.remove();
                    updateRowNumbers();
                    calculateTotal();
                };
            }

            function calculateTotal() {
                let total = 0;
                document.querySelectorAll('.qty').forEach((qtyInput) => {
                    const row = qtyInput.closest('tr');
                    const qty = parseFloat(qtyInput.value) || 0;
                    const cost = parseFloat(row.querySelector('.cost').value) || 0;
                    total += qty * cost;
                });
                document.getElementById('grand-total').innerText = formatCurrency(total);
            }

            function updateRowNumbers() {
                document.querySelectorAll('.row-number').forEach((cell, i) => {
                    cell.innerText = (i + 1).toString().padStart(2, '0');
                });
            }

            if (oldItems.length > 0) {
                oldItems.forEach(item => addRow(item));
            } else {
                addRow();
            }
        });
    </script>
</x-layouts::app>