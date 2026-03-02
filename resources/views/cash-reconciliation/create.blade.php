{{-- <x-layouts::app title="Cash Reconciliation">

    <h1 class="text-xl font-semibold mb-4">Cash Reconciliation</h1>
    <div class="flex items-center justify-between mb-4">
        @if ($errors->any())
        <div class="mb-4 text-red-600">
            {{ $errors->first() }}
        </div>
        @endif
    </div>
    <form method="POST" action="{{ route('cash.reconcile.store') }}">
        @csrf

        <input type="hidden" name="business_date" value="{{ $date }}">
        <input type="hidden" name="cash_expected" value="{{ $cashExpected }}">

        <div class="mb-3">
            <label class="block text-sm">Business Date</label>
            <input value="{{ $date }}" disabled class="border px-3 py-2 w-full bg-gray-100">
        </div>

        <div class="mb-3">
            <label class="block text-sm">Cash Expected</label>
            <input value="{{ number_format($cashExpected, 2) }}" disabled class="border px-3 py-2 w-full bg-gray-100">
        </div>

        <div class="mb-3">
            <label class="block text-sm">Cash Counted</label>
            <input name="cash_counted" type="number" step="0.01" required class="border px-3 py-2 w-full">
        </div>

        <div class="mb-3">
            <label class="block text-sm">Notes (required if over/short)</label>
            <textarea name="notes" class="border px-3 py-2 w-full"></textarea>
        </div>

        <button class="bg-black text-white px-4 py-2 w-full">
            Submit Reconciliation
        </button>
    </form>

</x-layouts::app>
--}}

<x-layouts::app title="Cash Reconciliation">
    <div class="max-w-2xl mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Cash Reconciliation</h1>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-600 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('cash.reconcile.store') }}" id="reconcileForm">
            @csrf
            <input type="hidden" name="business_date" value="{{ $date }}">
            {{-- This hidden input will be updated by JS --}}
            <input type="hidden" name="cash_expected" id="hidden_cash_expected" value="{{ $cashExpected }}">

            <div class="bg-gray-900 text-white rounded-xl p-6 mb-6 shadow-lg">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between opacity-70">
                        <span>Previous Balance (Carry over):</span>
                        <span>UGX {{ number_format($openingBalance) }}</span>
                    </div>
                    <div class="flex justify-between opacity-70">
                        <span>New Cash Sales:</span>
                        <span>+ UGX {{ number_format($newSales) }}</span>
                    </div>
                    <div class="flex justify-between text-red-400">
                        <span>Justified Expenses:</span>
                        <span>- UGX <span id="expense_subtotal">0</span></span>
                    </div>
                    <div class="border-t border-gray-700 mt-4 pt-4 flex justify-between items-center">
                        <span class="text-lg font-semibold">Expected Cash:</span>
                        <span class="text-2xl font-black text-indigo-400" id="display_cash_expected">
                            UGX {{ number_format($cashExpected) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-sm font-bold uppercase text-gray-500 mb-3 tracking-widest">Justify Pending Expenses</h3>
                <div class="bg-white border rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="p-3 text-left">Verify</th>
                                <th class="p-3 text-left">Category</th>
                                <th class="p-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($pendingExpenses as $expense)
                                <tr>
                                    <td class="p-3">
                                        <input type="checkbox" name="expense_ids[]" value="{{ $expense->id }}"
                                            data-amount="{{ $expense->amount }}"
                                            class="expense-checkbox w-4 h-4 rounded text-indigo-600">
                                    </td>
                                    <td class="p-3">
                                        <div class="font-medium">{{ $expense->category }}</div>
                                        <div class="text-xs text-gray-400">{{ $expense->notes }}</div>
                                    </td>
                                    <td class="p-3 text-right font-mono font-bold text-gray-700">
                                        {{ number_format($expense->amount) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-4 text-center text-gray-400 italic">No pending expenses found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Actual Cash Counted</label>
                    <input name="cash_counted" id="cash_counted" type="number" step="0.01"
                        class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-lg font-bold"
                        placeholder="Enter total money in drawer">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Notes / Discrepancy
                        Reason</label>
                    <textarea name="notes" rows="2" class="w-full border-gray-300 rounded-lg text-sm"
                        placeholder="e.g. Forgot to record a small expense..."></textarea>
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition shadow-md">
                    Verify & Close Business Day
                </button>
            </div>
        </form>
    </div>

    <!-- <script>
        const checkboxes = document.querySelectorAll('.expense-checkbox');
        const displayExpected = document.getElementById('display_cash_expected');
        const hiddenExpected = document.getElementById('hidden_cash_expected');
        const expenseSubtotal = document.getElementById('expense_subtotal');
        
        const initialExpected = {{ $cashExpected }};

        function calculate() {
            let totalExpenses = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    totalExpenses += parseFloat(cb.dataset.amount);
                }
            });

            const finalExpected = initialExpected - totalExpenses;
            
            // Update UI
            expenseSubtotal.innerText = totalExpenses.toLocaleString();
            displayExpected.innerText = 'UGX ' + finalExpected.toLocaleString();
            
            // Update Hidden Input for Controller
            hiddenExpected.value = finalExpected;
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', calculate);
        });
    </script> -->
    <script>
        const checkboxes = document.querySelectorAll('.expense-checkbox');
        const displayExpected = document.getElementById('display_cash_expected');
        const hiddenExpected = document.getElementById('hidden_cash_expected');
        const expenseSubtotal = document.getElementById('expense_subtotal');
        const cashCountedInput = document.getElementById('cash_counted');

        const initialExpected = {{ $cashExpected }};
        let userModifiedCounted = false;

        // 1️ Fill counted by default on page load
        cashCountedInput.value = initialExpected;

        // 2️ Detect if user manually changes counted
        cashCountedInput.addEventListener('input', () => {
            userModifiedCounted = true;
        });

        function calculate() {
            let totalExpenses = 0;

            checkboxes.forEach(cb => {
                if (cb.checked) {
                    totalExpenses += parseFloat(cb.dataset.amount);
                }
            });

            const finalExpected = initialExpected - totalExpenses;

            // Update UI
            expenseSubtotal.innerText = totalExpenses.toLocaleString();
            displayExpected.innerText = 'UGX ' + finalExpected.toLocaleString();

            // Update hidden field
            hiddenExpected.value = finalExpected;

            // 3️ Auto-update counted ONLY if user hasn’t modified it
            if (!userModifiedCounted) {
                cashCountedInput.value = finalExpected;
            }
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', calculate);
        });
    </script>
</x-layouts::app>