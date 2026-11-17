{{--<x-layouts::app title="Record New Expense">
    <div class="max-w-xl mx-auto py-12 px-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-700 p-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100 mb-6">Record New Expense</h1>

            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-200 mb-2">Category</label>
                        <select name="category"
                            class="w-full rounded-lg border-gray-300 dark:border-zinc-600 focus:ring-black focus:border-black dark:focus:border-zinc-400">
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-200 mb-2">Amount (UGX)</label>
                        <input type="number" name="amount" required step="0.01"
                            class="w-full rounded-lg border-gray-300 dark:border-zinc-600 font-bold text-lg">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-zinc-200 mb-2">Payment Method</label>
                            <select name="payment_method" class="w-full rounded-lg border-gray-300 dark:border-zinc-600">
                                @foreach($methods as $method)
                                <option value="{{ $method }}">{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-zinc-200 mb-2">Date</label>
                            <input type="date" name="expense_date" value="{{ date('Y-m-d') }}"
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-600">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-200 mb-2">Reference / Receipt No.</label>
                        <input type="text" name="reference_no" placeholder="Optional"
                            class="w-full rounded-lg border-gray-300 dark:border-zinc-600">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-200 mb-2">Notes</label>
                        <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300 dark:border-zinc-600"
                            placeholder="Describe what the money was for..."></textarea>
                    </div>

                    <button type="submit"
                        class="w-full bg-black dark:bg-white text-white dark:text-zinc-900 py-4 rounded-xl font-bold text-lg hover:bg-gray-800 dark:hover:bg-zinc-200 transition shadow-lg">
                        Save Expense Claim
                    </button>
                    <a href="{{ route('expenses.index') }}"
                        class="block text-center text-gray-500 dark:text-zinc-400 font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app> --}}


<x-layouts::app title="Record New Expense">

    <!-- <div class="min-h-screen bg-gray-50 dark:bg-zinc-800 py-12"> -->
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-700">

            <!-- Header -->
            <div class="px-8 py-6 border-b border-gray-100 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50">
                <h1 class="text-xl font-bold text-gray-900 dark:text-zinc-100">
                    Record New Expense
                </h1>
                <p class="text-sm text-gray-500 dark:text-zinc-400 mt-1">
                    Track operational or business spending.
                </p>
            </div>

            <!-- Form -->
            <form action="{{ route('expenses.store') }}" method="POST" class="px-8 py-8 space-y-6">
                @csrf

                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase mb-2">
                        Category
                    </label>
                    <select name="category" class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-4 py-3 text-sm
                                   focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase mb-2">
                        Amount (UGX)
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 font-medium">
                            UGX
                        </span>

                        <input type="number" name="amount" required step="0.01" class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900
                                          pl-16 pr-4 py-4 text-lg font-bold
                                          focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition">
                    </div>
                </div>

                <!-- Payment + Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase mb-2">
                            Payment Method
                        </label>
                        <select name="payment_method" class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-4 py-3 text-sm
                                       focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition">
                            @foreach($methods as $method)
                                <option value="{{ $method }}">{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase mb-2">
                            Date
                        </label>
                        <!-- <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-4 py-3 text-sm
                                          focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition"> -->
                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}"
                            class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-4 py-3 text-sm focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition">
                    </div>
                </div>

                <!-- Reference -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase mb-2">
                        Reference / Receipt No.
                    </label>
                    <input type="text" name="reference_no" placeholder="Optional" class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition">
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase mb-2">
                        Notes
                    </label>
                    <textarea name="notes" rows="4" placeholder="Describe what the money was for..."
                        class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-4 py-3 text-sm
                                         focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition resize-none"></textarea>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-zinc-700">
                    <a href="{{ route('expenses.index') }}"
                        class="text-sm font-medium text-gray-500 dark:text-zinc-400 hover:text-gray-700 dark:hover:text-zinc-200 transition">
                        Cancel
                    </a>

                    <button type="submit" class="bg-black dark:bg-white text-white dark:text-zinc-900 px-8 py-3 rounded-xl font-bold text-sm
                                       hover:bg-gray-800 dark:hover:bg-zinc-200 transition shadow-sm">
                        Save Expense
                    </button>
                </div>

            </form>
        </div>

    </div>
    </div>

</x-layouts::app>


{{--  }}<x-layouts::app title="Record New Expense">

    <!-- <div class="min-h-screen bg-gray-50 dark:bg-zinc-800 py-12"> -->
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-700">

            <!-- Header -->
            <div class="px-8 py-6 border-b border-gray-100 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50">
                <h1 class="text-xl font-bold text-gray-900 dark:text-zinc-100">
                    Record New Expense
                </h1>
                <p class="text-sm text-gray-500 dark:text-zinc-400 mt-1">
                    Track operational or business spending.
                </p>
            </div>

            <!-- Form -->
            <form action="{{ route('expenses.store') }}" method="POST" class="px-8 py-8 space-y-6">
                @csrf

                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase mb-2">
                        Category
                    </label>
                    <select name="category" class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-4 py-3 text-sm
                                   focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase mb-2">
                        Amount (UGX)
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 font-medium">
                            UGX
                        </span>

                        <input type="number" name="amount" required step="0.01" class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900
                                          pl-16 pr-4 py-4 text-lg font-bold
                                          focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition">
                    </div>
                </div>

                <!-- Payment + Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase mb-2">
                            Payment Method
                        </label>
                        <select name="payment_method" class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-4 py-3 text-sm
                                       focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition">
                            @foreach($methods as $method)
                                <option value="{{ $method }}">{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase mb-2">
                            Date
                        </label>
                        <!-- <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-4 py-3 text-sm
                                          focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition"> -->
                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}"
                            class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-4 py-3 text-sm focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition">
                    </div>
                </div>

                <!-- Reference -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase mb-2">
                        Reference / Receipt No.
                    </label>
                    <input type="text" name="reference_no" placeholder="Optional" class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition">
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase mb-2">
                        Notes
                    </label>
                    <textarea name="notes" rows="4" placeholder="Describe what the money was for..."
                        class="block w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-4 py-3 text-sm
                                         focus:ring-2 focus:ring-black focus:border-black dark:focus:border-zinc-400 transition resize-none"></textarea>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-zinc-700">
                    <a href="{{ route('expenses.index') }}"
                        class="text-sm font-medium text-gray-500 dark:text-zinc-400 hover:text-gray-700 dark:hover:text-zinc-200 transition">
                        Cancel
                    </a>

                    <button type="submit" class="bg-black dark:bg-white text-white dark:text-zinc-900 px-8 py-3 rounded-xl font-bold text-sm
                                       hover:bg-gray-800 dark:hover:bg-zinc-200 transition shadow-sm">
                        Save Expense
                    </button>
                </div>

            </form>
        </div>

    </div>
    </div>

</x-layouts::app>--}}