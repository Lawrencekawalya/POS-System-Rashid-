{{--<x-layouts::app title="Record New Expense">
    <div class="max-w-xl mx-auto py-12 px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Record New Expense</h1>
            
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Category</label>
                        <select name="category" class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Amount (UGX)</label>
                        <input type="number" name="amount" required step="0.01" class="w-full rounded-lg border-gray-300 font-bold text-lg">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Payment Method</label>
                            <select name="payment_method" class="w-full rounded-lg border-gray-300">
                                @foreach($methods as $method)
                                    <option value="{{ $method }}">{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Date</label>
                            <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="w-full rounded-lg border-gray-300">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Reference / Receipt No.</label>
                        <input type="text" name="reference_no" placeholder="Optional" class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300" placeholder="Describe what the money was for..."></textarea>
                    </div>

                    <button type="submit" class="w-full bg-black text-white py-4 rounded-xl font-bold text-lg hover:bg-gray-800 transition shadow-lg">
                        Save Expense Claim
                    </button>
                    <a href="{{ route('expenses.index') }}" class="block text-center text-gray-500 font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app> --}}


<x-layouts::app title="Record New Expense">

    <!-- <div class="min-h-screen bg-gray-50 py-12"> -->
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200">

                <!-- Header -->
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
                    <h1 class="text-xl font-bold text-gray-900">
                        Record New Expense
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Track operational or business spending.
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ route('expenses.store') }}" method="POST" class="px-8 py-8 space-y-6">
                    @csrf

                    <!-- Category -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                            Category
                        </label>
                        <select name="category" class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm
                                   focus:ring-2 focus:ring-black focus:border-black transition">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                            Amount (UGX)
                        </label>

                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 font-medium">
                                UGX
                            </span>

                            <input type="number" name="amount" required step="0.01" class="block w-full rounded-xl border border-gray-300 bg-white
                                          pl-16 pr-4 py-4 text-lg font-bold
                                          focus:ring-2 focus:ring-black focus:border-black transition">
                        </div>
                    </div>

                    <!-- Payment + Date -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                Payment Method
                            </label>
                            <select name="payment_method" class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm
                                       focus:ring-2 focus:ring-black focus:border-black transition">
                                @foreach($methods as $method)
                                    <option value="{{ $method }}">{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                Date
                            </label>
                            <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm
                                          focus:ring-2 focus:ring-black focus:border-black transition">
                        </div>
                    </div>

                    <!-- Reference -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                            Reference / Receipt No.
                        </label>
                        <input type="text" name="reference_no" placeholder="Optional" class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-black focus:border-black transition">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                            Notes
                        </label>
                        <textarea name="notes" rows="4" placeholder="Describe what the money was for..."
                            class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm
                                         focus:ring-2 focus:ring-black focus:border-black transition resize-none"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <a href="{{ route('expenses.index') }}"
                            class="text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                            Cancel
                        </a>

                        <button type="submit" class="bg-black text-white px-8 py-3 rounded-xl font-bold text-sm
                                       hover:bg-gray-800 transition shadow-sm">
                            Save Expense
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>

</x-layouts::app>