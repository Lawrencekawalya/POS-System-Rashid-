<x-layouts::app title="Edit Cash Reconciliation">
    <div class="max-w-2xl mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Edit Cash Reconciliation</h1>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-600 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('cash.reconcile.update', $reconciliation) }}">
            @csrf
            @method('PUT')

            <div class="bg-gray-900 text-white rounded-xl p-6 mb-6 shadow-lg">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between opacity-70">
                        <span>Business Date:</span>
                        <span>{{ $reconciliation->business_date }}</span>
                    </div>

                    <div class="border-t border-gray-700 mt-4 pt-4 flex justify-between items-center">
                        <span class="text-lg font-semibold">Expected Cash:</span>
                        <span class="text-2xl font-black text-indigo-400">
                            UGX {{ number_format($reconciliation->cash_expected) }}
                        </span>
                    </div>

                    <div class="flex justify-between mt-3">
                        <span>Status:</span>
                        <span class="font-bold
                            @if($reconciliation->status === 'balanced') text-green-400
                            @elseif($reconciliation->status === 'over') text-blue-400
                            @else text-red-400
                            @endif">
                            {{ ucfirst($reconciliation->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">
                        Actual Cash Counted
                    </label>
                    <input name="cash_counted"
                           type="number"
                           step="0.01"
                           value="{{ old('cash_counted', $reconciliation->cash_counted) }}"
                           class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-lg font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">
                        Notes / Discrepancy Reason
                    </label>
                    <textarea name="notes"
                              rows="2"
                              class="w-full border-gray-300 rounded-lg text-sm">{{ old('notes', $reconciliation->notes) }}</textarea>
                </div>

                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition shadow-md">
                    Update Reconciliation
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>