<x-layouts::app title="Record Purchase">
    <div class="min-h-screen bg-gray-50 pb-12">
        {{-- Header Section --}}
        <div class="bg-white border-b border-gray-200 mb-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Record Stock In</h1>
                        <p class="text-sm text-gray-500">Add new inventory items to your stock levels.</p>
                    </div>
                    <a href="{{ route('purchases.index') }}" wire:navigate
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        &larr; Back to History
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form id="purchase-form" method="POST" action="{{ route('purchases.store') }}">
                @csrf

                {{-- Feedback Messages --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 text-red-700">
                        <div class="flex">
                            <div class="ml-3 font-medium">{{ $errors->first() }}</div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {{-- Left Column: Items Table --}}
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white shadow-sm border border-gray-200 rounded-xl">
                            <div
                                class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Purchase Items</h2>
                                <button type="button" onclick="addRow()"
                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />                               
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
                                        {{-- Rows injected by JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Details & Submit --}}
                    <div class="space-y-6">
                        <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-6">
                            <h3 class="text-sm font-bold text-gray-900 mb-4 uppercase">Purchase Details</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Supplier
                                        Name</label>
                                    <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" required
                                        class="block w-full rounded-xl border border-gray-300 px-2 py-1 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reference /
                                        Invoice #</label>
                                    <input type="text" name="reference_no" value="{{ old('reference_no') }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 px-3 py-2 sm:text-sm font-mono">
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-900 rounded-xl p-6 shadow-lg text-white">
                            <div class="text-indigo-200 text-xs font-bold uppercase mb-1">Total Purchase Value</div>
                            <div class="text-3xl font-black">
                                <span class="text-lg font-normal">UGX</span> <span id="grand-total">0.00</span>
                            </div>

                            <button type="submit" id="save-btn"
                                class="w-full mt-6 bg-white text-indigo-900 hover:bg-indigo-50 font-bold py-3 px-4 rounded-lg shadow transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span id="btn-text">Save Purchase</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Assets --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <script>
        document.addEventListener('livewire:navigated', function () {
            let index = 0;
            const body = document.getElementById('items-body');
            const form = document.getElementById('purchase-form');
            const saveBtn = document.getElementById('save-btn');
            const btnText = document.getElementById('btn-text');
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

            // --- FORM LOCKING LOGIC ---
            if (form) {
                form.addEventListener('submit', function () {
                    saveBtn.disabled = true;
                    btnText.innerText = 'Saving...';
                    // Allow small delay so the browser actually sends the POST data
                    setTimeout(() => { saveBtn.style.pointerEvents = 'none'; }, 50);
                });
            }

            // --- ROW MANAGEMENT ---
            window.addRow = function (preset = null) {
                const row = document.createElement('tr');
                row.className = "hover:bg-gray-50 transition-colors";
                row.innerHTML = `
                    <td class="px-4 py-3 text-sm text-gray-400 font-mono row-number"></td>
                    <td class="px-4 py-3">
                        <select name="items[${index}][product_id]" required class="product-select">
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
                            class="cost block w-full rounded-lg border-gray-300 text-sm">
                    </td>
                    <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 subtotal">0.00</td>
                    <td class="px-4 py-3 text-center">
                        <button type="button" class="remove-btn text-gray-400 hover:text-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                `;

                body.appendChild(row);

                const select = row.querySelector('.product-select');
                const tom = new TomSelect(select, {
                    create: false,
                    onItemAdd: function (value) {
                        const option = this.options[value];
                        const costPrice = option.$option.dataset.cost;
                        const costInput = row.querySelector('.cost');
                        if (costPrice) {
                            costInput.value = costPrice;
                            costInput.dispatchEvent(new Event('input'));
                        }
                    }
                });

                if (preset) tom.setValue(preset.product_id);

                index++;
                updateRowNumbers();
                attachEvents(row);
                calculateTotal();
            };

            function attachEvents(row) {
                const inputs = row.querySelectorAll('.qty, .cost');
                inputs.forEach(input => {
                    input.addEventListener('input', () => {
                        const qty = parseFloat(row.querySelector('.qty').value) || 0;
                        const cost = parseFloat(row.querySelector('.cost').value) || 0;
                        row.querySelector('.subtotal').innerText = formatCurrency(qty * cost);
                        calculateTotal();
                    });
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

            // Init
            if (oldItems.length > 0) {
                oldItems.forEach(item => addRow(item));
            } else {
                addRow();
            }
        });
    </script>
</x-layouts::app>