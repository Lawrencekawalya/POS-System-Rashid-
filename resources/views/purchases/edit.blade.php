<x-layouts::app title="Edit Purchase">
    <div class="min-h-screen bg-gray-50 pb-12">
        <div class="bg-white border-b border-gray-200 mb-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Stock In</h1>
                        <p class="text-sm text-gray-500">
                            Update purchase #{{ $purchase->id }}
                        </p>
                    </div>
                    <a href="{{ route('purchases.index') }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        &larr; Back to History
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('purchases.update', $purchase) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    {{-- LEFT SIDE (ITEMS) --}}
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white shadow-sm border border-gray-200 rounded-xl">
                            <div
                                class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">
                                    Purchase Items
                                </h2>
                                <button type="button" onclick="addRow()"
                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition">
                                    Add Row
                                </button>
                            </div>

                            <div class="overflow-x-auto">
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
                                        {{-- Rows injected by JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT SIDE (DETAILS) --}}
                    <div class="space-y-6">
                        <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-6">
                            <h3 class="text-sm font-bold text-gray-900 mb-4 uppercase">Purchase Details</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Supplier
                                        Name</label>
                                    <input type="text" name="supplier_name"
                                        value="{{ old('supplier_name', $purchase->supplier_name) }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reference
                                        #</label>
                                    <input type="text" name="reference_no"
                                        value="{{ old('reference_no', $purchase->reference_no) }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 px-3 py-2">
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-900 rounded-xl p-6 shadow-lg text-white">
                            <div class="text-indigo-200 text-xs font-bold uppercase mb-1">Total Purchase Value</div>
                            <div class="text-3xl font-black">
                                UGX <span id="grand-total">0</span>
                            </div>
                            <button type="submit"
                                class="w-full mt-6 bg-white text-indigo-900 hover:bg-indigo-50 font-bold py-3 px-4 rounded-lg transition shadow-md">
                                Update Purchase
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        document.addEventListener('livewire:navigated', function () {
            let index = 0;
            const body = document.getElementById('items-body');

            // Data prepared by the controller
            const existingItems = @json($existingItems);

            const productsOptions = `
                <option value="">Search product...</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" data-cost="{{ $product->cost_price }}">
                        {{ $product->name }}
                    </option>
                @endforeach
            `;

            function formatCurrency(number) {
                return new Intl.NumberFormat('en-UG').format(Math.round(number));
            }

            window.addRow = function (preset = null) {
                // 1. Calculate the starting subtotal for the row
                const initialQty = preset ? preset.quantity : 1;
                const initialCost = preset ? preset.unit_cost : 0;
                const initialSubtotal = formatCurrency(initialQty * initialCost);

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
                            value="${initialQty}"
                            class="qty block w-full rounded-lg border-gray-300 px-2 py-1 text-sm focus:ring-indigo-500">
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" name="items[${index}][unit_cost]" min="0" step="0.01" 
                            value="${initialCost}"
                            class="cost block w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                    </td>
                    <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 subtotal">${initialSubtotal}</td>
                    <td class="px-4 py-3 text-center">
                        <button type="button" class="remove-btn text-gray-400 hover:text-red-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                `;

                body.appendChild(row);

                // 2. Initialize TomSelect
                const selectElement = row.querySelector('.product-select');
                if (typeof TomSelect !== "undefined") {
                    const tom = new TomSelect(selectElement, {
                        create: false,
                        placeholder: "Select Product",
                        onItemAdd: function (value) {
                            const option = this.options[value];
                            const costPrice = option.$option.dataset.cost;
                            const costInput = row.querySelector('.cost');

                            if (costPrice && !preset) {
                                costInput.value = costPrice;
                                costInput.dispatchEvent(new Event('input'));
                            }
                        }
                    });

                    if (preset) {
                        tom.setValue(preset.product_id);
                    }
                }

                index++;
                attachEvents(row);
                updateRowNumbers();
                calculateTotal();
            };

            function attachEvents(row) {
                const qtyInput = row.querySelector('.qty');
                const costInput = row.querySelector('.cost');

                [qtyInput, costInput].forEach(input => {
                    input.addEventListener('input', () => {
                        const qty = parseFloat(qtyInput.value) || 0;
                        const cost = parseFloat(costInput.value) || 0;
                        row.querySelector('.subtotal').innerText = formatCurrency(qty * cost);
                        calculateTotal();
                    });
                });

                row.querySelector('.remove-btn').addEventListener('click', () => {
                    row.remove();
                    updateRowNumbers();
                    calculateTotal();
                });
            }

            function calculateTotal() {
                let total = 0;
                document.querySelectorAll('#items-body tr').forEach(tr => {
                    const qty = parseFloat(tr.querySelector('.qty').value) || 0;
                    const cost = parseFloat(tr.querySelector('.cost').value) || 0;
                    total += (qty * cost);
                });
                document.getElementById('grand-total').innerText = formatCurrency(total);
            }

            function updateRowNumbers() {
                document.querySelectorAll('.row-number').forEach((cell, i) => {
                    cell.innerText = (i + 1).toString().padStart(2, '0');
                });
            }

            // Execute Initialization
            if (existingItems && existingItems.length > 0) {
                existingItems.forEach(item => addRow(item));
            } else {
                addRow();
            }
        });
    </script>
</x-layouts::app>
