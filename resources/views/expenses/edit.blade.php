<x-layouts::app title="Edit Expense">
    <div class="max-w-xl mx-auto py-12 px-4">
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Expense Record</h1>
            
            <form action="{{ route('expenses.update', $expense) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Category</label>
                        <select name="category" class="w-full rounded-lg border-gray-300">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $expense->category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Amount (UGX)</label>
                        <input type="number" name="amount" value="{{ $expense->amount }}" required step="0.01" class="w-full rounded-lg border-gray-300 font-bold">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300">{{ $expense->notes }}</textarea>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg">
                        Update Record
                    </button>
                    <a href="{{ route('expenses.index') }}" class="block text-center text-gray-500">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>